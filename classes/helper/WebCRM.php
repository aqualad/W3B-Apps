<?php

class WebCRM {

	// Returns an associative array of the lowest ctlControl objects
	// @return Associative array [ $control_name => $control_property ]
	// @param $control 		Control Object to scan
	// @param $ctlControls 	Always send an empty array, it's the same array that is passed through recursion
	// @param $type 		Associate array [$control_type => $control_property, $control_type=>$control_property, ... ]
	// 							Valid types ($control_type) [ ctlControl | ctlTextBox | ctlSelect | etc.. ]
	// 							Valid properties ($control_property) [ null | name | value | style | properties | etc ]
	// 	Note:	If no $control_property is set, the function defaults to the actual ctl Object
	// 			When requesting the ctl Object for multiple TYPES:
	//
	// 	e.x.	getctlControls( $this->controls['ViewPanel'], [], ['ctlTextBox'=>null, 'ctlSelect'=>null] )
	public static function getctlControls( $control, $ctlControls=array(), $type=array('ctlTextBox'=>'value') )
	{
		if ( array_key_exists($control->getControlName(), $type) )
		{
			$ctlControls[$control->getName()] = isset($type[$control->getControlName()])
				? $control->getProperty($type[$control->getControlName()]) : $control;
		}
		elseif ($control->getControlName() == 'ctlControl' || $control->getControlName() == 'ctlTabList')
		{
			// Find current control's children
			$children = $control->getChildren();

			foreach ( $children as $child )
			{
				// Search for child ctlControls
				if ( array_key_exists($child->getControlName(), $type) || $child->getControlName() == 'ctlControl' || $child->getControlName() == 'ctlTabList')
				{
					// Add the child ctlControl
					$ctlControls = WebCRM::getctlControls( $child, $ctlControls, $type );
				}
			}
		}

		return $ctlControls;
	}

	// Iterates through a list of values, handling containers (Arrays, Objects) by recursively iterating them
	// @param $controls - Associate array of control objects (Tip: This is what is returned from getctlControls)
	// 		[ $control_name => $control_object ]
	// @param $values - Associate array of values to set [ $control_name => $value ]
	public static function populateControls( &$controls, $values )
	{
		foreach($values as $name => $value)
			if ( array_key_exists($name,$controls) )
				switch($controls[$name]->getControlName())
				{
					case 'ctlCalendar':
						$controls[$name]->setProperty('value', format::formatedDate($value));
						break;
					case 'ctlCheckBox':
						$controls[$name]->setProperty('value', $value ? 'on' : 'off');
						break;
					case 'ctlLabel':
						$controls[$name]->setProperty('text', $value);
					default:
						$controls[$name]->setProperty('value', $value);
						break;
				}
			else
				if ( is_array($value) || is_object($value) )
					self :: populateControls($controls, $value);
	}

	// Returns the LayoutID of a specified application
	// @param string $appname
	// @param bool $unique
	//	(True) If the application is only in a single Layout, that LayoutID
	//	 will be returned, otherwise the return will be false
	//	(False) An array of all LayoutIDs will be returned for the app
	public static function getApplicationLayoutID($appname, $unique=true)
	{
		$prefix = db::prefix();

		$layout_results = db::query(<<<SQL
SELECT LayoutID
FROM {$prefix}layout_item items
LEFT JOIN {$prefix}layout_zone zones
	ON items.ZoneID = zones.ZoneID
WHERE items.Action = '$appname'
SQL
		);

		if ($layout_results->num_rows())
		{
			if ($unique)
				if($layout_results->num_rows() === 1)
					return $layout_results->fetch_object()->LayoutID;
				else
					return false;


			return $layout_results->fetch_all();
		}
	}

	public static function getEmployees()
	{
		$prefix = db::prefix();
		$auth = Auth::get();
		$uid = $auth['UserID'];

		$db_result = Auth::isAdmin()
			? db::query( <<<SQL
SELECT a.*
FROM {$prefix}crm_employees a
WHERE a.UID != $uid
ORDER BY Name ASC
SQL
		)
			: db::query( <<<SQL
SELECT b.*
FROM {$prefix}crm_useraccess a
LEFT JOIN {$prefix}crm_employees b ON a.EID=b.ID
WHERE a.UID=$uid
SQL
		);

		if ( $db_result->num_rows() )
		{
			while($row = $db_result->fetch_assoc())
			{
				$result[$row['ID']] = $row;
			}
		}
		else
		{
			return false;
		}

		return $result;

	}

	public static function getOpportunities($user = NULL, $just_sql = false)
	{
		$prefix = db::prefix();
		$opportunities = array();
		if ( is_array($user) )
		{
			$user = implode(',', $user).')';
			$equals = 'IN (';
		}
		else
			$equals = ' = ';

		$opportunity_query = is_null($user)
			? <<<SQL
SELECT a.ID, a.Name, b.CustomerName AS Account, a.Amount,  a.ExpectedClose, a.Probability, a.Result
FROM {$prefix}crm_opportunities a
LEFT JOIN {$prefix}crm_clients b ON a.Account=b.ID
WHERE 1
SQL
			: <<<SQL
SELECT a.ID, a.Name, b.CustomerName AS Account, a.Amount,  a.ExpectedClose, a.Probability, a.Result
FROM {$prefix}crm_opportunities a
LEFT JOIN {$prefix}crm_clients b ON a.Account=b.ID
WHERE (a.CreatedBy $equals $user OR a.ManagedBy $equals $user OR a.ManagedBy IS NULL)
SQL;

		if ($just_sql)
			return $opportunity_query;

		$opportunity_result = db::query($opportunity_query);

		if ($opportunity_result->num_rows())
			while($opportunity = $opportunity_result->fetch_assoc())
				$opportunities[$opportunity['ID']] = $opportunity;
		else
			return false;

		return $opportunities;
	}

	public static function getEmployeeUsers($user = NULL)
	{
		$users = array(0 => array('txt'=>'Select Employee', 'value'=>0));
		$prefix = db::prefix();

		$user_result = is_null($user)
			? db::query( <<<SQL
SELECT e.UID AS value, CONCAT(e.FirstName, ' ', e.LastName) AS txt
FROM {$prefix}crm_employees e
WHERE e.UID IS NOT NULL
SQL
		)	: db::query( <<<SQL
SELECT e.UID AS value, CONCAT(e.FirstName, ' ', e.LastName) AS txt
FROM {$prefix}crm_employees e
INNER JOIN {$prefix}crm_useraccess ua ON ua.EID = e.ID
WHERE (ua.UID = $user OR e.UID = $user) AND e.UID IS NOT NULL
SQL
				);

		while( $user = $user_result->fetch_object() )
			$users[$user->value] = $user;

		return $users;
	}

	public static function getDepartmentName($id = NULL)
	{
		if ( is_null($id) || ! is_numeric($id) )
			return false;

		$table = db::prefix('crm_departments');

		$dept_result = db::query("SELECT Name FROM $table WHERE ID = $id");

		if ($dept_result->num_rows())
			return $dept_result->fetch_object()->Name;
	}

	public static function getGroups($user = NULL)
	{
		$prefix = db::prefix();
		$items = array();

		$result = is_null($user)
			?	db::query( <<<SQL
SELECT GroupID AS value, Groupname AS txt
FROM {$prefix}groups
WHERE 1
ORDER BY txt ASC
SQL
		)	:	db::query( <<<SQL
SELECT DISTINCT g.GroupID as value, g.Groupname as txt
FROM {$prefix}crm_employees e
LEFT JOIN {$prefix}crm_useraccess ua ON ua.EID=e.ID
INNER JOIN {$prefix}user_groups ug ON ug.UserID=ua.UID
LEFT JOIN {$prefix}groups g ON g.GroupID=ug.GroupID
WHERE (e.UID=$user OR ua.UID=$user)
ORDER BY txt
SQL
		);

		if( $result->num_rows() )
		{
			while($group = $result->fetch_assoc())
				$items[] = $group;

			return $items;
		}
	}

	public static function getCountryName( $country=null )
	{
		if ( is_null( $country ) )
			return false;

		$table = db :: prefix( 'crm_countries' );

		$result = db :: query( "SELECT country_name FROM $table WHERE country_id = $country" );

		if ( $row = $result->fetch_object() )
			return $row->country_name;
		else
			return false;
	}

	public static function getCurrencies( $country=null )
	{
		$prefix = db::prefix();
		$items = array(
			array('txt'=>'Select a Currency', 'value'=>0)
		);

		$result = db::query( <<<SQL
SELECT ID AS value, CONCAT(CurrencyCode, ' (', CurrencyName, ')') AS txt
FROM {$prefix}crm_currencies
ORDER BY ID DESC, CurrencyCode ASC
SQL
		);

		while($row = $result->fetch_object())
			$items[$row->value] = array('txt'=>$row->txt, 'value'=>$row->value);

		return $items;
	}

	public static function getBillingPlansItems($event = NULL, $default = NULL)
	{
		$prefix = db::prefix();
		$items = is_null($default) ? array() : array(0=>array('txt'=>$default, 'value'=>0, 'onclick'=>$event?:''));
		$event = is_null($event) ?: ",'$event' as onclick";

		$plan_results = db::query("SELECT ID as value, Title as txt {$event} FROM {$prefix}crm_billing_plans WHERE 1");
		while($plan = $plan_results->fetch_assoc())
			$items[$plan['value']] = $plan;
		return $items;
	}


	public static function getCategoriesItems()
	{
		$prefix = db::prefix();
		$items = array();
		$items[] = array('txt'=>CrmLang::_('Select Category'),'value'=>0);
		$q = "SELECT * from {$prefix}crm_clients_categories where 1 order by CCategoryName asc";
		$res = db::query($q);
		while ($row = $res->fetch_object())
		{
			$items[$row->ID] = array('txt'=>$row->CCategoryName,'value'=>$row->ID);
		}
		return $items;
	}

}

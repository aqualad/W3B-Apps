<?php

class CRMCustomerAccess extends Application
{

	const APP_TYPE = APP_TYPE_PANEL;

	public function onBeforeInit()
	{
		if ( ! Auth::isAdmin() )
		{
			ErrorHandler::msgNotice('This application requires administrative privelege!');
			$this->close();
		}

		$this->prefix = db::prefix();
	}

	public function onBeforeLoadState()
	{
		$this->controls['plans']->setProperty('items', WebCRM::getBillingPlansItems('this.chplan()', 'Create New'));
		$this->controls['CustomerList']->setProperty('items', WebCRMclient::getClientsList());
		$this->loadWidgets();
	}

	public function onCustomerAccessMainWindowClose()
	{
		$this->close();
	}

	public function onManageCustomersClick()
	{
		$this->controls['TabList']->setProperty( "value", 'ManageCustomersTab' );
	}

	public function onManageUsersClick()
	{
		$this->controls['TabList']->setProperty( "value", 'ManageUsersTab' );
	}

	public function onManagePlansClick()
	{
		$this->controls['TabList']->setProperty( "value", 'ManagePlansTab' );
	}

	public function onCustomersClick()
	{
		// Get Customer Info from DbGrid
		$items = $this->controls['Customers']->getProperty('items');
		$client = $this->controls['Customers']->getProperty('value');
		$client = $items[$client[0]];
		$this->client_id = $client->ID;

		// Set details
		$this->controls['Plan']->setProperty('value', $client->billing_plan_id);
		$this->onPlanChange();
		$this->controls['CustomerName']->setProperty('text', "<h2>{$client->CustomerName}</h2>");

		// Build list of Assigned Employees
		$defs = ResCRMCustomerAccess::getAssignedUsersList($this->getAssignedPortalUsers($client->ID));

		if ($defs)
		{
			foreach($defs as $def)
			{
				if (isset($this->controls[$def['name']]))
					$this->controls[$def['name']]->close();
				new ctlLabel($this, $def, $this->controls['CustomerAssignedEmployees']);
			}
		}
		else
		{
			$ctls = WebCRM::getctlControls($this->controls['CustomerAssignedEmployees'], [], ['ctlLabel'=>null]);
			unset($ctls['AssignedEmployeesl']);
			foreach($ctls as $ctl)
				$ctl->close();
		}

		// Make details section visible
		$this->controls['CustomerDetails']->show();
		$this->controls['SavePlan']->hide(); // Hide until plan is changed
	}

	// Event for ctlAutoSelect in Customers Tab
	public function onPlanChange()
	{
		if ( ! $this->controls['Plan']->getProperty('value'))
		{
			$this->controls['Quotal']->clear();
			$this->controls['SubAmountl']->clear();
			$this->controls['SubPeriod']->clear();
			return ;
		}
		// Populate the Plans info
		$items = $this->controls['Customers']->getProperty('items');
		$client = $this->controls['Customers']->getProperty('value');
		$client = $items[$client[0]];

		$plan_results = db::query("SELECT * FROM {$this->prefix}crm_billing_plans WHERE ID = {$this->controls['Plan']->getProperty('value')} LIMIT 1");

		if ($plan_results->num_rows())
		{
			$plan = $plan_results->fetch_object();

			$this->controls['Quotal']->setProperty('text', "Users: {$client->Employees}/{$plan->MaxUsers}");
			$this->controls['SubAmountl']->setProperty('text', 'Cost: $'.$plan->Amount);
			$this->controls['SubPeriod']->setProperty('text', 'Period: '.$plan->BillPeriod);
		}

		$this->controls['SavePlan']->show();
	}

	public function onSavePlanClick()
	{
		if ( ! isset($this->client_id) )
			return ErrorHandler::msgNotice('You must first select a Customer');

		db::query("DELETE FROM {$this->prefix}crm_client_subscriptions WHERE client_id = {$this->client_id}");
		db::query("INSERT INTO {$this->prefix}crm_client_subscriptions (client_id, billing_plan_id) VALUES ({$this->client_id},{$this->controls['Plan']->getProperty('value')})");
		$this->controls['SavePlan']->hide();
	}

	// Event for ctlTree in Manage Plans tab
	public function onPlansChplan()
	{
		if ($this->controls['plans']->getProperty('value') == 0)
		{
			$this->controls['delete']->setProperty('visible', false);
			return $this->clearform('ManagePlansTab');
		}

		$this->controls['delete']->setProperty('visible', true);
		$target_ctls = WebCRM::getctlControls($this->controls['ManagePlansTab'], [], ['ctlTextBox'=>NULL, 'ctlRadio'=>NULL]);
		$plan_results = db::query("SELECT * FROM {$this->prefix}crm_billing_plans WHERE ID = {$this->controls['plans']->getProperty('value')} LIMIT 1");

		WebCRM::populateControls($target_ctls, $plan_results->fetch_assoc());
	}

	public function onSaveClick()
	{
		// Define the error container
		$errors = array();

		// Get all the form data and sanitize it at once
		$values = db::escape(WebCRM::getctlControls( $this->controls['ManagePlansTab'], [], ['ctlTextBox'=>'value', 'ctlRadio'=>'value']));
		$types = db::escape(WebCRM::getctlControls( $this->controls['ManagePlansTab'], [], ['ctlTextBox'=>'db_type', 'ctlRadio'=>'db_type']));
		$plan = db::escape($this->controls['plans']->getProperty('value'));

		// Remove special formatting characters
		$values['Amount'] = format::pricetodecimal($values['Amount']);

		// Validation
		validate::string($values['Title'], 'Title:', NULL, 80, true, $errors);
		validate::string($values['Description'], 'Description:', NULL, 500, false, $errors);
		validate::string($values['BillPeriod'], 'Billing Period:', 1, 20, true, $errors);
		validate::number($values['Amount'], 'Amount:', 0, NULL, true, $errors);
		validate::number($values['MaxUsers'], 'Max. Users:', 1, 99999, true, $errors);

		if (count($errors))
		{
			foreach($errors as $error)
				ErrorHandler::msgNotice($error);
			return;
		}

		// Prepare and format validated data for insert
		foreach($values as $ctlname => $value)
			if ($types[$ctlname] == 'VARCHAR' || $types[$ctlname] == 'TEXT')
				$values[$ctlname] = "'$value'";

		// Check if adding or editting
		if ($plan == 0)
		{ // Adding
			// Get column names and implode to a string
			$columns = array_keys($values);
			$columns = implode(',', $columns);
			$values = implode(',', $values);

			db::query(<<<SQL
INSERT INTO {$this->prefix}crm_billing_plans ($columns) VALUES ($values)
SQL
			);
		}
		else
		{ // Editting
			// Prepare values for UPDATE query
			foreach($values as $column => $value)
				$values[$column] = "$column=$value";

			$values = implode(',', $values);
			db::query(<<<SQL
UPDATE {$this->prefix}crm_billing_plans SET $values WHERE ID = $plan
SQL
			);
		}

		$this->clearform('ManagePlansTab');
		$this->onBeforeLoadState();
		$this->controls['Plan']->setProperty('items', WebCRM::getBillingPlansItems('', 'None'));
		$this->onPlansChplan();
	}

	public function ondeleteConfirm()
	{
		db::query("DELETE FROM {$this->prefix}crm_billing_plans WHERE ID = {$this->controls['plans']->getProperty('value')}");
		$this->clearform('ManagePlansTab');
		$this->onBeforeLoadState();
		$this->onPlansChplan();
	}

	public function onCancelClick()
	{
		$this->clearform();
	}

	public function onCustomerListChange()
	{
		$this->controls['AssignedUsers']->setProperty('items', $this->getAssignedPortalUsers($this->controls['CustomerList']->getProperty('value')));
	}

	public function onWidAddClick()
	{
		if ( ! $this->controls['UnassignedUsers']->getProperty('value'))
			return ErrorHandler::msgNotice('You must select a User first!');
		if ( ! $this->controls['CustomerList']->getProperty('value'))
			return ErrorHandler::msgNotice('You must select a Customer first!');

		$portal_user_id = db::escape($this->controls['UnassignedUsers']->getProperty('value'));
		$client_id = db::escape($this->controls['CustomerList']->getProperty('value'));
		db::query("INSERT INTO {$this->prefix}crm_portal_users_clients (portal_user_id, client_id) VALUES ({$portal_user_id[0]}, $client_id)");
		$this->loadWidgets();
	}

	public function onWidDelClick()
	{
		if ( ! $this->controls['AssignedUsers']->getProperty('value'))
			return ErrorHandler::msgNotice('You must select a User first!');
		if ( ! $this->controls['CustomerList']->getProperty('value'))
			return ErrorHandler::msgNotice('You must select a Customer first!');

		$portal_user_id = db::escape($this->controls['AssignedUsers']->getProperty('value'));
		$client_id = db::escape($this->controls['CustomerList']->getProperty('value'));
		db::query("DELETE FROM {$this->prefix}crm_portal_users_clients WHERE portal_user_id = {$portal_user_id[0]} AND client_id = $client_id");
		$this->loadWidgets();
	}

	public function clearform($control_name = NULL)
	{
		$control = $control_name ? $this->controls[$control_name]: $this->controls['TabList'];

		$ctls = WebCRM::getctlControls($control, [], ['ctlTextBox'=>NULL, 'ctlTree'=>NULL, 'ctlRadio'=>NULL]);

		foreach($ctls as $ctl)
			$ctl->clear();
	}

	public function getUnassignedPortalUsers()
	{
		$items = array();

		$user_results = db::query(<<<SQL
SELECT ID, Email
FROM  `w3b_crm_portal_users`
WHERE Email NOT
IN (
	SELECT Email
	FROM {$this->prefix}users
)
AND ID NOT
IN (
	SELECT portal_user_id
	FROM {$this->prefix}crm_portal_users_clients
)
SQL
		);

		while($user = $user_results->fetch_object())
			$items[$user->ID] = array('txt'=>$user->Email, 'value'=>$user->ID);

		return $items;
	}

	public function getAssignedPortalUsers($client_id = NULL)
	{
		if (is_null($client_id) || ! is_numeric($client_id))
			return false;

		$items = array();

		$user_results = db::query(<<<SQL
SELECT ID, Email
FROM {$this->prefix}crm_portal_users_clients puc
LEFT JOIN {$this->prefix}crm_portal_users pu
	ON puc.portal_user_id = pu.ID
WHERE puc.client_id = $client_id
SQL
		);

		while($user = $user_results->fetch_object())
			$items[$user->ID] = array('txt'=>$user->Email, 'value'=>$user->ID);

		return $items;
	}

	public function loadWidgets()
	{
		$this->controls['UnassignedUsers']->setProperty('items', $this->getUnassignedPortalUsers());
		if ($client_id = $this->controls['CustomerList']->getProperty('value'))
			$this->controls['AssignedUsers']->setProperty('items', $this->getAssignedPortalUsers($client_id));
	}

}

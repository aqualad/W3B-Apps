<?php

class CRMUserAccess extends Application
{
	public $target_uid = null; //UID of the selected User
	public $gid = null; //ID of the current Department
	public $user = null;
	public $MyCompany = null;
	public $MyEntityType = null;
	public $prefix = null;
	const APP_TYPE = APP_TYPE_WINDOW;

	public static function getTitle()
	{
		return CrmLang::_("CRM - User Access");
	}

	public static function getDescription()
	{
		return CrmLang::_("Manage access and availability between users, limiting possible interactions.");
	}

// Events
	public function onBeforeInit()
	{
	// Set UserID
		$auth = Auth :: get();
		$this->user = $auth['UserID'];
	// Set Prefix
		$this->prefix = db::prefix();
	}

	public function onBeforeLoadState()
	{
		$this->fillTarget();

		$params = Request::getParams();

		if (isset($params['type']))
			switch($params['type'])
			{
				case 'contact':
					$this->controls['TabList']->setProperty('value', 'ViewContacts');
					$this->controls['ViewEmployees']->close();
					$this->controls['ViewLeads']->close();
					$this->controls['ViewClients']->close();
					$this->controls['LeftArea']->close();

					$this->controls['TargetContact']->setProperty('value', $params['contact']);
					$this->onTargetContactChange();
					break;

				case 'lead':
					$this->controls['TabList']->setProperty('value', 'ViewLeads');
					$this->controls['ViewContacts']->close();
					$this->controls['ViewEmployees']->close();
					$this->controls['ViewClients']->close();
					$this->controls['LeftArea']->close();

					$this->controls['TargetLead']->setProperty('value', $params['lead']);
					$this->onTargetLeadChange();
					break;

				case 'client':
					$this->controls['TabList']->setProperty('value', 'ViewClients');
					$this->controls['ViewContacts']->close();
					$this->controls['ViewEmployees']->close();
					$this->controls['ViewLeads']->close();
					$this->controls['LeftArea']->close();

					$this->controls['TargetClient']->setProperty('value', $params['client']);
					$this->onTargetClientChange();
					break;
			}
	}

//Control-Triggered Events
	public function onUserAccessMainPanelClose()
	{
		$this->close();
	}

	public function onTargetEmployeeChange()
	{
		if(  $this->controls['TargetEmployee']->getProperty('value') )
			$this->controls['EmployeeGroupList']->setProperty('items', $this->generateTreeItems( WebCRM::getGroups(Auth::isAdmin() ? NULL : $this->user) ));
		else
		{
			$this->controls['EmployeeUserList']->dump();
			$this->controls['EmployeeGroupList']->dump(); // Depopulate the list if none selected
		}
	}

	public function onTargetContactChange()
	{
		if(  $this->controls['TargetContact']->getProperty('value') )
			$this->controls['ContactGroupList']->setProperty('items', $this->generateTreeItems( WebCRM::getGroups(Auth::isAdmin() ? NULL : $this->user) ));
		else
		{
			$this->controls['ContactUserList']->dump();
			$this->controls['ContactGroupList']->dump(); // Depopulate the list if none selected
		}
	}

	public function onTargetLeadChange()
	{
		if( is_numeric($this->controls['TargetLead']->getProperty('value')) )
		{
			$id = db::escape($this->controls['TargetLead']->getProperty('value'));

			$this->controls['LeadGroupList']->setProperty('items', $this->generateTreeItems( WebCRM::getGroups(Auth::isAdmin() ? NULL : $this->user) ));

			// Populate Manager field
			$lead_manager = db::query("SELECT e.UID, CONCAT(e.FirstName, ' ', e.LastName) AS Name FROM {$this->prefix}crm_leads l LEFT JOIN {$this->prefix}crm_employees e ON e.UID = ManagedBy WHERE l.ID = $id AND l.ID IS NOT NULL");

			if ($lead_manager->num_rows())
			{
				$lead = $lead_manager->fetch_object();
				$lead_name = $lead->Name;
				$this->controls['LeadManagedBy']->setProperty('value', $lead->UID);
			}
			else
				$this->controls['LeadManagedBy']->clear();


			// Check isAdmin or isCreator
			$lead_result = db::query("SELECT CreatedBy FROM {$this->prefix}crm_leads WHERE ID = $id");

			if ( Auth::isAdmin() || ($lead_result->num_rows() && $lead_result->fetch_object()->CreatedBy == $this->user) )
			{
				$this->controls['LeadManagedByl']->setProperty('text', 'Managed by:');
				$this->controls['LeadManagedBy']->show();
			}
			else
			{
				$this->controls['LeadManagedBy']->hide();
				$this->controls['LeadManagedByl']->setProperty('text', "Managed by: $lead_name");
			}

		}
		else
		{
			$this->controls['LeadUserList']->dump();
			$this->controls['LeadGroupList']->dump(); // Depopulate the list if none selected
			$this->controls['LeadManagedByl']->hide();
			$this->controls['LeadManagedBy']->hide();
			$this->controls['LeadManagedBy']->clear();
		}
	}

	public function onTargetClientChange()
	{
		if( is_numeric($this->controls['TargetClient']->getProperty('value')) )
		{
			$id = db::escape($this->controls['TargetClient']->getProperty('value'));

			$this->controls['ClientGroupList']->setProperty('items', $this->generateTreeItems( WebCRM::getGroups(Auth::isAdmin() ? NULL : $this->user) ));

			// Populate Manager field
			$client_manager = db::query("SELECT e.UID, CONCAT(e.FirstName, ' ', e.LastName) AS Name FROM {$this->prefix}crm_clients c LEFT JOIN {$this->prefix}crm_employees e ON e.UID = ManagedBy WHERE c.ID = $id AND c.ID IS NOT NULL");

			if ($client_manager->num_rows())
			{
				$client = $client_manager->fetch_object();
				$client_name = $client->Name;
				$this->controls['ClientManagedBy']->setProperty('value', $client->UID);
			}
			else
				$this->controls['ClientManagedBy']->clear();

			// Check isAdmin or isCreator
			$client_result = db::query("SELECT CreatedBy FROM {$this->prefix}crm_clients WHERE ID = $id");

			if ( Auth::isAdmin() || ($client_result->num_rows() && $client_result->fetch_object()->ID == $this->user) )
			{
				$this->controls['ClientManagedByl']->setProperty('text', 'Managed by:');
				$this->controls['ClientManagedBy']->show();
			}
			else
			{
				$this->controls['ClientManagedBy']->hide();
				$this->controls['ClientManagedByl']->setProperty('text', "Managed by: $client_name");
			}
		}
		else
		{
			$this->controls['ClientUserList']->dump();
			$this->controls['ClientGroupList']->dump(); // Depopulate the list if none selected
			$this->controls['ClientManagedByl']->hide();
			$this->controls['ClientManagedBy']->hide();
			$this->controls['ClientManagedBy']->clear();
		}
	}

	public function onEmployeeGroupListChgroup()
	{
		$this->fillUser('employee', $this->controls['TargetEmployee']->getProperty('value'), $this->controls['EmployeeGroupList']->getProperty('value'), $this->controls['EmployeeUserList']);
	}

	public function onContactGroupListChgroup()
	{
		$this->fillUser('contact', $this->controls['TargetContact']->getProperty('value'), $this->controls['ContactGroupList']->getProperty('value'), $this->controls['ContactUserList']);
	}

	public function onLeadGroupListChgroup()
	{
		$this->fillUser('lead', $this->controls['TargetLead']->getProperty('value'), $this->controls['LeadGroupList']->getProperty('value'), $this->controls['LeadUserList']);
	}

	public function onClientGroupListChgroup()
	{
		$this->fillUser('client', $this->controls['TargetClient']->getProperty('value'), $this->controls['ClientGroupList']->getProperty('value'), $this->controls['ClientUserList']);
	}

	public function onEmployeeSaveClick()
	{
		$target = WebCRMemployee::getUserID($this->controls['TargetEmployee']->getProperty('value'));

		// Save User Access Rules
		if (empty($target))
			return $this->controls['autoSave']->getProperty('value') === 'on' ? false : ErrorHandler::msgNotice("Please select a user from the menu");

		if ( ! $this->controls['EmployeeGroupList']->getProperty('value') )
			return $this->controls['autoSave']->getProperty('value') === 'on' ? false : ErrorHandler::msgNotice("Please select a group");

		if ( ! $this->controls['EmployeeUserList']->getProperty('value') )
			return;

		$items = $this->controls['EmployeeUserList']->getProperty('items');
		foreach($items as $index => $id)
			$items[$index]['value'] = WebCRMemployee::getEmployeeID($items[$index]['value']);

		$value = $this->controls['EmployeeUserList']->getProperty('value');
		$value = explode(',',$value);

		if (is_array($items))
			foreach($value as $id => $checked)
				$checked == 'on'
					? db::query("REPLACE INTO {$this->prefix}crm_useraccess SET `UID`={$target}, `EID`={$items[$id]['value']}")
					: db::query("DELETE FROM {$this->prefix}crm_useraccess WHERE `UID`={$target} AND `EID`={$items[$id]['value']}");
	}

	public function onContactSaveClick()
	{
		$target = $this->controls['TargetContact']->getProperty('value');

		// Save User Access Rules
		if (empty($target))
			return $this->controls['autoSave']->getProperty('value') === 'on' ? false : ErrorHandler::msgNotice("Please select a contact from the menu");

		if ( ! $this->controls['ContactGroupList']->getProperty('value') )
			return $this->controls['autoSave']->getProperty('value') === 'on' ? false : ErrorHandler::msgNotice("Please select a group");

		if ( ! $this->controls['ContactUserList']->getProperty('value') )
			return;

		$items = $this->controls['ContactUserList']->getProperty('items');
		$value = $this->controls['ContactUserList']->getProperty('value');
		$value = explode(',',$value);

		if (is_array($items))
			foreach($value as $id => $checked)
				$checked == 'on'
					? db::query("REPLACE INTO {$this->prefix}crm_contacts_access SET `UID`={$items[$id]['value']}, `CID`={$target}")
					: db::query("DELETE FROM {$this->prefix}crm_contacts_access WHERE `UID`={$items[$id]['value']} AND `CID`={$target}");
	}

	public function onLeadSaveClick()
	{
		$target = is_numeric($this->controls['TargetLead']->getProperty('value')) ? $this->controls['TargetLead']->getProperty('value') : null;

		// Save User Access Rules
		if (empty($target))
			return $this->controls['autoSave']->getProperty('value') === 'on' ? false : ErrorHandler::msgNotice("Please select a lead from the menu");

		if ($this->controls['LeadUserList']->getProperty('value'))
		{
			$items = $this->controls['LeadUserList']->getProperty('items');
			$value = $this->controls['LeadUserList']->getProperty('value');
			$value = explode(',',$value);

			if (is_array($items))
				foreach($value as $id => $checked)
					$checked == 'on'
						? db::query("REPLACE INTO {$this->prefix}crm_leads_access SET `UID`={$items[$id]['value']}, `LID`={$target}")
						: db::query("DELETE FROM {$this->prefix}crm_leads_access WHERE `UID`={$items[$id]['value']} AND `LID`={$target}");
		}

		// Check isAdmin or isCreator
		$lead_result = db::query("SELECT CreatedBy FROM {$this->prefix}crm_leads WHERE ID = $target");

		if ( Auth::isAdmin() || ($lead_result->num_rows() && $lead_result->fetch_object()->CreatedBy == $this->user) )
		{
			$managedby = db::escape($this->controls['LeadManagedBy']->getProperty('value')) ?: 'NULL';
			db::query("UPDATE {$this->prefix}crm_leads SET `ManagedBy`= $managedby WHERE ID = $target");
			db::query("DELETE FROM {$this->prefix}crm_leads_access WHERE UID = $managedby AND LID = $target");
		}
	}

	public function onClientSaveClick()
	{
		$target = $this->controls['TargetClient']->getProperty('value');

		// Save User Access Rules
		if (empty($target))
			return $this->controls['autoSave']->getProperty('value') === 'on' ? false : ErrorHandler::msgNotice("Please select a client from the menu");

		if ($this->controls['ClientUserList']->getProperty('value'))
		{
			$items = $this->controls['ClientUserList']->getProperty('items');
			$value = $this->controls['ClientUserList']->getProperty('value');
			$value = explode(',',$value);

			if (is_array($items))
				foreach($value as $id => $checked)
					$checked == 'on'
						? db::query("REPLACE INTO {$this->prefix}crm_clients_access SET `UID`={$items[$id]['value']}, `CID`={$target}")
						: db::query("DELETE FROM {$this->prefix}crm_clients_access WHERE `UID`={$items[$id]['value']} AND `CID`={$target}");
		}

		// Check isAdmin or isCreator
		$client_result = db::query("SELECT CreatedBy FROM {$this->prefix}crm_clients WHERE ID = $target");

		if ( Auth::isAdmin() || ($client_result->num_rows() && $client_result->fetch_object()->ID == $this->user) )
		{
			$managedby = db::escape($this->controls['ClientManagedBy']->getProperty('value')) ?: 'NULL';
			db::query("UPDATE {$this->prefix}crm_clients SET `ManagedBy`= $managedby WHERE ID = $target");
			db::query("DELETE FROM {$this->prefix}crm_clients_access WHERE UID = $managedby AND CID = $target");
		}
	}

	public function onAutoSaveClick()
	{
		$ctls = WebCRM::getctlControls($this->controls['TabList'], array(), array('ctlMultiSelect'=>null));

		if ($this->controls['autoSave']->getProperty('value') == 'on')
		{
			$this->controls['EmployeeSave']->setProperty('enabled', 'false');
			$this->controls['ContactSave']->setProperty('enabled', 'false');
			$this->controls['LeadSave']->setProperty('enabled', 'false');
			$this->controls['ClientSave']->setProperty('enabled', 'false');

			foreach($ctls as $ctl)
				$ctl->setEvent('click');
		}
		else
		{
			$this->controls['EmployeeSave']->setProperty('enabled', 'true');
			$this->controls['ContactSave']->setProperty('enabled', 'true');
			$this->controls['LeadSave']->setProperty('enabled', 'true');
			$this->controls['ClientSave']->setProperty('enabled', 'true');

			foreach($ctls as $ctl)
				$ctl->unsetEvent('click');
		}
	}

	public function onEmployeeUserListClick()
	{
		$this->onEmployeeSaveClick();
	}

	public function onContactUserListClick()
	{
		$this->onContactSaveClick();
	}

	public function onLeadUserListClick()
	{
		$this->onLeadSaveClick();
	}

	public function onClientUserListClick()
	{
		$this->onClientSaveClick();
	}

	public function onCancelEmployeesClick()
	{
		$this->clearAccessLists();
	}

	public function onEmployeeCancelClick()
	{
		$this->controls['EmployeeUserList']->dump();
		$this->controls['EmployeeGroupList']->dump();
		$this->controls['TargetEmployee']->clear();
	}


	public function onContactCancelClick()
	{
		if (isset($this->controls['UserAccessMainWindow']))
			$this->close();

		$this->controls['ContactUserList']->dump();
		$this->controls['ContactGroupList']->dump();
		$this->controls['TargetContact']->clear();
	}


	public function onLeadCancelClick()
	{
		if (isset($this->controls['UserAccessMainWindow']))
			$this->close();

		$this->controls['LeadUserList']->dump();
		$this->controls['LeadGroupList']->dump();
		$this->controls['TargetLead']->clear();
	}


	public function onClientCancelClick()
	{
		if (isset($this->controls['UserAccessMainWindow']))
			$this->close();

		$this->controls['ClientUserList']->dump();
		$this->controls['ClientGroupList']->dump();
		$this->controls['TargetClient']->clear();
	}

	public function onLeadManagedByChange()
	{
		$managedby = $this->controls['LeadManagedBy']->getProperty('value');
		$lead = $this->controls['TargetLead']->getProperty('value');

		if (is_numeric($managedby) && is_numeric($lead))
			db::query("UPDATE {$this->prefix}crm_leads SET ManagedBy = $managedby WHERE ID = $lead");

		$this->controls['LeadUserList']->dump();
		$this->onTargetLeadChange();
		$this->onLeadGroupListChgroup();
	}

	public function onClientManagedByChange()
	{
		$managedby = $this->controls['ClientManagedBy']->getProperty('value');
		$client = $this->controls['TargetClient']->getProperty('value');

		if (is_numeric($managedby) && is_numeric($client))
			db::query("UPDATE {$this->prefix}crm_clients SET ManagedBy = $managedby WHERE ID = $client");

		$this->controls['ClientUserList']->dump();
		$this->onTargetClientChange();
		$this->onClientGroupListChgroup();
	}

	/*
		Populate Data
	*/
	public function fillTarget($type = 'all')
	{
		switch($type)
		{
			case 'employee':
				$this->controls['TargetEmployee']->setProperty('items', Auth::isAdmin() ? WebCRMemployee::getALLEmployees() : WebCRMemployee::getAssignedEmployees($this->user));
				break;

			case 'contact':
				$this->controls['TargetContact']->setProperty('items', Auth::isAdmin() ? WebCRMContact::getContactsList() : WebCRMContact::getAssignedContacts($this->user));
				break;

			case 'lead':
				$this->controls['TargetLead']->setProperty('items', Auth::isAdmin() ? WebCRMlead::getLeadsList() : WebCRMlead::getLeadsList($this->user));
				$this->controls['LeadManagedBy']->setProperty('items', Auth::isAdmin() ? WebCRM::getEmployeeUsers() : WebCRM::getEmployeeUsers($this->user));
				break;

			case 'client':
				$this->controls['TargetClient']->setProperty('items', Auth::isAdmin() ? WebCRMclient::getClientsList() : WebCRMclient::getClientsList($this->user));
				$this->controls['ClientManagedBy']->setProperty('items', Auth::isAdmin() ? WebCRM::getEmployeeUsers() : WebCRM::getEmployeeUsers($this->user));
				break;

			default:
				$this->fillTarget('employee');
				$this->fillTarget('contact');
				$this->fillTarget('lead');
				$this->fillTarget('client');
				break;
		}
	}

	public function fillUser($type = NULL, $id = NULL, $group = NULL, &$control = NULL)
	{
		if (is_null($type) || is_null($id) || is_null($group) || is_null($control))
			return;

		switch($type)
		{
			case 'employee':
				$access_table = db::prefix('crm_useraccess');
				$table = "";
				$id = WebCRMemployee::getUserID($id);
				$id_col = 'UID';
				$access_col = 'EID';
				$employee_col = 'ID';
				$where = "AND e.UID != $id";
				break;

			case 'contact':
				$access_table = db::prefix('crm_contacts_access');
				$table = "LEFT JOIN {$this->prefix}crm_contacts t ON (t.ID = access.CID AND t.CreatedBy != e.UID)";
				$id_col = 'CID';
				$access_col = 'UID';
				$employee_col = 'UID';
				$where = 'AND (t.CreatedBy IS NULL OR e.UID != t.CreatedBy)';
				break;

			case 'lead':
				$access_table = db::prefix('crm_leads_access');
				$table = "LEFT JOIN {$this->prefix}crm_leads t ON ((t.ManagedBy != e.UID OR t.ManagedBy IS NULL) OR (t.CreatedBy != e.UID OR t.CreatedBy IS NULL))";
				$id_col = 'LID';
				$access_col = 'UID';
				$employee_col = 'UID';
				$where = "AND t.ID = $id AND (t.ManagedBy IS NULL OR e.UID != t.ManagedBy) AND (e.UID != t.CreatedBy OR t.CreatedBy IS NULL)";
				break;

			case 'client':
				$access_table = db::prefix('crm_clients_access');
				$table = "LEFT JOIN {$this->prefix}crm_clients t ON ((t.ManagedBy != e.UID OR t.ManagedBy IS NULL) OR (t.CreatedBy != e.UID OR t.CreatedBy IS NULL))";
				$id_col = 'CID';
				$access_col = 'UID';
				$employee_col = 'UID';
				$where = "AND t.ID = $id AND (t.ManagedBy IS NULL OR e.UID != t.ManagedBy) AND (t.CreatedBy IS NULL OR e.UID != t.CreatedBy)";
				break;
		}

		$items = array();
		$values = array();

		$employee_results = Auth::isAdmin()
			? db::query(<<<SQL
SELECT DISTINCT e.ID, e.UID, CONCAT(e.FirstName, ' ', e.LastName) as Name, IF(!ISNULL(access.{$id_col}), 1, 0) as Selected
FROM `{$this->prefix}crm_employees` e
LEFT JOIN $access_table access ON (access.$access_col = e.$employee_col AND access.$id_col = $id)
$table
INNER JOIN {$this->prefix}user_groups ug ON ug.UserID = e.UID
WHERE (ug.GroupID = $group $where)
SQL
				)
			: db::query(<<<SQL
SELECT DISTINCT e.ID, e.UID, CONCAT(e.FirstName, ' ', e.LastName) as Name, IF(!ISNULL(access.{$id_col}), 1, 0) as Selected
FROM `{$this->prefix}crm_employees` e
LEFT JOIN $access_table access ON (access.$access_col = e.$employee_col AND access.$id_col = $id)
$table
INNER JOIN {$this->prefix}user_groups ug ON ug.UserID = e.UID
WHERE (
ug.GroupID = $group AND e.ID IN (
	SELECT EID
	FROM {$this->prefix}crm_useraccess
	WHERE UID = {$this->user}
)
$where
)
SQL
				);

		if ( $employee_results->num_rows() )
		{
			while( $employee = $employee_results->fetch_object() )
			{
				$items[] = array('txt' => $employee->Name, 'value' => $employee->UID, 'tooltip'=>WebCRMemployee::getTooltip($employee->ID), 'enabled'=>false);
				$values[] = $employee->Selected ? 'on' : 'off';
			}
		}

		$control->setProperty('items', $items);
		$control->setProperty('value', implode(',', $values));
	}

	public function generateTreeItems($groups, $ParentID = '')
	{
		$items = array();

		foreach($groups as $group)
		{
			$items[] = array(
				'value' => $group['value'],
				'txt'   => $group['txt'],
				'onclick' => 'this.chgroup();'
			);
		}

		return $items;
	}

}

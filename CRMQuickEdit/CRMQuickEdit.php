<?php

class CRMQuickEdit extends CRMQuickAdd
{
	public function onBeforeLoadState()
	{
		$this->user = Auth::get('UserID');

		$params = Request :: getParams();

		$this->tab = isset($params['tab']) ? $params['tab'] : 'Main';

		if ( !isset($params['type']) )
		{
			ErrorHandler::msgError('QuickEdit was launched incorrectly and will now terminate.');
			$this->close();
		}

		$this->contact = isset($params['id']) ? $params['id'] : NULL; // Set the ID of a contact being editted

		switch($params['type'])
		{
			case 'Contact':
				$this->table = db::prefix('crm_contacts');
				break;
			case 'Lead':
				$this->table = db::prefix('crm_leads');
				break;
			case 'Activity':
				$this->table = $params['source'] == 'Customer' ? db::prefix('crm_client_activities') : db::prefix('crm_lead_activities');
				$this->message_table = $params['source'] == 'Customer' ? db::prefix('crm_client_activity_message') : db::prefix('crm_lead_activity_message');
				$this->createNoteList();
				break;
		}


		$this->controls['Country']->setProperty('items', WebCRM::getCountriesItems());
		$this->controls['State']->setProperty('items', WebCRM::getStatesItems());
		$this->controls['AssignedTo']->setProperty('items', WebCRMemployee::getSelectEmployees($this->user));
		$this->controls['Type']->setProperty('items', WebCRM::getActivTypesItems());
		$this->controls['Status']->setProperty('items', WebCRM::getActivStatusItems());
		$this->controls['Priority']->setProperty('items', WebCRM::getActivPriorityItems());

		$this->populate();

	}

	public function populate()
	{
		$controls = WebCRM::getctlControls($this->controls[$this->tab], [], ['ctlTextBox'=>null, 'ctlAutoSelect'=>null, 'ctlSelect'=>null, 'ctlCalendar'=>null, 'ctlCheckBox'=>null]);
		$result = db::query( <<<SQL
SELECT *
FROM {$this->table}
WHERE ID = {$this->contact}
SQL
		);

		if ( $result->num_rows() )
			WebCRM::populateControls($controls, $result->fetch_assoc());

		$rr = db::query( "SELECT COUNT(*) AS total FROM {$this->prefix}contact_avatars WHERE ContactID = {$this->contact}" );

		if ( $rr->num_rows() && $rr->fetch_object()->total )
		{
			$this->controls["Thumb"]->setProperty( "value", "userimage.php?type=contact&id=".$this->contact );
			$this->controls["UseAvatar"]->setProperty( "value", "on" );
			$this->controls["Thumb"]->setStyle( "visibility", "inherit" );
			$this->controls["Image"]->setStyle( "visibility", "inherit" );
		}
		else
		{
			$this->controls["Thumb"]->setProperty( "value", "images/s.gif" );
			$this->controls["UseAvatar"]->setProperty( "value", "off" );
			$this->controls["Thumb"]->setStyle( "visibility", "hidden" );
			$this->controls["Image"]->setStyle( "visibility", "hidden" );
		}
	}

	public function onSaveActivityClick()
	{
		$errors = [];

		$values = db::escape(WebCRM::getctlControls($this->controls['AddInfoTab'], [], ['ctlTextBox'=>'value', 'ctlAutoSelect'=>'value', 'ctlSelect'=>'value', 'ctlCalendar'=>'value', 'ctlCheckBox'=>'value']));

		$values['DueTo'] = strtotime($values['DueTo']);
		$values['Remind'] = $values['Remind'] == 'on' ? 1 : 0;

		if ($values['AssignedTo'] == 0) unset($values['AssignedTo']);
		validate::integer($values['AssignedTo'], 'Assigned to field', null, null, true, $errors);
		validate::string($values['Name'], 'Subject field', 1, null, true, $errors);
		validate::integer($values['DueTo'], 'Date Due', 0, null, true, $errors);

		if (count($errors))
		{
			foreach($errors as $error)
				ErrorHandler::msgNotice($error);
			return false;
		}
		else
		{
			foreach($values as $k => $v)
				if ($this->controls[$k]->getProperty('db_type') == 'varchar' || $this->controls[$k]->getProperty('db_type') == 'text')
					$values[$k] = "$k='$v'";
				else
					$values[$k] = "$k=$v";

			$values = implode(', ', $values);

			// Insert record into Employees
			db::query( <<<SQL
UPDATE {$this->table}
SET $values
WHERE ID = {$this->contact}
SQL
			);
		}

		if (!count($errors))
			$this->close();
	}

	public function onSaveClick()
	{
		$errors  = [];

		// Get values out of controls
		$values = db::escape(WebCRM::getctlControls($this->controls[$this->tab], [], ['ctlTextBox'=>'value', 'ctlAutoSelect'=>'value', 'ctlSelect'=>'value']));

		// Validate input and set errors
		validate::string($values['FirstName'], 'Field: First Name', 1, 255, true, $errors);
		validate::string($values['LastName'], 'Field: Last Name', 1, 255, true, $errors);
		validate::string($values['Email'], 'Field: Email', 5, 255, true, $errors);
		if (count($values['Email']) > 5)validate::email($values['Email'], 'Field: Email', true, $errors);

		// Verify unique Email
		$query = <<<SQL
SELECT t.Email
FROM {$this->table} t
WHERE t.Email LIKE '{$values['Email']} AND t.ID != {$this->contact}'
SQL;

		$result = db::query($query);

		if (count($values['Email']) > 5 && $result->num_rows() > 0) $errors[] = "The email {$values['Email']} is already being used.";

		// Check the error list and decide whether to continue
		if (count($errors))
		{
			foreach($errors as $error)
				ErrorHandler::msgNotice($error);
			return false;
		}

		foreach($values as $k => $v)
			$values[$k] = "$k = '$v'";

		$values = "ModifiedBy = {$this->user}, " . implode(', ', $values);

		// Insert record into Employees
		db::query( <<<SQL
UPDATE {$this->table}
SET $values
WHERE ID = {$this->contact}
SQL
		);

		$this->saveAvatar();

		$this->close();
	}

	public function createNoteList()
	{
		// Close any open notes and ctls
		foreach($this->controls['tabnotes']->getChildren() as $ctl)
			$ctl->close();

		$defs = ResCRMQuickEdit::createnotelist($this->user, $this->contact, $this->message_table);

		foreach($defs as $def)
			$ctl = new $def['type']( $this, $def, $this->controls['tabnotes'] );

		$this->controls['ActNotesTabList']->show();
	}

	public function onaddNewNoteClick()
	{
		if ($this->user == null || $this->message_table == null || $this->contact == null)
			return;

		$errors = array(); // Error collection array

		$message = db::escape($this->controls['Message']->getProperty('value'));

		validate::string($message, 'Text', 1, null, true, $errors);

		if (count($errors))
		{
			foreach($errors as $error)
				ErrorHandler::msgNotice($error);

			return;
		}

		db::query("INSERT INTO {$this->message_table} (TaskID, Message, UserID) VALUES ({$this->contact}, '{$message}', {$this->user})");

		if ($this->controls['Remind']->getProperty('value') == 'on')
		{
			//send email to the employee.
			$res = db::query( "select Email from ".db::prefix( 'crm_employees' )." where ID=".$this->assignto );
			$row = $res->fetch_object();
			if ( isset( $row->Email ) )
			{
				$headers  = 'MIME-Version: 1.0' . "\r\n";
				$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
				// Additional headers
				$headers .= 'From: Web3box CRM <no-reply@web3box.com>' . "\r\n";
				$mailsub = CrmLang::_( 'Nou comentariu la activitate' )."'{$this->controls['Name']->getProperty('value')}";
				$mailmessage = CrmLang::_( 'Buna ziua, aveti un nou comentariu la o activitate' ).'.'.
					"<br /><br />{$message}<br /><br /><h6>".CrmLang::_( 'Pentru login' ).' <a href="http://www.web3box.com/">'.CrmLang::_( 'click aici' ).'</a>.</h6>';
				// Send the Email
				if(!mail($row->Email,$mailsub,$mailmessage,$headers))
					ErrorHandler::msgError('Email failed to send correctly');
			}
		}

		$this->createNoteList();


	}

	public function onEvent($ctlevent, $element, $subelement, $value)
	{
		if ($ctlevent=='onclick' && substr($element,0,6)=='Unlink')
		{
			list($event, $target, $id) = explode('XDELIMX', $element);

			switch($target)
			{
				case 'Note':
					db::query("DELETE FROM {$this->message_table} WHERE ID={$id}");
					break;
			}
			$this->createNoteList();
		}
	}

}

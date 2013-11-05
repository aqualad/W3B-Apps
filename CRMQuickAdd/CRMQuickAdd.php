<?php

class CRMQuickAdd extends Application
{
	public $file = NULL;
	private static $thumbSize = array( "width"=>100, "height"=>100 );

	public function onBeforeInit()
	{
		$this->prefix = db::prefix();
	}

	public function onMainWindowClose()
	{
		$this->close();
	}

	public function onBeforeLoadState()
	{
		$this->user = Auth::get('UserID');

		$params = Request::getParams();

		$this->tab = isset($params['tab']) ? $params['tab'] : 'Main';

		if ( !isset($params['type']) || !isset($params['source']) || !isset($params['assignto']) )
		{
			ErrorHandler::msgError('QuickAdd was started incorrectly (Unknown Type)');
			$this->close();
		}

		switch($params['type'])
		{
			case 'Contact':
				$this->table = db::prefix('crm_contacts');
				$this->assignto_field = $params['source'] == 'Customer'  ? 'ClientID' : 'LeadID'; // Use application that called quickadd (Customers, Leads, etc.) to determine assignto type
				break;
			case 'Lead':
				$this->table = db::prefix('crm_leads');
				$this->assignto_field = $params['source'] == 'Customer'  ? 'ClientID' : 'LeadID'; // Use application that called quickadd (Customers, Leads, etc.) to determine assignto type
				break;
			case 'Activity':
				$this->table = $params['source'] == 'Customer' ? db::prefix('crm_client_activities') : db::prefix('crm_lead_activities');
				$this->assignto_field = $params['source'] == 'Customer' ? 'ClientID' : 'LeadID';
				break;
		}
		$this->assignto = $params['assignto']; // Set ID of assignto target

		$this->controls['Country']->setProperty('items', WebCRM::getCountriesItems());
		$this->controls['State']->setProperty('items', WebCRM::getStatesItems());
		$this->controls['Type']->setProperty('items', WebCRM::getActivTypesItems());
		$this->controls['Status']->setProperty('items', WebCRM::getActivStatusItems());
		$this->controls['Priority']->setProperty('items', WebCRM::getActivPriorityItems());
	}

	public function onBeforeRender()
	{
		$this->controls['TabList']->setProperty( "value",  $this->tab);
		$params = Request :: getParams();
		if ( isset( $params["cmd"] ) )
			switch ( $params["cmd"] )
			{
			case "getThumb":
				$this->exportThumb();
				break;
			}
	}

	public function onImageUpload()
	{
		if ( !is_null( $this->file ) )
			if ( is_writable( $this->file ) )
				unlink( $this->file );

		$file = $this->controls["Image"]->getFile();
		if ( is_null( $file ) ) return;
		$this->file = $file["tmp_name"];

		$err  = array();
		if ( User :: checkAvatar( $this->file, $err ) )
			$this->controls["Thumb"]->setProperty( "value", html_entity_decode( Request :: build( array( "pid"=>$this->getPid(), "cmd"=>"getThumb", "rand"=>rand( 1, 100000 ) ) ) ) );
		ErrorHandler :: msgError( $err, "Image" );

		if ( $this->controls['UseAvatar']->getProperty('value') !== 'on' )
			$this->controls["UseAvatar"]->setProperty( "value", "on" );
	}

	public function exportThumb()
	{
		ob_end_clean();
		if ( $this->file == null )
		{
			header( "HTTP/1.0 404 Not Found" );
			exit;
		}
		header( "Content-type: image/png" );
		echo file_get_contents( $this->file );
		exit();
	}

	public function onCountryChange()
	{
		$this->controls['State']->setProperty('items', WebCRM::getStatesItems());
	}

	public function onSaveActivityClick()
	{
		$errors = [];

		$values = db::escape(WebCRM::getctlControls($this->controls['ActivityPanel'], [], ['ctlTextBox'=>'value', 'ctlAutoSelect'=>'value', 'ctlSelect'=>'value', 'ctlCalendar'=>'value', 'ctlCheckBox'=>'value']));

		$values['DueTo'] = strtotime($values['DueTo']);
		$values['Remind'] = $values['Remind'] == 'on' ? 1 : 0;

		if ($values['AssignedTo'] == 0) unset($values['AssignedTo']);
		if ($values['Type'] == 0) unset($values['Type']);
		if ($values['Status'] == 0) unset($values['Status']);
		if ($values['Priority'] == 0) unset($values['Priority']);
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
			$subject = $values['Name'];
			$description = $values['Description'] ?: '(No Description)';

			foreach($values as $k => $v)
				if ($this->controls[$k]->getProperty('db_type') == 'varchar' || $this->controls[$k]->getProperty('db_type') == 'text')
					$values[$k] = "'$v'";

			$fields = "ID, {$this->assignto_field}," . implode(', ', array_keys($values));
			$values = "'', {$this->assignto}," . implode(', ', $values);

			db::query( <<<SQL
INSERT INTO {$this->table} ($fields)
VALUES ($values)
SQL
			);
		}

		if (!count($errors))
		{
			if ($this->controls['Remind']->getProperty('value') == 'on')
			{
				//send email to the employee.
				$res = db::query( "SELECT Email FROM {$this->table} activ LEFT JOIN `w3b_crm_employees` e ON activ.AssignedTo = e.ID WHERE activ.ID = {$this->contact}" );
				if ($res->num_rows())
				{
					$row = $res->fetch_object();
					if ( isset( $row->Email ) )
					{
						$headers  = 'MIME-Version: 1.0' . "\r\n";
						$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
						// Additional headers
						$headers .= 'From: Web3box CRM <no-reply@web3box.com>' . "\r\n";
						$mailsub = CrmLang::_( 'Activitate noua asociata in CRM.' );
						$mailmessage = CrmLang::_( 'Salut,<br/><br/> Tocmai a fost asociata o noua activitate catre tine in CRM.' ).
							"<br/><br /><br />Subject: {$subject}<br/><br />Description: {$description}<br /><br /><h6>".CrmLang::_( 'Pentru login' ).' <a href="http://www.web3box.com/">'.CrmLang::_( 'click aici' ).'</a>.</h6>';
						// Send the Email
						if(!mail($row->Email,$mailsub,$mailmessage,$headers))
							ErrorHandler::msgError('Email failed to send correctly');
					}
				}
			}

			$this->close();
		}
	}

	public function onSaveClick()
	{
		$errors  = [];

		// Get values out of controls
		$values = db::escape(WebCRM::getctlControls($this->controls['Main'], [], ['ctlTextBox'=>'value', 'ctlAutoSelect'=>'value', 'ctlSelect'=>'value']));

		// Validate input and set errors
		validate::string($values['FirstName'], 'Field: First Name', 1, 255, true, $errors);
		validate::string($values['LastName'], 'Field: Last Name', 1, 255, true, $errors);
		validate::string($values['Email'], 'Field: Email', 5, 255, true, $errors);
		if (count($values['Email']) > 5)validate::email($values['Email'], 'Field: Email', true, $errors);

		// Verify unique Email
		$query = <<<SQL
SELECT t.Email
FROM {$this->table} t
WHERE t.Email LIKE '{$values['Email']}'
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
			$values[$k] = "'$v'";

		$fields = "ID, {$this->assignto_field}, CreatedBy, " . implode(', ', array_keys($values));
		$values = "'', {$this->assignto}, {$this->user}, ". implode(', ', $values);

		// Insert record into Employees
		db::query( <<<SQL
INSERT INTO {$this->table} ($fields)
VALUES ($values)
SQL
		);

		$this->saveAvatar();

		// Delete avatar file on disk
		if ( !is_null( $this->file ) )
			if ( is_writable( $this->file ) )
				unlink( $this->file );

		$this->close();
	}

	public function saveAvatar()
	{
		// Handle Contact Avatar
		if ( $this->controls['UseAvatar']->getProperty( 'value' ) == 'off' )
		{
			$this->controls["Thumb"]->setProperty( "value", 'images/s.gif' );
			db :: query("delete from ".db :: prefix("contact_avatars")." where ContactID={$this->contact} limit 1");
		}
		else
		{
			if ( $this->file != null )
			{
				$file_binary = db::escape(file_get_contents($this->file));
				$md5sum = db::escape(md5_file($this->file));
				$filesize = filesize($this->file);
				$time = time();

				db::query(<<<SQL
INSERT INTO {$this->prefix}contact_avatars (
ContactID, Contents, Created, Hash, Filesize
) VALUES(
{$this->contact}, '$file_binary', $time, '$md5sum', $filesize
)
SQL
				, false);
			}
		}
	}

	public function onCancelClick()
	{
		$this->close();
	}

	public function onCancelActivClick()
	{
		$this->close();
	}

	/*Check email availability*/
	public function onEmailcheckEmailAval()
	{
		$email = db :: escape( $this->controls["Email"]->getProperty( "value" ) );

		$and_where = isset($this->contact) ? "AND ID != {$this->contact}" : NULL;

		$r = db::query( "SELECT COUNT(*) as num_rows FROM {$this->table} WHERE Email = '". $email ."'" . $and_where );

		if ( $r->fetch_object()->num_rows > 0 )
		{
			ErrorHandler::msgNotice( CrmLang::_( "The email $email is already being used." ) );
			$this->controls['Email']->clear();
		}

		return false;
	}

}

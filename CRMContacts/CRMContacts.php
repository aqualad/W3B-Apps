<?php

	class CRMContacts extends Application
	{
		const APP_TYPE = APP_TYPE_PANEL;

		public static function getTitle()
		{
			return ($lang = lang :: get(get_called_class())) ? $lang->title : get_called_class();
		}

		public static function getDescription()
		{
			return ($lang = lang :: get(get_called_class())) ? $lang_description : get_called_class();
		}

		public function onAddContactClick()
		{
			$this->clearform();

			// Fill Companies, Countries, States
			$this->controls['company']->setProperty('items', WebCRM :: getCompaniesItems());
			$this->controls['country']->setProperty('items', WebCRM :: getCountriesItems());
			$this->controls['state']->setProperty('items', WebCRM :: getStatesItems());

			$this->controls['TabList']->setProperty("value",'ViewPanel');
		}

		public function onViewContactsClick()
		{
			$this->clearform();

			$this->controls['TabList']->setProperty('value','ListPanel');
		}

		public function onListContactsMenuEdit()
		{
			$this->onCancelClick();
			$this->onAddContactClick();

			$items = $this->controls['ListContacts']->getProperty('items');
			$val = $this->controls['ListContacts']->getProperty('value');

			if ( !isset($this->contact) )
				$this->contact = new CRMContact($items[$val[0]]->id);

			WebCRM::populateControls( $this->controls, $this->contact );

			ErrorHandler::checkpoint(__METHOD__);

		}

		public function onListContactsMenuDelete()
		{
			$this->controls['ListContacts']->getProperty('value');

			$items = $this->controls['ListContacts']->getProperty('items');
			$val = $this->controls['ListContacts']->getProperty('value');

			$contact = new CRMContact($items[$val[0]]->id);

			$contact->delete();

			$this->onViewContactsClick();

			ErrorHandler::checkpoint(__METHOD__);
		}

		public function onSaveClick()
		{
			$form_data = WebCRM::getctlControls( $this->controls['ViewPanel'], [], ['ctlTextBox' => 'value', 'ctlSelect' => 'value']);

			if ( !isset($this->contact) )
				$this->contact = new CRMContact();

			$this->contact->loadArray($form_data);

			$this->contact->save();

			ErrorHandler::checkpoint(__METHOD__);

			ErrorHandler::msgNotice('Saved');

			/*if ( isset($this->contact) )
				$contact = R::load(db::prefix('crm_contacts',$this->contact->id));*/
		}

		public function clearform($tabname = 'ViewPanel')
		{
			// Get Controls
			$form_controls = WebCRM::getctlControls($this->controls[$tabname], [], ['ctlTextBox' => null, 'ctlSelect' => null]);

			foreach($form_controls as $ctl)
			{
				$ctl->clear();
			}

		}

		public function onCancelClick()
		{
			unset($this->contact);
			$this->clearform();
			$this->controls['TabList']->setProperty('value','ListPanel');

			ErrorHandler::checkpoint(__METHOD__);
		}
	}

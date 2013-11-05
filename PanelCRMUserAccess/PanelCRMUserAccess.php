<?php

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 *  Class: CRMUserAccess                                     *
 *  Description: Handles the visibility and access between   *
 *  	all Users (Clients, Employees, etc.)                 *
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
	class PanelCRMUserAccess extends CRMUserAccess
	{
		const APP_TYPE = APP_TYPE_PANEL;

		public function onUserAccessMainPanelClose()
		{
			$this->close();
		}
	}

?>

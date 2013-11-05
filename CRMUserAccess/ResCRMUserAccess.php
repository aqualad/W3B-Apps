<?php

class ResCRMUserAccess extends ResPanelCRMUserAccess
{
	public static function ctlDefs()
	{
		return array(
			array(
				'type' => 'ctlWindow',
				'name' => 'UserAccessMainWindow',
				'style' => array( 'width' => '825px', 'height' => '540px'),
				'properties' => array(
					'position' => 'center',
					'scrolling' => true,
					'title' => 'CRM - User Access',
					'icon' => 'apps/CRM/icons/16/app.png'
				),
				'ctlDefs' => parent::ctlDefs()
			)
		);
	}
}

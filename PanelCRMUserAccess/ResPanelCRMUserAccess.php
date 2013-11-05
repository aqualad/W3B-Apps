<?php

	class ResPanelCRMUserAccess {

	public static function ctlDefs()
	{
		$lang = lang :: get("CrmLanguage");
		$req = new Request();
		$layout = new Layout();
		$iconset = Layout :: getIconset();
		$allimgs = ImageConfig :: extString();
		$delc1=CrmLang::_('Confirma Stergerea');
		$delc2=CrmLang::_('Esti sigur ca vrei sa stergi acest camp?');
		//Row Size
		define("R1_BASE", "8px");
		define("R2_BASE", "4px");
		define("ROW_SIZE",30); //Row Size
		//Col Size: [COL_X_Y] (X - Main Column Number, Y - Sub-Column Number)
		define("COL_1_1", "10px"); // Col 1, ctlLabel
		define("COL_1_2", "120px"); // Col 1, (ctlTextBox|ctlSelect|ctl...)
		define("COL_2_1", "380px"); // Col 2, ctlLabel
		define("COL_2_2", "490px"); // Col 2, (ctlTextBox|ctlSelect|ctl...)

		$r1 = R1_BASE; //ctlLabel Base Row Size (Offset from top)
		$r2 = R2_BASE; //ctl[Input] Base Row Size (Offset from top)

		function inc(&$r, $v=null)
		{
			return is_null($v) ? (($r+=ROW_SIZE)."px") : $r=$v;
		}

		$width = get_called_class() == 'ResCRMUserAccess' ? '825px' : '1000px';

		return array(
				array(
					"type"=>"ctlControl",
					"name"=>"UserAccessMainPanel",
					"properties"=>array("title"=>"CRM - Manage User Access", "icon"=>"apps/CRM/icons/16/app.png","scrolling"=>true),
					"style"=>array("width"=>$width, "height"=>'540px', "left"=>COL_1_2, "top"=>rand(0,10)."px"),
					"events"=>array("close"),
					"ctlDefs"=>array(
  //LEFT MENU
						array(
							"type"=>"ctlControl",
							"class"=>"AdminPanel",
							"name"=>"LeftArea",
							"style"=>array("width"=>'200px', "position"=>'relative', "float"=>'left', 'height'=>"300px"),
							"ctlDefs"=>array(
								array(
									"type"=>"ctlBigButton",
									"name"=>"ViewAccess",
									"properties"=>array("icon"=>"apps/CRM/icons/64/companies.png", "text"=>"Manage Access", "description"=>"Manage access among various Users (Employees, Customers, etc.)", "tabindex"=>"4"),
									"style"=>array("width"=>"210px"),
									"events"=>array('click'),
								),
								array(
									"type"=>"ctlBigButton",
									"name"=>"ViewUsers",
									"properties"=>array(
										"icon"=>"apps/CRM/icons/64/companies.png",
										"text"=>CrmLang::_("Gestiona Utilizatorii"),
										"description"=>CrmLang::_('Adauga sau elimina apartenenta la un grup de utilizatori, modifica datele de utilizator'),
										'tabindex'=>'5'
									),
									'style'=>array('width'=>'210px'),
									'csCode'=> <<<eojs
%INSTANCE_NAME%.onClick = function(event)
{
	createRequest('{$req->build(array('application'=>'AdminUsers', 'ajax'=>'true'))}', {});
}
eojs
								),
								array(
									"type"=>"ctlBigButton",
									"name"=>"ViewGroups",
									"properties"=>array(
										"icon"=>"apps/CRM/icons/64/companies.png",
										"text"=>CrmLang::_("Vizualizeaza / Editeaza Grupuri"),
										"description"=>CrmLang::_('Gestionati diverse grupuri'),
										'tabindex'=>'5'
									),
									'style'=>array('width'=>'210px'),
									'csCode'=> <<<eojs
%INSTANCE_NAME%.onClick = function(event)
{
	createRequest('{$req->build(array('application'=>'AdminGroups', 'ajax'=>'true'))}', {});
}
eojs
								),
							),
						),
  //END MENU

  //TAB LIST
						array(
							"type"=>"ctlControl",
							"class"=>"AdminPanel",
							"name"=>"RightArea",
							"style"=>array("left"=>'25px', "position"=>'relative', "float"=>'left','width'=>"770px","height"=>"540px"),
							"ctlDefs"=>array(
								array(
									'type'=>'ctlCheckBox',
									'name'=>'autoSave',
									'style'=>array('top'=>'91px', 'right'=>'32px', 'position'=>'absolute', 'width'=>'75px'),
									'properties'=>array('value'=>'off', 'text'=>'Auto-Save', 'alwaysOnTop'=>true),
									'events'=>array('click')
								),
								array(
									"type"=>"ctlTabList",
									"name"=>"TabList",
									"ctlDefs"=>array(
  //View Employees
										array(
											"type"=>"ctlControl",
											"class"=>"AdminPanel",
											"name"=>"ViewEmployees",
											"properties"=>array("txt"=>CrmLang::_('Lista Angajati')),
											"csCode"=><<<eohtml
%INSTANCE_NAME%.onHide = function()
{
	var ctl = proc[this.pid].controls.TabList;
	$(ctl.cid + '_button_ListPanel_div').style.display = 'none';
};
%INSTANCE_NAME%.onShow = function()
{
	var ctl = proc[this.pid].controls.TabList;
	$(ctl.cid + '_button_ListPanel_div').style.display = 'block';
};
eohtml
,
											"ctlDefs"=>array(
												array(
													"type"=>"ctlLabel",
													"name"=>"EmployeeIntro",
													"style"=>array("top"=>$r2, "left"=>COL_1_1, "color"=>"blue"),
													"properties"=>array("text"=>'<img src="'.Layout :: getIconset("16/info.png").'" />'.'Select the User to manage and assign Visibility',"allowTags"=>true),
												),
												array( //Info Tab
													"type"=>"ctlButton",
													"name"=>"EmployeeSave",
													"style"=>array("top"=>"4px", "right"=>"10px", "width"=>"100px"),
													"properties"=>array("text"=>CrmLang::_("Salveaza"), "icon"=>Layout :: getIconset("16/save.png")),
													"events"=>array("click"),
												),
												array(
													"type"=>"ctlButton",
													"name"=>"EmployeeCancel",
													"style"=>array("top"=>"4px", "right"=>"119px", "width"=>"100px"),
													"properties"=>array("text"=>CrmLang::_("Renunta"), "icon"=>Layout :: getIconset("16/close.png")),
													"events"=>array("click"),
												),
												array(
													'type'=>'ctlLabel',
													'name'=>'TargetEmployeel',
													'style'=>array('left'=>COL_1_1, 'top'=>inc($r1, $r1+ROW_SIZE*2).'px'),
													'properties'=>array('text'=>'Select Employee:'),
												),
												array(
													'type'=>'ctlAutoSelect',
													'name'=>'TargetEmployee',
													'style'=>array('left'=>COL_1_2, 'top'=>inc($r2, $r2+ROW_SIZE*2).'px', 'width'=>'200px'),
													'properties'=>array('value'=>0, 'items'=>array()),
													'events'=>array('change'),
												),
												array(
													"type"=>"ctlLinkButton",
													"name"=>"EmployeeRefreshFilter",
													"style"=>array( "top"=>($r1 - 2).'px', "left"=>COL_1_2+210 .'px', "width"=>"100px" ),
													"properties"=>array(
														"tabindex"=>'2',
														'text'=>CrmLang::_( "Reimprospateaza" ),
														'icon'=>Layout :: getIconset( '16/refresh.png' ),
														'visible'=>false
													),
													"events"=>array( "click" ),
											),
												array(
													'type'   => 'ctlTree',
													'name'   => 'EmployeeGroupList',
													'style'  => array(
														'left'   => COL_1_2,
														'top'    => inc($r1),
														'width'  => '259px',
														'height' => 440 - $r1 . 'px'
													),
													'properties' => array('value'=>'0'),
													'csCode' => <<<eojs
%INSTANCE_NAME%.chgroup = function()
{
  var val = this.items[this.properties.selectedID].value;
  sendServerSideEvent(this, 'chgroup', null);
};
eojs
													),
												array(
													"type"  => "ctlMultiSelect",
													"name"  => "EmployeeUserList",
													"style" => array(
														'left'    => COL_2_1,
														'top'     => ($r1 + 0 .'px'),
														'width'   => COL_1_2 .'px',
														'height'  => 400 - $r1 . 'px',
														'border'  => '1px solid black',
														'padding' => '20px',
													),
												),
											),
										),
//View Contacts
										array(
											"type"=>"ctlControl",
											"class"=>"AdminPanel",
											"name"=>"ViewContacts",
											"properties"=>array("txt"=>CrmLang::_('Manage Contacts')),
											"csCode"=><<<eohtml
%INSTANCE_NAME%.onHide = function()
{
	var ctl = proc[this.pid].controls.TabList;
	$(ctl.cid + '_button_ViewContacts_div').style.display = 'none';
};
%INSTANCE_NAME%.onShow = function()
{
	var ctl = proc[this.pid].controls.TabList;
	$(ctl.cid + '_button_ViewContacts_div').style.display = 'block';
};
eohtml
,
											"ctlDefs"=>array(
												array(
													"type"=>"ctlLabel",
													"name"=>"ContactIntro",
													"style"=>array("top"=>inc($r1, R1_BASE), "left"=>COL_1_1, "color"=>"blue"),
													"properties"=>array("text"=>'<img src="'.Layout :: getIconset("16/info.png").'" />'.'Select either Contacts or Leads to manage',"allowTags"=>true),
												),
												array( //Info Tab
													"type"=>"ctlButton",
													"name"=>"ContactSave",
													"style"=>array("top"=>inc($r2, R2_BASE), "right"=>"10px", "width"=>"100px"),
													"properties"=>array("text"=>CrmLang::_("Salveaza"), "icon"=>Layout :: getIconset("16/save.png")),
													"events"=>array("click"),
												),
												array(
													"type"=>"ctlButton",
													"name"=>"ContactCancel",
													"style"=>array("top"=>"4px", "right"=>"119px", "width"=>"100px"),
													"properties"=>array("text"=>CrmLang::_("Renunta"), "icon"=>Layout :: getIconset("16/close.png")),
													"events"=>array("click"),
												),
												array(
													'type'=>'ctlLabel',
													'name'=>'TargetContactl',
													'style'=>array('left'=>COL_1_1, 'top'=>inc($r1, $r1+ROW_SIZE*2).'px'),
													'properties'=>array('text'=>'Select Contact:'),
												),
												array(
													'type'=>'ctlAutoSelect',
													'name'=>'TargetContact',
													'style'=>array('left'=>COL_1_2, 'top'=>inc($r2, $r2+ROW_SIZE*2).'px', 'width'=>'200px'),
													'properties'=>array('value'=>0),
													'events'=>array('change'),
												),
												array(
													'type'   => 'ctlTree',
													'name'   => 'ContactGroupList',
													'style'  => array(
														'left'   => COL_1_2,
														'top'    => inc($r1),
														'width'  => '259px',
														'height' => 440 - $r1 . 'px'
													),
													'properties' => array('value'=>'0'),
													'csCode' => <<<eojs
%INSTANCE_NAME%.chgroup = function()
{
  var val = this.items[this.properties.selectedID].value;
  sendServerSideEvent(this, 'chgroup', null);
};
eojs
													),
												array(
													"type"  => "ctlMultiSelect",
													"name"  => "ContactUserList",
													"style" => array(
														'left'    => COL_2_1,
														'top'     => ($r1 + 0 .'px'),
														'width'   => COL_1_2 .'px',
														'height'  => 400 - $r1 . 'px',
														'border'  => '1px solid black',
														'padding' => '20px',
													),
												),
											),
										),
//View Leads
										array(
											"type"=>"ctlControl",
											"class"=>"AdminPanel",
											"name"=>"ViewLeads",
											"properties"=>array("txt"=>CrmLang::_('Manage Leads')),
											"csCode"=><<<eohtml
%INSTANCE_NAME%.onHide = function()
{
	var ctl = proc[this.pid].controls.TabList;
	$(ctl.cid + '_button_ViewLeads_div').style.display = 'none';
};
%INSTANCE_NAME%.onShow = function()
{
	var ctl = proc[this.pid].controls.TabList;
	$(ctl.cid + '_button_ViewLeads_div').style.display = 'block';
};
eohtml
,
											"ctlDefs"=>array(
												array(
													"type"=>"ctlLabel",
													"name"=>"LeadIntro",
													"style"=>array("top"=>inc($r1, R1_BASE), "left"=>COL_1_1, "color"=>"blue"),
													"properties"=>array("text"=>'<img src="'.Layout :: getIconset("16/info.png").'" />'.'Select Leads to manage',"allowTags"=>true),
												),
												array( //Info Tab
													"type"=>"ctlButton",
													"name"=>"LeadSave",
													"style"=>array("top"=>inc($r2, R2_BASE), "right"=>"10px", "width"=>"100px"),
													"properties"=>array("text"=>CrmLang::_("Salveaza"), "icon"=>Layout :: getIconset("16/save.png")),
													"events"=>array("click"),
												),
												array(
													"type"=>"ctlButton",
													"name"=>"LeadCancel",
													"style"=>array("top"=>"4px", "right"=>"119px", "width"=>"100px"),
													"properties"=>array("text"=>CrmLang::_("Renunta"), "icon"=>Layout :: getIconset("16/close.png")),
													"events"=>array("click"),
												),
												array(
													'type'=>'ctlLabel',
													'name'=>'TargetLeadl',
													'style'=>array('left'=>COL_1_1, 'top'=>inc($r1, $r1+ROW_SIZE*2).'px'),
													'properties'=>array('text'=>'Select Lead:'),
												),
												array(
													'type'=>'ctlAutoSelect',
													'name'=>'TargetLead',
													'style'=>array('left'=>COL_1_2, 'top'=>inc($r2, $r2+ROW_SIZE*2).'px', 'width'=>'200px'),
													'properties'=>array('value'=>0),
													'events'=>array('change'),
												),
												array(
													'type' => 'ctlLabel',
													'name' => 'LeadManagedByl',
													'style' => array('left' => COL_2_1, 'top' => $r1.'px'),
													'properties' => array('text'=>'Managed by:', 'visible' => false)
												),
												array(
													'type' => 'ctlAutoSelect',
													'name' => 'LeadManagedBy',
													'style' => array('left' => COL_2_2, 'top' => $r2.'px', 'width' => '200px'),
													'properties' => array('value' => 0, 'visible' => false),
													'events' => array( 'change' ),
												),
												array(
													'type'   => 'ctlTree',
													'name'   => 'LeadGroupList',
													'style'  => array(
														'left'   => COL_1_2,
														'top'    => inc($r1),
														'width'  => '259px',
														'height' => 440 - $r1 . 'px'
													),
													'properties' => array('value'=>'0'),
													'csCode' => <<<eojs
%INSTANCE_NAME%.chgroup = function()
{
  var val = this.items[this.properties.selectedID].value;
  sendServerSideEvent(this, 'chgroup', null);
};
eojs
													),
												array(
													"type"  => "ctlMultiSelect",
													"name"  => "LeadUserList",
													"style" => array(
														'left'    => COL_2_1,
														'top'     => ($r1 + 0 .'px'),
														'width'   => COL_1_2 .'px',
														'height'  => 400 - $r1 . 'px',
														'border'  => '1px solid black',
														'padding' => '20px',
													),
												),
											),
										),
//View Customers
										array(
											"type"=>"ctlControl",
											"class"=>"AdminPanel",
											"name"=>"ViewClients",
											"properties"=>array("txt"=>CrmLang::_('Manage Customers')),
											"csCode"=><<<eohtml
%INSTANCE_NAME%.onHide = function()
{
	var ctl = proc[this.pid].controls.TabList;
	$(ctl.cid + '_button_ViewClients_div').style.display = 'none';
};
%INSTANCE_NAME%.onShow = function()
{
	var ctl = proc[this.pid].controls.TabList;
	$(ctl.cid + '_button_ViewClients_div').style.display = 'block';
};
eohtml
,
											"ctlDefs"=>array(
												array(
													"type"=>"ctlLabel",
													"name"=>"ClientIntro",
													"style"=>array("top"=>inc($r1, R1_BASE), "left"=>COL_1_1, "color"=>"blue"),
													"properties"=>array("text"=>'<img src="'.Layout :: getIconset("16/info.png").'" />'.'Select Customers to manage',"allowTags"=>true),
												),
												array( //Info Tab
													"type"=>"ctlButton",
													"name"=>"ClientSave",
													"style"=>array("top"=>inc($r2, R2_BASE), "right"=>"10px", "width"=>"100px"),
													"properties"=>array("text"=>CrmLang::_("Salveaza"), "icon"=>Layout :: getIconset("16/save.png")),
													"events"=>array("click"),
												),
												array(
													"type"=>"ctlButton",
													"name"=>"ClientCancel",
													"style"=>array("top"=>"4px", "right"=>"119px", "width"=>"100px"),
													"properties"=>array("text"=>CrmLang::_("Renunta"), "icon"=>Layout :: getIconset("16/close.png")),
													"events"=>array("click"),
												),
												array(
													'type'=>'ctlLabel',
													'name'=>'TargetClientl',
													'style'=>array('left'=>COL_1_1, 'top'=>inc($r1, $r1+ROW_SIZE*2).'px'),
													'properties'=>array('text'=>'Select Customer:'),
												),
												array(
													'type'=>'ctlAutoSelect',
													'name'=>'TargetClient',
													'style'=>array('left'=>COL_1_2, 'top'=>inc($r2, $r2+ROW_SIZE*2).'px', 'width'=>'200px'),
													'properties'=>array('value'=>0),
													'events' => array( 'change' )
												),
												array(
													'type' => 'ctlLabel',
													'name' => 'ClientManagedByl',
													'style' => array('left' => COL_2_1, 'top' => $r1.'px'),
													'properties' => array('text'=>'Managed by:', 'visible' => false)
												),
												array(
													'type' => 'ctlAutoSelect',
													'name' => 'ClientManagedBy',
													'style' => array('left' => COL_2_2, 'top' => $r2.'px', 'width' => '200px'),
													'properties' => array('value' => 0, 'visible' => false),
													'events' => array( 'change' )
												),
												array(
													'type'   => 'ctlTree',
													'name'   => 'ClientGroupList',
													'style'  => array(
														'left'   => COL_1_2,
														'top'    => inc($r1),
														'width'  => '259px',
														'height' => 440 - $r1 . 'px'
													),
													'properties' => array('value'=>'0'),
													'csCode' => <<<eojs
%INSTANCE_NAME%.chgroup = function()
{
  var val = this.items[this.properties.selectedID].value;
  sendServerSideEvent(this, 'chgroup', null);
};
eojs
													),
												array(
													"type"  => "ctlMultiSelect",
													"name"  => "ClientUserList",
													"style" => array(
														'left'    => COL_2_1,
														'top'     => ($r1 + 0 .'px'),
														'width'   => COL_1_2 .'px',
														'height'  => 400 - $r1 . 'px',
														'border'  => '1px solid black',
														'padding' => '20px',
													),
												),
											),
										),
									),
								),
							),
						),
					),
				),
			);
		}
	}

<?php

class ResCRMCustomerAccess
{
	public static function ctlDefs()
	{
		$prefix = db::prefix();
		$delc1=CrmLang::_( 'Confirma Stergerea' );
		$delc2="Are you sure you wish to delete this Plan?";
		$iconset = Layout :: getIconset();
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
		return array(
			array(
				"type"=>"ctlControl",
				"name"=>"CustomerAccessMainWindow",
				"class"=>"grid10",
				"properties"=>array( "title"=>'Customer Access', "icon"=>"apps/CRM/icons/16/app.png", "scrolling"=>true ),
				"style"=>array( "height"=>"570px", "position"=>"relative" ),
				"events"=>array( "close" ),
				"ctlDefs"=>array(
					/* ================= desktop window ======================*/
					array(
						"type"=>"ctlControl",
						"name"=>"MainWindow",
						"class"=>"grid10",
						"properties"=>array( "title"=>"CRM - Customer Access", "icon"=>"apps/CRM/icons/16/app.png", "scrolling"=>true ),
						"style"=>array( "height"=>"570px", "position"=>"relative" ),
						"events"=>array( "close" ),
						"ctlDefs"=>array(
							//LEFT MENU
							array(
								"type"=>"ctlControl",
								"class"=>"AdminPanel",
								"name"=>"LeftArea",
								"style"=>array( "width"=>'200px', "position"=>'relative', "float"=>'left', 'height'=>"300px" ),
								"ctlDefs"=>array(
									array(
										"type"=>"ctlBigButton",
										"name"=>"ManageCustomers",
										"properties"=>array(
											"icon"=>"apps/CRM/icons/64/companies.png", "text"=>'Manage Customers',
											"description"=>'Peruse and/or modify current CRM Customers',
										),
										"style"=>array( "width"=>"210px" ),
										"events"=>array( 'click' ),
									),
									array(
										"type"=>"ctlBigButton",
										"name"=>"ManageUsers",
										"properties"=>array(
											"icon"=>"apps/CRM/icons/64/company.png",
											"text"=>'Manage Users',
											"description"=>'View, Edit, and/or Assign Customer created Users'
										),
										"style"=>array( "width"=>"210px" ),
										"events"=>array( 'click' ),
									),
									array(
										"type"=>"ctlBigButton",
										"name"=>"ManagePlans",
										"properties"=>array(
											"icon"=>"apps/CRM/icons/64/company.png",
											"text"=>'Manage Plans',
											"description"=>'Add or Edit existing CRM subscription plans'
										),
										"style"=>array( "width"=>"210px" ),
										"events"=>array( 'click' ),
									),
								),
							),
							//END MENU

							//TAB LIST
							array(
								"type"=>"ctlControl",
								"class"=>"AdminPanel",
								"name"=>"RightArea",
								"style"=>array( "left"=>'25px', "position"=>'relative', "float"=>'left', "height"=>"920px" ),
								"ctlDefs"=>array(

									array(
										"type"=>"ctlTabList",
										"name"=>"TabList",
										"ctlDefs"=>array(
											//LIST CUSTOMERS
											array(
												"type"=>"ctlControl",
												"class"=>"AdminPanel",
												"name"=>"ManageCustomersTab",
												"properties"=>array( "txt"=>'Customers'),
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
														"name"=>"customersintro",
														"style"=>array( "top"=>"4px", "left"=>"10px", "color"=>"blue" ),
														"properties"=>array( "text"=>'<img src="'.Layout :: getIconset( "16/info.png" ).'" />Peruse and/or modify current CRM Customers', "allowTags"=>true ),
													),
													array(
														'type'=>'ctlDbGrid',
														'name'=>'Customers',
														'style'=>array( 'top'=>'40px', 'left'=>'10px', 'bottom'=>'10px', 'width'=>'415px' ),
														'properties'=>array(
															"sql"=><<<SQL
SELECT c.ID, cs.billing_plan_id,
	CustomerName,
	COUNT(puc.portal_user_id) AS Employees,
	COALESCE(bp.Title, 'Unassigned') AS Plan
FROM w3b_crm_clients c
LEFT JOIN w3b_crm_client_subscriptions cs ON cs.client_id = c.ID
LEFT JOIN w3b_crm_billing_plans bp ON bp.ID = cs.billing_plan_id
LEFT JOIN w3b_crm_portal_users_clients puc ON puc.client_id = c.ID
WHERE 1
GROUP BY c.ID
SQL
															,
															"fields"=>array(
																array( 'type'=>'string', 'name'=>'CustomerName', 'title'=>CrmLang::_('Clienti') ),
																array( 'type'=>'string', 'name'=>'Employees', 'title'=>CrmLang::_( 'Angajat' ), 'filter'=>false ),
																array( 'type'=>'string', 'name'=>'Plan', 'title'=>'Plan', 'filter'=>false ),
															),
														),
														'events'=>array('click')
													),
													array(
														'type'=>'ctlControl',
														'name'=>'CustomerDetails',
														'style'=>array('top'=>'57px', 'left'=>'140px', 'position'=>'relative'),
														'properties'=>array('visible'=>false),
														'ctlDefs'=>array(
															array(
																'type'=>'ctlLabel',
																'name'=>'CustomerName',
																'class'=>'BoldLabel',
																'style'=>array('left'=>COL_2_1 + (COL_2_2 - COL_2_1)/2 . 'px', 'top'=>inc($r1, '30px')),
																'properties'=>array('allowTags'=>true, 'text'=>'')
															),
															array(
																'type'=>'ctlLabel',
																'name'=>'Planl',
																'class'=>'BoldLabel',
																'style'=>array('left'=>COL_2_1, 'top'=>inc($r1, '80px')),
																'properties'=>array('text'=>'Plan:')
															),
															array(
																'type'=>'ctlAutoSelect',
																'name'=>'Plan',
																'style'=>array('top'=>inc($r2, '76px'), 'left'=>COL_2_2),
																'properties'=>array('items'=>WebCRM::getBillingPlansItems('', 'None')),
																'events'=>array('change')
															),
															array(
																'type'=>'ctlLabel',
																'name'=>'Quotal',
																'style'=>array('top'=>inc($r1), 'left'=>COL_2_1 + 10 . 'px'),
																'properties'=>array('text'=>'')
															),
															array(
																'type'=>'ctlLabel',
																'name'=>'SubAmountl',
																'style'=>array('top'=>inc($r1), 'left'=>COL_2_1 + 10 . 'px'),
																'properties'=>array('text'=>'')
															),
															array(
																'type'=>'ctlLabel',
																'name'=>'SubPeriod',
																'style'=>array('top'=>inc($r1), 'left'=>COL_2_1 + 10 . 'px'),
																'properties'=>array('text'=>'')
															),
															array(
																'type'=>'ctlButton',
																'name'=>'SavePlan',
																'style'=>array('top'=>inc($r1), 'left'=>COL_2_2),
																'properties'=>array('text'=>CrmLang::_('Salveaza'), 'icon'=>Layout::getIconset('16/save.png'), 'visible'=>false),
																'events'=>array('click')
															),
															array(
																'type'=>'ctlControl',
																'name'=>'CustomerAssignedEmployees',
																'style'=>array('left'=>COL_1_1, 'top'=>inc($r1)),
																'ctlDefs'=>array(
																	array(
																		'type'=>'ctlLabel',
																		'name'=>'AssignedEmployeesl',
																		'class'=>'BoldLabel',
																		'style'=>array('left'=>COL_2_1 + (COL_2_2 - COL_2_1)/2 . 'px', 'top'=>'235px'),
																		'properties'=>array('allowTags'=>true, 'text'=>'<h3>Assigned Employees</h3>')
																	)
																)
															)
														)
													),
												)
											),
											array(
												"type"=>"ctlControl",
												"class"=>"AdminPanel",
												"name"=>"ManageUsersTab",
												"properties"=>array( "txt"=>'Users'),
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
														"name"=>"usersintro",
														"style"=>array( "top"=>"4px", "left"=>"10px", "color"=>"blue" ),
														"properties"=>array( "text"=>'<img src="'.Layout :: getIconset( "16/info.png" ).'" />View, Edit, and/or Assign Customer created Users', "allowTags"=>true ),
													),
													array(
												"type"=>"ctlLabel",
												"name"=>"WidgetLabel1",
												"class"=>"BoldLabel",
												"style"=>array( "top"=>"34px", "left"=>"20px" ),
												"properties"=>array( "text"=>'Unassigned Users' ),
											),
											array(
												"type"=>"ctlGrid",
												"name"=>"UnassignedUsers",
												"style"=>array( "top"=>"65px", "left"=>COL_1_1, "width"=>"330px", "height"=>"420px" ),
												"properties"=>array(
													'fields'=>array(
														array( 'name'=>'txt', 'type'=>'string', 'title'=>CrmLang::_( 'Nume' ) ),
													),
												),
											),
											array(
												"type"=>"ctlImageButton",
												"name"=>"widDel",
												"style"=>array( "top"=>"186px", "left"=>"342px" ),
												"properties"=>array( "icon"=>Layout :: getIconset( '16/arrow-left.png' ) ),
												"events"=>array( "click" ),
											),
											array(
												"type"=>"ctlImageButton",
												"name"=>"widUp",
												"style"=>array( "top"=>"162px", "left"=>"366px" ),
												"properties"=>array( "icon"=>Layout :: getIconset( '16/arrow-up.png' ) ),
												"events"=>array( "click" ),
											),
											array(
												"type"=>"ctlImageButton",
												"name"=>"widAdd",
												"style"=>array( "top"=>"186px", "left"=>"390px" ),
												"properties"=>array( "icon"=>Layout :: getIconset( '16/arrow-right.png' ) ),
												"events"=>array( "click" ),
											),
											array(
												"type"=>"ctlImageButton",
												"name"=>"widDown",
												"style"=>array( "top"=>"210px", "left"=>"366px" ),
												"properties"=>array( "icon"=>Layout :: getIconset( '16/arrow-down.png' ) ),
												"events"=>array( "click" ),
											),
											array(
												"type"=>"ctlAutoSelect",
												"name"=>"CustomerList",
												"class"=>"BoldLabel",
												"style"=>array( "top"=>"34px", "left"=>"412px", 'width'=>'320px' ),
											),
											array(
												"type"=>"ctlGrid",
												"name"=>"AssignedUsers",
												"style"=>array( "top"=>"65px", "left"=>"412px", "width"=>"330px", "height"=>"420px" ),
												"properties"=>array( 'sortable'=>false,
													'fields'=>array(
														array( 'name'=>'txt', 'type'=>'string', 'title'=>CrmLang::_( "Nume" ) ),
													),
												),
											),
												)
											),
											array(
												"type"=>"ctlControl",
												"class"=>"AdminPanel",
												"name"=>"ManagePlansTab",
												"properties"=>array( "txt"=>'Add/Edit Plans'),
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
														"name"=>"Manageplansintro",
														"style"=>array( "top"=>"4px", "left"=>"10px", "color"=>"blue" ),
														"properties"=>array( "text"=>'<img src="'.Layout :: getIconset( "16/info.png" ).'" />Add or Edit existing CRM subscription plans', "allowTags"=>true ),
													),
													array(
														'type'=>'ctlButton',
														'name'=>'delete',
														'style'=>array('top'=>'0px', 'right'=>'250px', 'width'=>'100px'),
														'properties'=>array('text'=>CrmLang::_('Sterge'), 'icon'=>Layout::getIconset('16/delete.png'), 'visible'=>false),
														"csCode"=> <<<eojs
%INSTANCE_NAME%.onClick = function()
{
	var dio = new ctlDialog(this.pid, null, this.name + "Dialog", null, {}, [], {title: '$delc1', text: '$delc2', buttons: 10, type: 'question'});
	dio.buttonCtl = this;
	dio.onReply = function(idx)
	{
		if(idx == this.ALERT_YES)
			sendServerSideEvent(this.buttonCtl, 'Confirm', null);
	}
}
eojs
													),
													array(
														'type'=>'ctlButton',
														'name'=>'save',
														'style'=>array('top'=>'0px', 'right'=>'10px', 'width'=>'100px'),
														'properties'=>array('text'=>CrmLang::_('Salveaza'), "icon"=>Layout :: getIconset( "16/save.png" )),
														'events'=>array('click')
													),
													array(
														'type'=>'ctlButton',
														'name'=>'cancel',
														'style'=>array('top'=>'0px', 'right'=>'130px', 'width'=>'100px'),
														'properties'=>array('text'=>CrmLang::_('Renunta'), "icon"=>Layout :: getIconset( "16/close.png" )),
														'events'=>array('click')
													),
													array(
														"type"=>"ctlTabList",
														"name"=>"ManagePlansTabList",
														"class"=>"noborders ctlTabList",
														"style"=>array( "height"=>"370px", "top"=>"30px" ),
														"ctlDefs"=>array(
															array(
																"type"=>"ctlControl",
																"name"=>"ManagePlansSection1",
																"properties"=>array( "txt"=>CrmLang::_( 'Informatii Generale' ) ),
																"ctlDefs"=>array(
																	array(
																		'type'=>'ctlTree',
																		'name'=>'plans',
																		'style'=>array('top'=>'4px', 'left'=>COL_1_1, 'width'=>'345px', 'height'=>'280px'),
																		'properties'=>array(
																			'items'=>array(), // Set in the application
																			'value'=>'0'
																		),
																		'csCode'=><<<eojs
%INSTANCE_NAME%.chplan = function()
{
  var val = this.items[this.properties.selectedID].value;
  sendServerSideEvent(this, 'chplan', null);
};
eojs
																	),
																	array(
																		"type"=>"ctlLabel",
																		"name"=>"Titlel",
																		"class"=>"BoldLabel",
																		"style"=>array( "top"=>inc($r1, '8px'), "left"=>COL_2_1 ),
																		"properties"=>array( "text"=>CrmLang::_( 'Titlu' ).':', 'required' => true ),
																	),
																	array(
																		'type'=>'ctlTextBox',
																		'name'=>'Title',
																		'style'=>array('top'=>inc($r2, '4px'), 'left'=>COL_2_2, 'width'=>'350px'),
																		'properties'=>array('db_type'=>'VARCHAR')
																	),
																	array(
																		'type'=>'ctlLabel',
																		'name'=>'Descriptionl',
																		'class'=>'BoldLabel',
																		'style'=>array('top'=>inc($r1), 'left'=>COL_2_1),
																		'properties'=>array('text'=>CrmLang::_('Descriere'))
																	),
																	array(
																		'type'=>'ctlTextBox',
																		'name'=>'Description',
																		'style'=>array('top'=>inc($r2), 'left'=>COL_2_2, 'height'=>'110px', 'width'=>'350px'),
																		'properties'=>array('multiline'=>true, 'db_type'=>'TEXT')
																	),
																	array(
																		'type'=>'ctlLabel',
																		'name'=>'BillPeriodl',
																		'class'=>'BoldLabel',
																		'style'=>array('top'=>inc($r1, '168px'), 'left'=>COL_2_1),
																		'properties'=>array('text'=>'Billing Period', 'required'=>true)
																	),
																	array(
																		'type'=>'ctlRadio',
																		'name'=>'BillPeriod',
																		'style'=>array('top'=>inc($r2, '164px'), 'left'=>COL_2_2),
																		'properties'=>array(
																			'items'=>array(
																				// array('value'=>'1 Month', 'txt'=>'1 Month (Monthly)'),
																				array('value'=>'6 Months', 'txt'=>'6 Months (Semi-Annually)'),
																				array('value'=>'12 Months', 'txt'=>'12 Months (Annually)')
																			),
																			'db_type'=>'VARCHAR'
																		)
																	),
																	array(
																		'type'=>'ctlLabel',
																		'name'=>'Amountl',
																		'class'=>'BoldLabel',
																		'style'=>array('top'=>inc($r1, '218px'), 'left'=>COL_2_1),
																		'properties'=>array('text'=>'Amount', 'required'=>true)
																	),
																	array(
																		'type'=>'ctlTextBox',
																		'name'=>'Amount',
																		'class'=>'price ctlTextBox',
																		'style'=>array('top'=>inc($r2, '214px'), 'left'=>COL_2_2),
																		'properties'=>array('db_type'=>'DECIMAL')
																	),
																	array(
																		'type'=>'ctlLabel',
																		'name'=>'MaxUsersl',
																		'class'=>'BoldLabel',
																		'style'=>array('top'=>inc($r1), 'left'=>COL_2_1),
																		'properties'=>array('text'=>'Max. Users', 'required'=>true)
																	),
																	array(
																		'type'=>'ctlTextBox',
																		'name'=>'MaxUsers',
																		'class'=>'CenteredTextBox numeric ctlTextBox',
																		'style'=>array('top'=>inc($r2), 'left'=>COL_2_2, 'width'=>'45px'),
																		'properties'=>array('maxlength'=>5, 'db_type'=>'INT')
																	),
																)
															)
														)
													)
												)
											),
										)
									)
								)
							)
						)
					)
				)
			)
		);
	}

	public static function getAssignedUsersList($assigned_users)
	{
		if ( ! count($assigned_users) )
			return false;

		$defs = array();
		$ctr = 0;

		foreach($assigned_users as $user)
		{
			$defs[] = array(
				'type' => 'ctlLabel',
				'name' => "AssignedUser{$ctr}",
				'style' => array('top'=>270 + ($ctr * 30) . 'px', 'left'=>'390px'),
				'properties' => array('text'=>$user['txt'], 'tooltip'=>WebCRMemployee::getTooltip($user['value']))
			);

			$ctr++;
		}

		return $defs;
	}
}

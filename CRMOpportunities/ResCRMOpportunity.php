<?php

class ResCRMOpportunity {


	public static function ctlDefs()
	{
		$user = Auth::isAdmin() ? NULL : (isset($_SESSION['auth']['UserID']) ? $_SESSION['auth']['UserID'] : NULL);
		$managedby_items = Auth::isAdmin() ? WebCRM::getEmployeeUsers() : WebCRM::getEmployeeUsers($user);

		$prefix = db::prefix();
		$delc1=CrmLang::_( 'Confirma Stergerea' );
		$delc2=CrmLang::_( 'Esti sigur ca vrei sa stergi acest oportunitate?' );
		$iconset = Layout :: getIconset();

		//Row Size
		define("R1_BASE", "8px");
		define("R2_BASE", "4px");
		define("ROW_SIZE",30); //Row Size
		//Col Size: [COL_X_Y] (X - Main Column Number, Y - Sub-Column Number)
		define("COL_1_1", "10px"); // Col 1, ctlLabel
		define("COL_1_2", "140px"); // Col 1, (ctlTextBox|ctlSelect|ctl...)
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
				"name"=>"OpportunityMainWindow",
				"class"=>"grid10",
				"properties"=>array( "title"=>CrmLang::_( "CRM - Oportunitati" ), "icon"=>"apps/CRM/icons/16/app.png", "scrolling"=>true ),
				"style"=>array( "height"=>"570px", "position"=>"relative" ),
				"events"=>array( "close" ),
				"ctlDefs"=>array(
					/* ================= desktop window ======================*/
					array(
						"type"=>"ctlControl",
						"name"=>"MainWindow",
						"class"=>"grid10",
						"properties"=>array( "title"=>CrmLang::_( "CRM - Oportunitati" ), "icon"=>"apps/CRM/icons/16/app.png", "scrolling"=>true ),
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
										"name"=>"ViewOpportunities",
										"properties"=>array(
											"icon"=>"apps/CRM/icons/64/companies.png", "text"=>CrmLang::_( 'List Opportunities' ),
											"description"=>CrmLang::_( 'Lista oportunitatile definite in sistem' ),
										),
										"style"=>array( "width"=>"210px" ),
										"events"=>array( 'click' ),
									),
									array(
										"type"=>"ctlBigButton",
										"name"=>"AddOpportunity",
										"properties"=>array(
											"icon"=>"apps/CRM/icons/64/company.png",
											"text"=>CrmLang::_( 'Adauga oportunitate' ),
											"description"=>CrmLang::_( 'Adaugati o oportunitate' )
										),
										"style"=>array( "width"=>"210px" ),
										"events"=>array( 'click' ),
									),
									array(
										"type"=>"ctlBigButton",
										"name"=>"RepOpportunity",
										"properties"=>array(
											"icon"=>"apps/CRM/icons/64/company.png",
											"text"=>CrmLang::_( 'Report' ),
											"description"=>CrmLang::_( 'Opportunity Report ' )
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
											//LIST COMPANIES
											array(
												"type"=>"ctlControl",
												"class"=>"AdminPanel",
												"name"=>"ListPanel",
												"properties"=>array( "txt"=>CrmLang::_( 'Opportunities' ) ),
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
														"name"=>"listintro",
														"style"=>array( "top"=>"4px", 'left'=>COL_1_1, "color"=>"blue" ),
														"properties"=>array( "text"=>'<img src="'.Layout :: getIconset( "16/info.png" ).'" />'.CrmLang::_( 'Click dreapta pe o inregistrare pentru stergere sau editare.' ), "allowTags"=>true ),
													),
													array(
														"type"=>"ctlLabel",
														"name"=>"FilterResultl",
														"style"=>array( "top"=>"42px", "left"=>'350px' ),
														"properties"=>array("text"=>"Result:")
													),
													array(
														"type"=>"ctlSelect",
														"name"=>"FilterResult",
														"style"=>array( "top"=>"40px", "left"=>'420px' ),
														"properties"=>array(
															"items"=>array(
																array('value'=>0, 'txt'=>'Any'),
																array('value'=>'In-Progress', 'txt'=>'In-Progress'),
																array('value'=>'Success', 'txt'=>'Success'),
																array('value'=>'Failure', 'txt'=>'Failure')
															),
															"value"=>3
														),
														"events"=>array('change')
													),
													array(
														"type"=>"ctlDbGrid",
														"name"=>"ListOps",
														"style"=>array( "top"=>"70px", 'left'=>COL_1_1, "right"=>"10px", "bottom"=>"10px" ),
														"properties"=>array(
															'popupMenu'=>'ListOpsMenu',

															"sql"=>WebCRM::getOpportunities($user, true),
															'value'=>$user,
															"fields"=>array(
																array( 'type'=>'string', 'name'=>'Name', 'title'=>CrmLang::_('Nume')),
																array( 'type'=>'string', 'name'=>'Account', 'title'=>CrmLang::_( 'Nume Cont' ), 'filter'=>false),
																array( 'type'=>'string', 'name'=>'Result', 'title'=>'Result', 'filter'=>false),
																array( 'type'=>'price', 'name'=>'Amount', 'title'=>'Amount', 'filter'=>false),
																array( 'type'=>'datetime', 'name'=>'ExpectedClose', 'title'=>'Expected Close Date', 'filter'=>false),
																array( 'type'=>'number', 'name'=>'Probability', 'title'=>'Probability (%)', 'filter'=>false),
															),
														),
													),
													array(
														"type"=>"ctlPopupMenu",
														"name"=>"ListOpsMenu",
														"events"=>array(),
														"properties"=>array( "items"=>array(
																array( 'title'=>CrmLang::_( 'Editeaza' ), 'icon'=>"{$iconset}16/group-edit.png", 'event'=>'Edit' ),
																array( 'title'=>CrmLang::_( 'Sterge' ), 'icon'=>"{$iconset}16/group-delete.png", 'code'=>'this.groupFDelete();' )
															)
														),
														"csCode"=> <<<eojs
%INSTANCE_NAME%.groupFDelete = function()
{
	var dio = new ctlDialog(this.pid, null, this.name + "Dialog", null, {}, [], {title: '$delc1', text: '$delc2', buttons: 10, type: 'question'});
	dio.buttonCtl = this;
	dio.onReply = function(idx)
	{
		if(idx == this.ALERT_YES)
			sendServerSideEvent(this.buttonCtl, 'Delete', null);
	}
}
eojs
													),
												),
											),





											//ADD COMPANY
											array(
												"type"=>"ctlControl",
												"class"=>"AdminPanel",
												"name"=>"ViewPanel",
												"properties"=>array( "txt"=>CrmLang::_( 'Adaugare/Editare' ) ),
												"csCode"=><<<eohtml
%INSTANCE_NAME%.onHide = function()
{
	var ctl = proc[this.pid].controls.TabList;
	$(ctl.cid + '_button_ViewPanel_div').style.display = 'none';
};
%INSTANCE_NAME%.onShow = function()
{
	var ctl = proc[this.pid].controls.TabList;
	$(ctl.cid + '_button_ViewPanel_div').style.display = 'block';
};
eohtml
												,
												"ctlDefs"=>array(
													array(
														"type"=>"ctlLabel",
														"name"=>"introtext",
														"class"=>"crmtitle",
														"style"=>array( "top"=>"4px", "left"=>"4px", "position"=>"absolute", "color"=>"blue" ),
														"properties"=>array( "text"=>'<img src="'.Layout :: getIconset( "16/info.png" ).'" />'.CrmLang::_( 'Aici puteti adauga un nou potential client sau modifica unul deja existent.' ), "allowTags"=>true ),
													),
													//Info Tab
													array(
														"type"=>"ctlButton",
														"name"=>"save",
														"style"=>array( "top"=>"4px", "right"=>"10px", "width"=>"100px" ),
														"properties"=>array( "text"=>CrmLang::_( "Salveaza" ), "icon"=>Layout :: getIconset( "16/save.png" ) ),
														"events"=>array( "click" ),
													),
													array(
														"type"=>"ctlButton",
														"name"=>"cancel",
														"style"=>array( "top"=>"4px", "right"=>"130px", "width"=>"100px" ),
														"properties"=>array( "text"=>CrmLang::_( "Renunta" ), "icon"=>Layout :: getIconset( "16/close.png" ) ),
														"events"=>array( "click" ),
													),
													array(
														"type"=>"ctlTabList",
														"name"=>"GenInfoTabList",
														"class"=>"noborders ctlTabList",
														"style"=>array( "height"=>"100%", "top"=>"30px" ),
														"ctlDefs"=>array(
															array(
																"type"=>"ctlControl",
																"name"=>"GeneralInfo",
																"properties"=>array( "txt"=>CrmLang::_( 'Informatii Generale' ) ),
																"ctlDefs"=>array(
																	array(
																		"type"=>"ctlLabel",
																		"name"=>"Namel",
																		"class"=>"BoldLabel",
																		"style"=>array( "top"=>$r1, 'left'=>COL_1_1 ),
																		"properties"=>array( "allowTags"=>true, "text"=>CrmLang::_( 'Numele oportunitate' ).':', 'required' => true ),
																	),
																	array(
																		"type"=>"ctlTextBox",
																		"name"=>"Name",
																		"style"=>array( "top"=>$r2, 'left'=>COL_1_2 ),
																		"properties"=>array( "tabindex"=>'1', 'db_type'=>'varchar' ),
																	),
																	array(
																		"type"=>"ctlLabel",
																		"name"=>"Currencyl",
																		"class"=>"BoldLabel",
																		"style"=>array( "top"=>inc($r1), 'left'=>COL_1_1 ),
																		"properties"=>array( "allowTags"=>true, "text"=>CrmLang::_('Moneda').':', 'required' => true),
																	),
																	array(
																		"type"=>"ctlAutoSelect",
																		"name"=>"Currency",
																		"style"=>array( "top"=>inc($r2), 'left'=>COL_1_2 ),
																		"properties"=>array( "tabindex"=>'2', 'db_type'=>'int' ),
																	),
																	array(
																		"type"=>"ctlLabel",
																		"name"=>"Amountl",
																		"class"=>"BoldLabel",
																		"style"=>array( "top"=>inc($r1), 'left'=>COL_1_1 ),
																		"properties"=>array( "text"=>CrmLang::_('Amount').':', 'required' => true),
																	),
																	array(
																		"type"=>"ctlTextBox",
																		'class'=>'price ctlTextBox',
																		"name"=>"Amount",
																		"style"=>array( "top"=>inc($r2), 'left'=>COL_1_2 ),
																		"properties"=>array( "tabindex"=>'3', 'db_type'=>'decimal' ),
																	),
																	array(
																		"type"=>"ctlLabel",
																		"name"=>"Probabilityl",
																		"class"=>"BoldLabel",
																		"style"=>array( "top"=>inc($r1), 'left'=>COL_1_1 ),
																		"properties"=>array( "text"=>CrmLang::_('Probabilitate').'(%):'),
																	),
																	array(
																		"type"=>"ctlTextBox",
																		"name"=>"Probability",
																		'class'=>'percentage ctlTextBox',
																		"style"=>array( "top"=>inc($r2), 'left'=>COL_1_2 ),
																		"properties"=>array( "tabindex"=>'4', 'db_type'=>'int'),
																	),
																	array(
																		"type"=>"ctlLabel",
																		"name"=>"ExpectedClosel",
																		"class"=>"BoldLabel",
																		"style"=>array( "top"=>inc($r1), 'left'=>COL_1_1 ),
																		"properties"=>array( "text"=>CrmLang::_('Data de sfarsit asteptat').':' ),
																	),
																	array(
																		"type"=>"ctlCalendar",
																		"name"=>"ExpectedClose",
																		"style"=>array( "top"=>inc($r2), 'left'=>COL_1_2, "width"=>"200px" ),
																		"properties"=>array( "tabindex"=>'6', 'db_type'=>'int' ),
																	),
																	array(
																		"type"=>"ctlLabel",
																		"name"=>"Descriptionl",
																		"class"=>"BoldLabel",
																		"style"=>array( "top"=>inc($r1), 'left'=>COL_1_1 ),
																		"properties"=>array( "text"=>CrmLang::_('Descriere'.':'), 'required' => true ),
																	),
																	array(
																		"type"=>"ctlTextBox",
																		"name"=>"Description",
																		"style"=>array( "top"=>inc($r2), 'left'=>COL_1_2, "width"=>"600px", "height"=>"100px" ),
																		"properties"=>array( "tabindex"=>'9' , 'multiline'=>true, 'db_type'=>'text'),
																	),
																	array(
																		"type"=>"ctlLabel",
																		"name"=>"Reasonl",
																		"class"=>"BoldLabel",
																		"style"=>array( "top"=>inc($r1, $r1+120 . 'px'), 'left'=>COL_1_1 ),
																		"properties"=>array( "text"=>CrmLang::_('motiv').':' )
																	),
																	array(
																		"type"=>"ctlTextBox",
																		"name"=>"Reason",
																		"style"=>array( "top"=>inc($r2, $r2+120 . 'px'), 'left'=>COL_1_2, 'width'=>'600px', 'height'=>'100px' ),
																		"properties"=>array( "tabindex"=>"9", 'db_type'=>'text', 'multiline'=>true )
																	),
																	array(
																		"type"=>"ctlLabel",
																		"name"=>"Accountl",
																		"class"=>"BoldLabel",
																		"style"=>array("top"=>inc($r1,"8px"), 'left'=>COL_2_1),
																		"properties"=>array("text"=>CrmLang::_('Nume Cont:')),
																	),
																	array(
																		"type"=>"ctlAutoSelect",
																		"name"=>"Account",
																		"style"=>array( "top"=>inc($r2,"4px"), 'left'=>COL_2_2 ),
																		"properties"=>array( "tabindex"=>'5', 'db_type'=>'int' ),
																	),
																	array(
																		"type"=>"ctlLabel",
																		"name"=>"ManagedByl",
																		"class"=>"BoldLabel",
																		"style"=>array( "top"=>inc($r1), 'left'=>COL_2_1 ),
																		"properties"=>array( "text"=>CrmLang::_('Administrat de:') ),
																	),
																	array(
																		"type"=>"ctlAutoSelect",
																		"name"=>"ManagedBy",
																		"style"=>array( "top"=>inc($r2), 'left'=>COL_2_2, "width"=>"200px" ),
																		"properties"=>array( "tabindex"=>'6', 'db_type'=>'int', 'items'=>$managedby_items, 'value'=>$user ),
																	),
																	array(
																		"type"=>"ctlLabel",
																		"name"=>"BestCasel",
																		"class"=>"BoldLabel",
																		"style"=>array( "top"=>inc($r1), 'left'=>COL_2_1 ),
																		"properties"=>array( "text"=>CrmLang::_('Cel mai bun caz').':' ),
																	),
																	array(
																		"type"=>"ctlTextBox",
																		"name"=>"BestCase",
																		"style"=>array( "top"=>inc($r2), 'left'=>COL_2_2, "width"=>"200px" ),
																		"properties"=>array( "tabindex"=>'7', 'db_type'=>'varchar' ),
																	),
																	array(
																		"type"=>"ctlLabel",
																		"name"=>"WorstCasel",
																		"class"=>"BoldLabel",
																		"style"=>array( "top"=>inc($r1), 'left'=>COL_2_1 ),
																		"properties"=>array( "text"=>CrmLang::_('Cel mai rau caz').':' ),
																	),
																	array(
																		"type"=>"ctlTextBox",
																		"name"=>"WorstCase",
																		"style"=>array( "top"=>inc($r2), 'left'=>COL_2_2, "width"=>"200px" ),
																		"properties"=>array( "tabindex"=>'8', 'db_type'=>'varchar' ),
																	),
																	array(
																		'type'=>'ctlLabel',
																		'name'=>'Resultl',
																		'class'=>'BoldLabel',
																		'style'=>array( 'top'=>inc($r1), 'left'=>COL_2_1 ),
																		'properties'=>array( 'text'=>'Result:' ),
																	),
																	array(
																		'type'=>'ctlRadio',
																		'name'=>'Result',
																		'class'=>'Inline ctlRadio',
																		'style'=>array( 'top'=>inc($r2), 'left'=>COL_2_2 ),
																		'properties'=>array(
																			'db_type'=>'varchar',
																			'items'=>array(
																				array('value'=>'In-Progress', 'txt'=>'In-Progress'),
																				array('value'=>'Success', 'txt'=>'Success'),
																				array('value'=>'Failure', 'txt'=>'Failure')
																			),
																			'value'=>3
																		)
																	)
																),
															),
														),
													),
												),
											),


											//Opportunity Report
											array(
												"type"=>"ctlControl",
												"class"=>"AdminPanel",
												"name"=>"ReportPanel",
												"properties"=>array( "txt"=>CrmLang::_( 'Opportunity Report' ) ),
												"ctlDefs"=>array(

													array(
														"type"=>"ctlControl",
														"name"=>"graph",
														"class"=>"testgraph",
														"style"=>array( "width"=>"100%", "minHeight"=>"300px", "overflow"=>"hidden" ),
														"ctlDefs"=>array(),
													),

												),
											),	// tab end


										),
									),
								),
							),
						),
					),
				),
			)
		);

	}

}

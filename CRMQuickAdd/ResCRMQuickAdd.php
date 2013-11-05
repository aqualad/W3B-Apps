<?php

class ResCRMQuickAdd
{

	public static function ctlDefs()
	{
		$height = get_called_class() == 'ResCRMQuickAdd' ? '400px' : '500px';
		$allimgs = ImageConfig :: extString(); //Used by Image Upload

		define('COL_1_1', '10px');
		define('COL_1_2', '140px');
		define('COL_2_1', '370px');
		define('COL_2_2', '500px');

		define("ROW_SIZE",30); //Row Size

		$r1 = '8px';
		$r2 = '4px';

		function inc(&$r, $v=null) {
				return is_null($v) ? (($r+=ROW_SIZE)."px") : $r=$v;
		}

		return [[
			'type'	=> 'ctlWindow',
			'name'	=> 'MainWindow',
			'style'	=> [ 'width' => '840px', 'height' => $height ],
			'properties' => [ 'position' => 'center',
				'minheight'	=> '300',
				'minwidth'	=> '660',
				'scrolling'	=> true,
				'title'		=> 'CRM - Quick Add',
				'icon'		=> 'apps/CRM/icons/16/app.png',
			],
			'events'	=> ['close'],
			'ctlDefs'	=> [ [
				'type' => 'ctlTabList',
				'name' => 'TabList',
				'style' => ['top'=>'-43px'],
				'ctlDefs' => [ [

					"type"=>'ctlControl',
					"class"=>'AdminPanel',
					"name"=>'Main',
					"style"=>array( 'position' => 'relative', 'float' => 'left', 'top' => '4px', 'width' => '825px', 'height' => '300px' ),
					"ctlDefs"=>[ [ //Info Tab
						"type"=>"ctlButton",
						"name"=>"save",
						"style"=>array( "top"=>$r2, "right"=>"10px", "width"=>"100px" ),
						"properties"=>array( "text"=>CrmLang::_( "Salveaza" ), "icon"=>Layout :: getIconset( "16/save.png" ) ),
						"events"=>array( "click" ),
					],
					[
						"type"=>"ctlButton",
						"name"=>"cancel",
						"style"=>array( "top"=>$r2, "right"=>"120px", "width"=>"100px" ),
						"properties"=>array( "text"=>CrmLang::_( "Renunta" ), "icon"=>Layout :: getIconset( "16/close.png" ) ),
						"events"=>array( "click" ),
					],
					[
						"type"=>"ctlTabList",
						"name"=>"GenInfoTabList",
						"class"=>"noborders ctlTabList",
						"style"=>array( "height"=>"120px", "top"=>"30px", "position"=>"absolute" ),
						"properties"=>array('text'=>CrmLang::_( 'Informatii Generale' ) ),
						"ctlDefs"=>[
							[
								"type"=>"ctlControl",
								"name"=>"GeneralInfo",
								"properties"=>array( "txt"=>CrmLang::_( 'Informatii Generale' ) ),
								"ctlDefs"=>[
									[
										"type"=>"ctlLabel",
										"name"=>"FirstNamel",
										"class"=>"BoldLabel",
										"style"=>array( "top"=>$r1, "left"=>COL_1_1, "width"=>COL_1_2 ),
										"properties"=>array( "allowTags"=>true, "text"=>CrmLang::_("Nume:"), 'required'=>true ),
									],
									[
										"type"=>"ctlTextBox",
										"name"=>"FirstName",
										"style"=>array( "top"=>$r2, "left"=>COL_1_2 ),
										"properties"=>array( "tabindex"=>'1' ),
									],
									[
										"type"=>"ctlLabel",
										"name"=>"LastNamel",
										"class"=>"BoldLabel",
										"style"=>array( "allowTags"=>true, "top"=>inc($r1), "left"=>COL_1_1, "width"=>COL_1_2 ),
										"properties"=>array( "allowTags"=>true, "text"=>CrmLang::_("Prenume:"), 'required'=>true ),
									],
									[
										"type"=>"ctlTextBox",
										"name"=>"LastName",
										"style"=>array( "top"=>inc($r2), "left"=>COL_1_2 ),
										"properties"=>array( "tabindex"=>'2' ),
									],
									[
										"type"=>"ctlCheckBox",
										"name"=>"UseAvatar",
										"style"=>array( "top"=>inc($r1, '8px'), "left"=>COL_2_1 ),
										"properties"=>array( "text"=>CrmLang::_( 'Utilizati avatar' ), "visible"=>true ),
										"csCode"=><<<eojs
%INSTANCE_NAME%.onChange = function()
{
if(this.properties.value == "on")
{
$(proc[this.pid].controls["Image"].cid).style.visibility = "inherit";
$(proc[this.pid].controls["Thumb"].cid).style.visibility = "inherit";
}
else
{
$(proc[this.pid].controls["Image"].cid).style.visibility = "hidden";
$(proc[this.pid].controls["Thumb"].cid).style.visibility = "hidden";
}
}
eojs
									],
									[
										"type"=>"ctlUploadSingle",
										"name"=>"Image",
										"style"=>array( "top"=>inc($r1, '19px'), "left"=>'465px', "visibility"=>"hidden" ),
										"properties"=>array( "extlist"=>$allimgs ),
										"csCode"=> <<<eojs
%INSTANCE_NAME%.onComplete = function()
{
sendServerSideEvent(this, 'keypress', null);
}
eojs
									],
									[
										"type"=>"ctlImage",
										"name"=>"Thumb",
										"class"=>"EditUserAvatarThumb",
										"style"=>array( "top"=>'0px', "left"=>'720px', 'maxWidth'=>'64px', 'maxHeight'=>'64px', 'position'=>'relative', "visibility"=>"hidden" ),
										"properties"=>array( "value"=>"images/s.gif" ),
									],
								],
							],
						],
					],

					//Address Tab

					array(
						"type"=>"ctlTablist",
						"name"=>"GenInfoTabList2",
						"class"=>"noborders ctlTabList",
						"style"=>array( "top"=>"150px", "height"=>"235px", "position"=>"absolute" ),
						"ctlDefs"=>array(
							array(
								"type"=>"ctlControl",
								"name"=>"GeneralInfo2",
								"properties"=>array( "txt"=>CrmLang::_( 'Informatii Contact' ) ),
								"ctlDefs"=>array(
									array(
										"type"=>"ctlLabel",
										"name"=>"Phonel",
										"class"=>"BoldLabel",
										"style"=>array( "top"=>inc($r1, '8px') , "left"=>COL_1_1 ),
										"properties"=>array( "text"=>CrmLang::_( 'Telefon fix:' ) ),
									),
									array(
										"type"=>"ctlTextBox",
										"name"=>"Phone",
										"class"=>'ctlTextBox phone-mask',
										"style"=>array( "top"=>inc($r2, '4px'), "left"=>COL_1_2 ),
										"properties"=>array( "tabindex"=>'3' ),
									),
									array(
										"type"=>"ctlLabel",
										"name"=>"Emaill",
										"class"=>"BoldLabel",
										"style"=>array( "top"=>inc($r1), "left"=>COL_1_1, "width"=>'100px' ),
										"properties"=>array( "text"=>CrmLang::_( 'Adresa email:' ), 'required'=>true ),
									),
									array(
										"type"=>"ctlTextBox",
										"name"=>"Email",
										"class"=>"mysupernastytextbox ctlTextBox",
										"style"=>array( "top"=>inc($r2), "left"=>COL_1_2 ),
										"properties"=>array( "tabindex"=>'4' ),
										/* DANIEL : here's the deal ... to obtain the control I do $(proc[this.pid].controls["email"]) ... this doesn't load the first time around for "obvious" reasons ... WTF! */
										"csCode"=> <<<eojs
acesta = $(proc[this.pid].controls["Email"]);
jQuery("div.mysupernastytextbox input.ctlTextBox_text").change( function() {
sendServerSideEvent( acesta, 'checkEmailAval', null);
} );
eojs
									),
									array(
										"type"=>"ctlLabel",
										"name"=>"Addressl",
										"class"=>"BoldLabel",
										"style"=>array( "top"=>inc($r1), "left"=>COL_1_1 ),
										"properties"=>array( "text"=>CrmLang::_( 'Adresa:' ) ),
									),
									array(
										"type"=>"ctlTextBox",
										"name"=>"Address",
										"style"=>array( "top"=>inc($r2), "left"=>COL_1_2, "width"=>"200px" ),
										"properties"=>array( "tabindex"=>'5' ),
									),
									array(
										"type"=>"ctlLabel",
										"name"=>"Countryl",
										"class"=>"BoldLabel",
										"style"=>array( "top"=>inc($r1, '8px'), "left"=>COL_2_1 ),
										"properties"=>array( "allowTags"=>true, "text"=>CrmLang::_( 'Tara:' ) ),
									),
									array(
										"type"=>"ctlAutoSelect",
										"name"=>"Country",
										"style"=>array( "top"=>inc($r2, '4px'), "left"=>COL_2_2, "width"=>"200px" ),
										"properties"=>array( "tabindex"=>'6' ),
										"events"=>array( "change" ),
									),
									array(
										"type"=>"ctlLabel",
										"name"=>"Statel",
										"class"=>"BoldLabel",
										"style"=>array( "top"=>inc($r1), "left"=>COL_2_1 ),
										"properties"=>array( "allowTags"=>true, "text"=>CrmLang::_( 'Judetul:' ) ),
									),
									array(
										"type"=>"ctlAutoSelect",
										"name"=>"State",
										"style"=>array( "top"=>inc($r2), "left"=>COL_2_2, "width"=>"200px" ),
										"properties"=>array( "tabindex"=>'7' ),
									),
									array(
										"type"=>"ctlLabel",
										"name"=>"Cityl",
										"class"=>"BoldLabel",
										"style"=>array( "top"=>inc($r1), "left"=>COL_2_1 ),
										"properties"=>array( "text"=>CrmLang::_( 'Orasul:' ) ),
									),
									array(
										"type"=>"ctlTextBox",
										"name"=>"City",
										"style"=>array( "top"=>inc($r2), "left"=>COL_2_2 ),
										"properties"=>array( "tabindex"=>'8' ),
									),
									array(
										"type"=>"ctlLabel",
										"name"=>"Notesl",
										"class"=>"BoldLabel",
										"style"=>array( "top"=>inc($r1), "left"=>COL_1_1 ),
										"properties"=>array( "text"=>CrmLang::_( 'Notite:' ) ),
									),
									array(
										"type"=>"ctlTextBox",
										"name"=>"Notes",
										"style"=>array( "top"=>inc($r2), "left"=>COL_1_2, "width"=>"560px", "height"=>"80px" ),
										"properties"=>array( "tabindex"=>'9', "multiline"=>'true' ),
									),
								),
							),
						),
					),

					] ], // Activity Panel
					array(
						"type"=>"ctlControl",
						"class"=>"AdminPanel",
						"name"=>"ActivityPanel",
						"properties"=>array( "txt"=>CrmLang::_( 'Activities' ) ),
						"ctlDefs"=>array(
							array(
								"type"=>"ctlLabel",
								"name"=>"activityintro",
								"style"=>array( "top"=>"4px", "left"=>COL_1_1, "color"=>"blue" ),
								"properties"=>array( "text"=>'<img src="'.Layout :: getIconset( "16/info.png" ).'" />'.CrmLang::_( 'Folositi formularul de mai jos pentru a adauga sau modifica o activitate.' ), "allowTags"=>true ),
							),
							array(
								"type"=>"ctlButton",
								"name"=>"saveActivity",
								"style"=>array( "top"=>"4px", "right"=>"10px", "width"=>"130px" ),
								"properties"=>array( "text"=>CrmLang::_( "Salveaza" ), "icon"=>Layout :: getIconset( "16/save.png" ) ),
								"events"=>array( "click" ),
							),
							array(
								"type"=>"ctlButton",
								"name"=>"cancelActiv",
								"style"=>array( "top"=>"4px", "right"=>"150px", "width"=>"100px" ),
								"properties"=>array( "text"=>CrmLang::_( "Renunta" ), "icon"=>Layout :: getIconset( "16/close.png" ) ),
								"events"=>array( "click" ),
							),
							array(
								"type"=>"ctlTabList",
								"name"=>"AddTabList",
								"class"=>"noborders ctlTabList",
								"style"=>array( "height"=>"330px", "top"=>"30px" ),
								"ctlDefs"=>array(
									array(
										"type"=>"ctlControl",
										"name"=>"AddInfoTab",
										"properties"=>array( "txt"=>CrmLang::_( 'Informatii Generale' ) ),
										"ctlDefs"=>array(
											array(
												"type"=>"ctlLabel",
												"name"=>"AssignedTol",
												"class"=>"BoldLabel",
												"style"=>array( "top"=>inc($r1, '8px'), "left"=>COL_1_1 ),
												"properties"=>array( "text"=>CrmLang::_( 'Asignat lui:' ), 'required'=>true ),
											),
											array(
												"type"=>"ctlAutoSelect",
												"name"=>"AssignedTo",
												"style"=>array( "top"=>inc($r2, '4px'), "left"=>COL_1_2, "width"=>"200px" ),
												"properties"=>array(
													"tabindex"=>'2',
													'db_type' => 'int',
													'items'=>WebCRMemployee::getSelectEmployees(Auth::get('UserID')),
													'value'=>WebCRMemployee::getEmployeeID()
												),
											),
											array(
												"type"=>"ctlLabel",
												"name"=>"Namel",
												"class"=>"BoldLabel",
												"style"=>array( "top"=>inc($r1), "left"=>COL_1_1 ),
												"properties"=>array( "text"=>CrmLang::_( 'Subiect:' ), 'required'=>true ),
											),
											array(
												"type"=>"ctlTextBox",
												"name"=>"Name",
												"style"=>array( "top"=>inc($r2), "left"=>COL_1_2 ),
												"properties"=>array( "tabindex"=>'2', 'db_type' => 'varchar' ),
											),
											array(
												"type"=>"ctlLabel",
												"name"=>"DueTol",
												"class"=>"BoldLabel",
												"style"=>array( "top"=>inc($r1), "left"=>COL_1_1 ),
												"properties"=>array( "text"=>CrmLang::_( 'Data:' ), 'required' => true),
											),
											array(
												"type"=>"ctlCalendar",
												"name"=>"DueTo",
												"style"=>array( "top"=>inc($r2), "left"=>COL_1_2, 'db_type' => 'int'),
												"properties"=>array( "tabindex"=>'2' ),
											),
											array(
												"type"=>"ctlLabel",
												"name"=>"Descriptionl",
												"class"=>"BoldLabel",
												"style"=>array( "top"=>inc($r1), "left"=>COL_1_1 ),
												"properties"=>array( "text"=>CrmLang::_( 'Descriere:' ) ),
											),
											array(
												"type"=>"ctlTextBox",
												"name"=>"Description",
												"style"=>array( "top"=>inc($r2), "left"=>COL_1_2, "height"=>"70px", "width"=>"560px" ),
												"properties"=>array( "tabindex"=>'2', "multiline"=>'true', 'db_type' => 'text' ),
											),
											array(
												"type"=>"ctlLabel",
												"name"=>"Typel",
												"class"=>"BoldLabel",
												"style"=>array( "top"=>inc($r1, '8px'), "left"=>COL_2_1 ),
												"properties"=>array( "text"=>CrmLang::_( 'Tip Activitate:' ) ),
											),
											array(
												"type"=>"ctlSelect",
												"name"=>"Type",
												"style"=>array( "top"=>inc($r2, '4px'), "left"=>COL_2_2 ),
												"properties"=>array( "tabindex"=>'2', 'db_type' => 'int', 'value'=>0 ),
											),
											array(
												"type"=>"ctlLabel",
												"name"=>"Statusl",
												"class"=>"BoldLabel",
												"style"=>array( "top"=>inc($r1), "left"=>COL_2_1 ),
												"properties"=>array( "text"=>CrmLang::_( 'Status Activitate:' ) ),
											),
											array(
												"type"=>"ctlSelect",
												"name"=>"Status",
												"style"=>array( "top"=>inc($r2), "left"=>COL_2_2 ),
												"properties"=>array( "tabindex"=>'2', 'db_type' => 'int', 'value'=>0 ),
											),
											array(
												"type"=>"ctlLabel",
												"name"=>"Priorityl",
												"class"=>"BoldLabel",
												"style"=>array( "top"=>inc($r1), "left"=>COL_2_1 ),
												"properties"=>array( "text"=>CrmLang::_( 'Prioritate:' ) ),
											),
											array(
												"type"=>"ctlSelect",
												"name"=>"Priority",
												"style"=>array( "top"=>inc($r2), "left"=>COL_2_2 ),
												"properties"=>array( "tabindex"=>'2', 'db_type' => 'int', 'value'=>0 ),
											),
											array(
												"type"=>"ctlLabel",
												"name"=>"Remindl",
												"class"=>"BoldLabel",
												"style"=>array( "top"=>inc($r1, $r1 + 120 . 'px'), "left"=>COL_1_1 ),
												"properties"=>array( "text"=>CrmLang::_( 'Notificare:' ) ),
											),
											array(
												"type"=>"ctlCheckBox",
												"name"=>"Remind",
												"style"=>array( "top"=>inc($r2, $r2 + 120 . 'px'), "left"=>COL_1_2 ),
												"properties"=>array( "tabindex"=>'2', 'text'=>CrmLang::_( '(anunta angajatul pe email)' ), 'db_type' => 'int' ),
											)
										)
									)
								)
							),
							array(
								"type"=>"ctlTabList",
								'class'=>'noborders ctlTabList',
								"name"=>"ActNotesTabList",
								"style"=>array( "top"=>"318px", "height"=>"535px", "position"=>"absolute", "width"=>"80%", "left"=>"2px" ),
								'properties' => array('visible'=>"false"),
								"ctlDefs"=>array(
									array(
										"type"=>"ctlControl",
										"name"=>"tabnotes",
										"properties"=>array( "txt"=>CrmLang::_( 'Notite' ) ),
										"ctlDefs"=>array(),
									)
								),
							),
						)
					)
				], // TabList
			] ] // Main Window
		]]; /// return
	}
}

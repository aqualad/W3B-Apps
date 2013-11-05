<?php

class ResCRMContacts {


	public static function ctlDefs() {
		$lang = lang :: get( "CrmLanguage" );
		$allimgs = ImageConfig :: extString(); //Used by Image Upload
		$req = new Request();
		$layout = new Layout();
		$iconset = Layout :: getIconset();
		$delc1=CrmLang::_( 'Confirma Stergerea' );
		$delc2=CrmLang::_( 'Esti sigur ca vrei sa stergi acest camp?' );

		return array(


			/* ====================== desktop window ========================== */
			array(
				"type"=>"ctlControl",
				"name"=>"MainWindow",
				"properties"=>array( "title"=>CrmLang::_( "CRM - Baza de Contacte" ), "icon"=>"apps/CRM/icons/16/app.png", "scrolling"=>true ),
				"style"=>array( "width"=>"980px", "height"=>"570px", "position"=>"relative" ),
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
								"name"=>"ViewContacts",
								"properties"=>array( "icon"=>"apps/CRM/icons/64/companies.png", "text"=>CrmLang::_( 'Lista de contacte' ), "description"=>CrmLang::_( 'Listeaza contactele definite in sistem' ), "tabindex"=>"4" ),
								"style"=>array( "width"=>"210px" ),
								"events"=>array( 'click' ),
							),
							array(
								"type"=>"ctlBigButton",
								"name"=>"AddContact",
								"properties"=>array( "icon"=>"apps/CRM/icons/64/company.png", "text"=>CrmLang::_( 'Adaugati Contact' ), "description"=>CrmLang::_( 'Adaugati un nou contact.' ), "tabindex"=>"4" ),
								"style"=>array( "width"=>"210px" ),
								"events"=>array( 'click' ),
							),
							array(
								"type"=>"ctlBigButton",
								"name"=>"ViewActivities",
								"properties"=>array( "icon"=>"apps/CRM/icons/64/activity.png", "text"=>CrmLang::_( 'Lista Activitati' ), "description"=>CrmLang::_( 'Listeaza activitatile definite in sistem' ), "tabindex"=>"4" ),
								"style"=>array( "width"=>"210px" ),
								"events"=>array( 'click' ),
							),
							array(
								"type"=>"ctlBigButton",
								"name"=>"AddActivity",
								"properties"=>array( "icon"=>"apps/CRM/icons/64/activityadd.png", "text"=>CrmLang::_( 'Adauga Activitate' ), "description"=>CrmLang::_( 'Adaugati o noua activitate.' ), "tabindex"=>"4" ),
								"style"=>array( "width"=>"210px" ),
								"events"=>array( 'click' ),
							),
							array(
								"type"=>"ctlBigButton",
								"name"=>"Configuration",
								"properties"=>array( "icon"=>"apps/CRM/icons/64/fields.png", "text"=>CrmLang::_( 'Campuri Optionale' ), "description"=>CrmLang::_( 'Adaugati campuri optionale in aplicatie.' ), "tabindex"=>"4" ),
								"style"=>array( "width"=>"210px" ),
								"events"=>array( 'click' ),
							),
							array(
								"type"=>"ctlBigButton",
								"name"=>"Settings",
								"properties"=>array( "icon"=>"apps/CRM/icons/64/settings.png", "text"=>CrmLang::_( 'Import Fisier' ), "description"=>CrmLang::_( 'Importa in baza de date un fisier CSV.' ), "tabindex"=>"4" ),
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
						"style"=>array( "left"=>'25px', "position"=>'relative', "float"=>'left', 'width'=>"750px", "height"=>"570px" ),
						"ctlDefs"=>array(

							array(
								"type"=>"ctlTabList",
								"name"=>"TabList",
								"ctlDefs"=>array(
									//LIST CONTACTS
									array(
										"type"=>"ctlControl",
										"class"=>"AdminPanel",
										"name"=>"ListPanel",
										"properties"=>array( "txt"=>CrmLang::_( 'Contacte' ) ),
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
												"style"=>array( "top"=>"4px", "left"=>"10px", "color"=>"blue" ),
												"properties"=>array( "text"=>'<img src="'.Layout :: getIconset( "16/info.png" ).'" />'.CrmLang::_( 'Click dreapta pe o inregistrare pentru stergere sau editare.' ), "allowTags"=>true ),
											),

											array(
												"type"=>"ctlPopupMenu",
												"name"=>"ListContactsMenu",
												"events"=>array(),
												"properties"=>array( "items"=>array(
														array( 'title'=>CrmLang::_( 'Editeaza' ), 'icon'=>"{$iconset}16/group-edit.png", 'event'=>'Edit' ),
														array( 'title'=>CrmLang::_( 'Sterge' ), 'icon'=>"{$iconset}16/group-delete.png", 'code'=>'this.groupFDelete();' )
													)
												),
												"csCode"=> <<<eojs
%INSTANCE_NAME%.groupFDelete = function()
{sendServerSideEvent(proc[this.pid].controls.ListContacts, 'Delete', null);
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
											array(
												"type"  => "ctlDbGrid",
												"name"  => "ListContacts",
												"style" => array(
													"top"    =>"40px",
													"left"   =>"10px",
													"right"  =>"10px",
													"bottom" =>"10px"
												),
												"properties" => array(
													'popupMenu' => 'ListContactsMenu',
													'filters'	=> false,
													'limit'     => '21',
													//"sql"       => "select id, concat(LastName,' ',FirstName) as _name, Phone as _phone, Email AS _email from ".db::prefix('crm_contacts_view').' where 1 order by _name asc',
													"sql"				=> "select a.id, concat(a.firstname,' ',a.lastname) as _name, b.phone as _phone, a.email as _email from contact a left join profile b on a.id=b.contact_id WHERE b.createdby = ".Auth::get('UserID'),
													//'items'		=> R::find(db::prefix('crm_contacts_view'), ' CreatedBy = ?', [Auth::get('UserID')]),
													"fields"    => array(
														array(
															'type'=>'string',
															'name'=>'_name',
															'title'=>CrmLang::_('Name')
														),
														array(
															'type'=>'string',
															'name'=>'_phone',
															'title'=>CrmLang::_('Telefon')
														),
														array(
															'type'=>'string',
															'name'=>'_email',
															'title'=>CrmLang::_('Email')
														),
													),
													'pager'		=> false,
												),
												"csCode"=> <<<eojs
%INSTANCE_NAME%.onDblClick = function(event, subElement)
{
	if(subElement == null) return;
	var arr = subElement.split('_');
	if(arr[0] == 'cell')
		sendServerSideEvent(proc[this.pid].controls.ListContactsMenu, 'Edit', null);
}
eojs
											),

										),
									),

									//ADD CONTACT
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
												"properties"=>array( "text"=>'<img src="'.Layout :: getIconset( "16/info.png" ).'" />'.CrmLang::_( 'Aici puteti adauga un nou contact sau modifica unul deja existent.' ), "allowTags"=>true ),
											),
											//Info Tab
											array(
												"type"=>"ctlButton",
												"name"=>"save",
												"style"=>array( "top"=>"4px", "right"=>"10px", "width"=>"130px" ),
												"properties"=>array( "text"=>CrmLang::_( "Salveaza" ), "icon"=>Layout :: getIconset( "16/save.png" ) ),
												"events"=>array( "click" ),
											),
											array(
												"type"=>"ctlButton",
												"name"=>"cancel",
												"style"=>array( "top"=>"4px", "right"=>"150px", "width"=>"100px" ),
												"properties"=>array( "text"=>CrmLang::_( "Renunta" ), "icon"=>Layout :: getIconset( "16/close.png" ) ),
												"events"=>array( "click" ),
											),


											array(
												"type"=>"ctlLabel",
												"name"=>"GeneralInfoLabel",
												"class"=>"BoldLabel",
												"style"=>array( "top"=>"37px", "left"=>"10px", "color"=>"blue" ),
												"properties"=>array( "text"=>CrmLang::_( 'Informatii Generale' ) ),
											),

											array(
												"type"=>"ctlControl",
												"name"=>"GenInfoTabList",
												"style"=>array( "height"=>"120px", "top"=>"60px", "position"=>"absolute" ),
												"ctlDefs"=>array(
													array(
														"type"=>"ctlControl",
														"name"=>"GeneralInfo",
														"properties"=>array( "txt"=>CrmLang::_( 'Informatii Generale' ) ),
														"ctlDefs"=>array(
															// First Name
															array(
																"type"=>"ctlLabel",
																"name"=>"FirstNamel",
																"class"=>"BoldLabel",
																"style"=>array( "top"=>"8px", "left"=>"10px" ),
																"properties"=>array( "text"=>CrmLang::_( 'Nume:' ) ),
															),
															array(
																"type"=>"ctlTextBox",
																"name"=>"firstname",
																"style"=>array( "top"=>"4px", "left"=>"140px" ),
																"properties"=>array( "tabindex"=>'2' ),
															),
															// Last Name
															array(
																"type"=>"ctlLabel",
																"name"=>"LastNamel",
																"class"=>"BoldLabel",
																"style"=>array( "top"=>"38px", "left"=>"10px" ),
																"properties"=>array( "text"=>CrmLang::_( 'Prenume:' ) ),
															),
															array(
																"type"=>"ctlTextBox",
																"name"=>"lastname",
																"style"=>array( "top"=>"34px", "left"=>"140px" ),
																"properties"=>array( "tabindex"=>'2' ),
															),
															// Position
															array(
																"type"=>"ctlLabel",
																"name"=>"Professionl",
																"class"=>"BoldLabel",
																"style"=>array( "top"=>"8px", "left"=>"370px" ),
																"properties"=>array( "text"=>CrmLang::_( 'Profesia:' ) ),
															),
															array(
																"type"=>"ctlTextBox",
																"name"=>"profession",
																"style"=>array( "top"=>"4px", "left"=>"500px" ),
																"properties"=>array( "tabindex"=>'2' ),
															),
															// Prospects
															/*array(
																"type"=>"ctlLabel",
																"name"=>"managedl",
																"class"=>"BoldLabel",
																"style"=>array( "top"=>"8px", "left"=>"370px", "color"=>"green" ),
																"properties"=>array( "text"=>CrmLang::_( 'Potential Client:' ) ),
															),
															array(
																"type"=>"ctlSelect",
																"name"=>"managed",
																"style"=>array( "top"=>"4px", "left"=>"500px", "width"=>"200px" ),
																"properties"=>array( "tabindex"=>'2' ),
															),*/
															// Customer
															/*
															array(
																"type"=>"ctlLabel",
																"name"=>"sourcel",
																"class"=>"BoldLabel",
																"style"=>array( "top"=>"38px", "left"=>"370px", "color"=>"green" ),
																"properties"=>array( "text"=>CrmLang::_( 'Referinta Client:' ) ),
															),
															array(
																"type"=>"ctlSelect",
																"name"=>"source",
																"style"=>array( "top"=>"34px", "left"=>"500px", "width"=>"200px" ),
																"properties"=>array( "tabindex"=>'2' ),
															),*/
															// Company
															array(
																"type"=>"ctlLabel",
																"name"=>"Companyl",
																"class"=>"BoldLabel",
																"style"=>array( "top"=>"38px", "left"=>"370px" ),
																"properties"=>array( "text"=>CrmLang::_( 'Companie:' ) ),
															),
															/*array(
																"type"=>"ctlTextBox",
																"name"=>"Company",
																"style"=>array( "top"=>"34px", "left"=>"500px" ),
																"properties"=>array( "tabindex"=>'2' ),
															),*/
															array(
																"type"=>"ctlSelect",
																"name"=>"company",
																"style"=>array( "top"=>"34px", "left"=>"500px", "width"=>"200px" ),
																"properties"=>array( "tabindex"=>'2' ),
																"events"=>array( "change" ),
															),
														),
													),
												),
											),

											//Address Tab

											array(
												"type"=>"ctlLabel",
												"name"=>"GeneralInfo2Label",
												"class"=>"BoldLabel",
												"style"=>array( "top"=>"160px", "left"=>"10px", "color"=>"blue" ),
												"properties"=>array( "text"=>CrmLang::_( 'Informatii Contact' ) ),
											),

											array(
												"type"=>"ctlControl",
												"name"=>"GenInfoTabList2",
												"style"=>array( "top"=>"175px", "height"=>"175px", "position"=>"absolute" ),
												"ctlDefs"=>array(
													array(
														"type"=>"ctlControl",
														"name"=>"GeneralInfo2",
														"properties"=>array( "txt"=>CrmLang::_( 'Informatii Contact' ) ),
														"ctlDefs"=>array(
															// Phone
															array(
																"type"=>"ctlLabel",
																"name"=>"Phonel",
																"class"=>"BoldLabel",
																"style"=>array( "top"=>"8px", "left"=>"10px" ),
																"properties"=>array( "text"=>CrmLang::_( 'Telefon fix:' ) ),
															),
															array(
																"type"=>"ctlTextBox",
																"name"=>"phone",
																"style"=>array( "top"=>"4px", "left"=>"140px" ),
																"properties"=>array( "tabindex"=>'2' ),
															),
															// Mobile
															array(
																"type"=>"ctlLabel",
																"name"=>"Mobilel",
																"class"=>"BoldLabel",
																"style"=>array( "top"=>"38px", "left"=>"10px", "width"=>'100px' ),
																"properties"=>array( "text"=>CrmLang::_( 'Telefon mobil:' ) ),
															),
															array(
																"type"=>"ctlTextBox",
																"name"=>"mobile",
																"style"=>array( "top"=>"34px", "left"=>"140px" ),
																"properties"=>array( "tabindex"=>'2' ),
															),
															// Fax
															array(
																"type"=>"ctlLabel",
																"name"=>"Faxl",
																"class"=>"BoldLabel",
																"style"=>array( "top"=>"68px", "left"=>"10px" ),
																"properties"=>array( "text"=>CrmLang::_( 'Fax:' ) ),
															),
															array(
																"type"=>"ctlTextBox",
																"name"=>"fax",
																"style"=>array( "top"=>"64px", "left"=>"140px" ),
																"properties"=>array( "tabindex"=>'2' ),
															),
															// Email
															array(
																"type"=>"ctlLabel",
																"name"=>"Emaill",
																"class"=>"BoldLabel",
																"style"=>array( "top"=>"98px", "left"=>"10px", "width"=>'100px' ),
																"properties"=>array( "text"=>CrmLang::_( 'Adresa email:' ) ),
															),
															array(
																"type"=>"ctlTextBox",
																"name"=>"email",
																"style"=>array( "top"=>"94px", "left"=>"140px" ),
																"properties"=>array( "tabindex"=>'2' ),
															),
															// URL
															array(
																"type"=>"ctlLabel",
																"name"=>"URLl",
																"class"=>"BoldLabel",
																"style"=>array( "top"=>"128px", "left"=>"10px", "width"=>'100px' ),
																"properties"=>array( "text"=>CrmLang::_( 'Adresa URL:' ) ),
															),
															array(
																"type"=>"ctlTextBox",
																"name"=>"url",
																"style"=>array( "top"=>"124px", "left"=>"140px" ),
																"properties"=>array( "tabindex"=>'2' ),
															),
															// Avatar Image
															array(
																"type"=>"ctlLabel",
																"name"=>"UseAvatarl",
																"class"=>"BoldLabel",
																"style"=>array( "top"=>"158px", "left"=>"10px" ),
																"properties"=>array( "text"=>CrmLang::_( 'imagine:' ) ),
															),
															array(
																"type"=>"ctlCheckBox",
																"name"=>"UseAvatar",
																"style"=>array( "top"=>"158px", "left"=>"140px" ),
																"properties"=>array( "text"=>CrmLang::_( 'Utilizati avatar' ) ),
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
															),
															array(
																"type"=>"ctlUploadSingle",
																"name"=>"Image",
																"style"=>array( "top"=>"174px", "left"=>"140px", "visibility"=>"hidden" ),
																"properties"=>array( "extlist"=>$allimgs ),
																"csCode"=> <<<eojs
%INSTANCE_NAME%.onComplete = function()
{
	sendServerSideEvent(this, 'keypress', null);
}
eojs
															),
															array(
																"type"=>"ctlImage",
																"name"=>"Thumb",
																"class"=>"EditUserAvatarThumb",
																"style"=>array( "top"=>"134px", "left"=>"10px", "visibility"=>"hidden" ),
																"properties"=>array( "value"=>"images/s.gif" ),
															),
															// Country
															array(
																"type"=>"ctlLabel",
																"name"=>"Countryl",
																"class"=>"BoldLabel",
																"style"=>array( "top"=>"8px", "left"=>"370px" ),
																"properties"=>array( "text"=>CrmLang::_( 'Tara:' ) ),
															),
															array(
																"type"=>"ctlSelect",
																"name"=>"country",
																"style"=>array( "top"=>"4px", "left"=>"500px", "width"=>"200px" ),
																"properties"=>array( "tabindex"=>'2' ),
																"events"=>array( "change" ),
															),
															// State
															array(
																"type"=>"ctlLabel",
																"name"=>"Statel",
																"class"=>"BoldLabel",
																"style"=>array( "top"=>"38px", "left"=>"370px" ),
																"properties"=>array( "text"=>CrmLang::_( 'Judetul:' ) ),
															),
															array(
																"type"=>"ctlSelect",
																"name"=>"state",
																"style"=>array( "top"=>"34px", "left"=>"500px", "width"=>"200px" ),
																"properties"=>array( "tabindex"=>'2' ),
															),
															// Zip
															array(
																"type"=>"ctlLabel",
																"name"=>"Zipl",
																"class"=>"BoldLabel",
																"style"=>array( "top"=>"68px", "left"=>"370px", "width"=>'100px' ),
																"properties"=>array( "text"=>CrmLang::_( 'Cod Postal:' ) ),
															),
															array(
																"type"=>"ctlTextBox",
																"name"=>"zip",
																"style"=>array( "top"=>"64px", "left"=>"500px" ),
																"properties"=>array( "tabindex"=>'2' ),
															),
															// City
															array(
																"type"=>"ctlLabel",
																"name"=>"Cityl",
																"class"=>"BoldLabel",
																"style"=>array( "top"=>"98px", "left"=>"370px" ),
																"properties"=>array( "text"=>CrmLang::_( 'Orasul:' ) ),
															),
															array(
																"type"=>"ctlTextBox",
																"name"=>"city",
																"style"=>array( "top"=>"94px", "left"=>"500px" ),
																"properties"=>array( "tabindex"=>'2' ),
															),
															// Address
															array(
																"type"=>"ctlLabel",
																"name"=>"Addressl",
																"class"=>"BoldLabel",
																"style"=>array( "top"=>"128px", "left"=>"370px" ),
																"properties"=>array( "text"=>CrmLang::_( 'Adresa:' ) ),
															),
															array(
																"type"=>"ctlTextBox",
																"name"=>"address",
																"style"=>array( "top"=>"124px", "left"=>"500px" ),
																"properties"=>array( "tabindex"=>'2' ),
															),
															// Notes
															array(
																"type"=>"ctlLabel",
																"name"=>"notesl",
																"class"=>"BoldLabel",
																"style"=>array( "top"=>"158px", "left"=>"370px" ),
																"properties"=>array( "text"=>CrmLang::_( 'Notite:' ) ),
															),
															array(
																"type"=>"ctlTextBox",
																"name"=>"notes",
																"style"=>array( "top"=>"177px", "left"=>"382px", "width"=>"317px", "height"=>"80px" ),
																"properties"=>array( "tabindex"=>'2', "multiline"=>'true' ),
															),
														),
													),
												),
											),


											//Custom Fields Tab

											array(
												"type"=>"ctlLabel",
												"name"=>"GeneralInfo3Label",
												"class"=>"BoldLabel",
												"style"=>array( "top"=>"487px", "left"=>"10px", "color"=>"blue" ),
												"properties"=>array( "text"=>CrmLang::_( 'Alte Informatii' ) ),
											),

											array(
												"type"=>"ctlControl",
												"name"=>"GenInfoTabList3",
												"style"=>array( "top"=>"517px", "height"=>"175px", "position"=>"absolute" ),
												"ctlDefs"=>array(
													array(
														"type"=>"ctlControl",
														"name"=>"otherinfo",
														"properties"=>array( "txt"=>CrmLang::_( 'Alte Informatii' ) ),
														"ctlDefs"=>array(

														),
													),
												),
											),

										),
									),

									//Custom Fields Tab
									array(
										"type"=>"ctlControl",
										"class"=>"AdminPanel",
										"name"=>"ConfigPanel",
										"properties"=>array( "txt"=>CrmLang::_( 'Administrare Campuri Optionale' ) ),
										"csCode"=><<<eohtml
%INSTANCE_NAME%.onHide = function()
{
	var ctl = proc[this.pid].controls.TabList;
	$(ctl.cid + '_button_ConfigPanel_div').style.display = 'none';
};
%INSTANCE_NAME%.onShow = function()
{
	var ctl = proc[this.pid].controls.TabList;
	$(ctl.cid + '_button_ConfigPanel_div').style.display = 'block';
};
eohtml
										,
										"ctlDefs"=>array(

											array(
												"type"=>"ctlLabel",
												"name"=>"siteintrop",
												"style"=>array( "top"=>"4px", "left"=>"10px", "color"=>"blue" ),
												"properties"=>array( "text"=>'<img src="'.Layout :: getIconset( "16/info.png" ).'" />'.CrmLang::_( 'Aici puteti adauga campuri noi in aplicatie (Ex: Cont Bancar).' ), "allowTags"=>true ),
											),
											array(
												"type"=>"ctlLabel",
												"name"=>"addfieldl",
												"class"=>"BoldLabel",
												"style"=>array( "top"=>"34px", "left"=>"10px" ),
												"properties"=>array( "text"=>CrmLang::_( "Denumire camp" ) ),
											),
											array(
												"type"=>"ctlTextBox",
												"name"=>"addfield",
												"style"=>array( "top"=>"30px", "left"=>"130px", "width"=>"230px" ),
												"properties"=>array( "maxlength"=>"1000", "inputType"=>"text", "tabindex"=>"2" ),
												"events"=>array(),
											),
											array(
												"type"=>"ctlLabel",
												"name"=>"typefieldl",
												"class"=>"BoldLabel",
												"style"=>array( "top"=>"64px", "left"=>"10px" ),
												"properties"=>array( "text"=>CrmLang::_( "Tipul campului" ) ),
											),
											array(
												"type"=>"ctlSelect",
												"name"=>"typefield",
												"style"=>array( "top"=>"60px", "left"=>"130px", "width"=>"230px" ),
												"properties"=>array( "tabindex"=>1, "value"=>"1", "items"=> array(
														array( 'txt'=>CrmLang::_( 'camp de tip text' ), 'value'=>"1" ),
														array( 'txt'=>CrmLang::_( 'valoare numerica' ), 'value'=>"2" ),
														array( 'txt'=>CrmLang::_( 'format data' ), 'value'=>"3" )
													)
												),
											),
											array(
												"type"=>"ctlLabel",
												"name"=>"orderfieldl",
												"class"=>"BoldLabel",
												"style"=>array( "top"=>"94px", "left"=>"10px" ),
												"properties"=>array( "text"=>CrmLang::_( "Ordine" ) ),
											),
											array(
												"type"=>"ctlTextBox",
												"name"=>"orderfield",
												"style"=>array( "top"=>"90px", "left"=>"130px", "width"=>"50px" ),
												"properties"=>array( "maxlength"=>"1000", "inputType"=>"text", "tabindex"=>"2" ),
												"events"=>array(),
											),
											array(
												"type"=>"ctlCheckBox",
												"name"=>"pinspected",
												"style"=>array( "top"=>"92px", "left"=>"255px", "width"=>"50px" ),
												"properties"=>array( "maxlength"=>"32", "tabindex"=>"2", 'value'=>"off", "text"=>CrmLang::_( "Apare in listing" ) ),
												"events"=>array(),
											),
											array(
												"type"=>"ctlButton",
												"name"=>"savefield",
												"style"=>array( "top"=>"160px", "left"=>"240px", "width"=>"130px" ),
												"properties"=>array( "text"=>CrmLang::_( "Adauga Camp" ), "icon"=>Layout :: getIconset( "16/add.png" ) ),
												"events"=>array( "click" ),
											),
											array(
												"type"=>"ctlButton",
												"name"=>"cancelfield",
												"style"=>array( "top"=>"160px", "left"=>"130px", "width"=>"90px" ),
												"properties"=>array( "visible"=>"true", "text"=>CrmLang::_( "Renunta" ), "icon"=>Layout :: getIconset( "16/close.png" ) ),
												"events"=>array( "click" ),
											),
											array(
												"type"=>"ctlLabel",
												"name"=>"hlpviewfields",
												"style"=>array( "top"=>"4px", "left"=>"450px", "color"=>"blue" ),
												"properties"=>array( "text"=>'<img src="'.Layout :: getIconset( "16/info.png" ).'" />'.CrmLang::_( 'Click dreapta pe o inregistrare pentru stergere sau editare.' ), "allowTags"=>true ),
											),
											array(
												"type"=>"ctlDbGrid",
												"name"=>"ListFields",
												"style"=>array( "top"=>"40px", "left"=>"450px", "right"=>"10px", "bottom"=>"10px" ),
												"properties"=>array(
													'popupMenu'=>'ListFieldsMenu',
													"sql"=>"select * from ".db :: prefix( "crm_contacts_fields" )." where 1",
													"fields"=>array(
														array( 'type'=>'string', 'name'=>'ListOrder', 'title'=>'Ord.#' ),
														array( 'type'=>'string', 'name'=>'Name', 'title'=>CrmLang::_( 'Nume Camp' ) ),
													),
												),
											),
											array(
												"type"=>"ctlPopupMenu",
												"name"=>"ListFieldsMenu",
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
	sendServerSideEvent(this.buttonCtl, 'DeleteField', null);
}
}
eojs
											),

										),
									),

									//Panel List Activities
									array(
										"type"=>"ctlControl",
										"class"=>"AdminPanel",
										"name"=>"ActivitiesPanel",
										"properties"=>array( "txt"=>CrmLang::_( 'Listeaza Activitatile' ) ),
										"csCode"=><<<eohtml
%INSTANCE_NAME%.onHide = function()
{
	var ctl = proc[this.pid].controls.TabList;
	$(ctl.cid + '_button_ActivitiesPanel_div').style.display = 'none';
};
%INSTANCE_NAME%.onShow = function()
{
	var ctl = proc[this.pid].controls.TabList;
	$(ctl.cid + '_button_ActivitiesPanel_div').style.display = 'block';
};
eohtml
										,
										"ctlDefs"=>array(
											array(
												"type"=>"ctlLabel",
												"name"=>"activitiesintro",
												"style"=>array( "top"=>"4px", "left"=>"10px", "color"=>"blue" ),
												"properties"=>array( "text"=>'<img src="'.Layout :: getIconset( "16/info.png" ).'" />'.CrmLang::_( 'Mai jos sunt listate activitatile definite in CRM. Folositi filtrele pentru o mai buna cautare.' ), "allowTags"=>true ),
											),
											/*
								array(
										"type"=>"ctlDbGrid",
										"name"=>"ListActivities",
										"style"=>array("top"=>"40px", "left"=>"10px", "right"=>"10px","bottom"=>"10px"),
										"properties"=>array(
											'popupMenu'=>'ListActMenu',
											"sql"=>"select a.Name,a.ID,a.CreatedTime,a.DueTo,
													concat(b.LastName,' ',b.FirstName) as Lead,
													concat(c.LastName,' ',c.FirstName) as Numele
													from ".db :: prefix("crm_contacts_activities")." a
													inner join ".db :: prefix("crm_contacts")." b
													on a.ClientID=b.ID
													inner join ".db :: prefix("crm_employees")." c
													on a.AssignedTo=c.ID
													where 1",
											"fields"=>array(
												array('type'=>'string','name'=>'Name','title'=>CrmLang::_('Titlu')),
												array('type'=>'string','name'=>'Lead','title'=>CrmLang::_('Contact')),
												array('type'=>'date','name'=>'DueTo','title'=>CrmLang::_('Pana La')),
												array('type'=>'date','name'=>'CreatedTime','title'=>CrmLang::_('Data Adaugarii')),
												array('type'=>'string','name'=>'Numele','title'=>CrmLang::_('Asignat Catre')),
											),
										),
									),
								*/

											array(
												"type"=>"ctlPopupMenu",
												"name"=>"ListActMenu",
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
									//end Activities

									//Panel ADD Activities
									array(
										"type"=>"ctlControl",
										"class"=>"AdminPanel",
										"name"=>"ActivityPanel",
										"properties"=>array( "txt"=>CrmLang::_( 'Adauga/Editeaza Activitate' ) ),
										"csCode"=><<<eohtml
%INSTANCE_NAME%.onHide = function()
{
	var ctl = proc[this.pid].controls.TabList;
	$(ctl.cid + '_button_ActivityPanel_div').style.display = 'none';
};
%INSTANCE_NAME%.onShow = function()
{
	var ctl = proc[this.pid].controls.TabList;
	$(ctl.cid + '_button_ActivityPanel_div').style.display = 'block';
};
eohtml
										,
										"ctlDefs"=>array(
											array(
												"type"=>"ctlLabel",
												"name"=>"activityintro",
												"style"=>array( "top"=>"4px", "left"=>"10px", "color"=>"blue" ),
												"properties"=>array( "text"=>'<img src="'.Layout :: getIconset( "16/info.png" ).'" />'.CrmLang::_( 'Folositi formularul de mai jos pentru a adauga sau modifica o activitate.' ), "allowTags"=>true ),
											),
											array(
												"type"=>"ctlButton",
												"name"=>"saveactiv",
												"style"=>array( "top"=>"4px", "right"=>"10px", "width"=>"130px" ),
												"properties"=>array( "text"=>CrmLang::_( "Salveaza" ), "icon"=>Layout :: getIconset( "16/save.png" ) ),
												"events"=>array( "click" ),
											),
											array(
												"type"=>"ctlButton",
												"name"=>"cancelactiv",
												"style"=>array( "top"=>"4px", "right"=>"150px", "width"=>"100px" ),
												"properties"=>array( "text"=>CrmLang::_( "Renunta" ), "icon"=>Layout :: getIconset( "16/close.png" ) ),
												"events"=>array( "click" ),
											),
											array(
												"type"=>"ctlTabList",
												"name"=>"AddTabList",
												"style"=>array( "height"=>"330px", "top"=>"30px" ),
												"ctlDefs"=>array(
													array(
														"type"=>"ctlControl",
														"name"=>"AddInfoTab",
														"properties"=>array( "txt"=>CrmLang::_( 'Informatii Generale' ) ),
														"ctlDefs"=>array(
															array(
																"type"=>"ctlLabel",
																"name"=>"assignedl",
																"class"=>"BoldLabel",
																"style"=>array( "top"=>"8px", "left"=>"10px" ),
																"properties"=>array( "text"=>CrmLang::_( 'Asignat lui:' ) ),
															),
															array(
																"type"=>"ctlSelect",
																"name"=>"assigned",
																"style"=>array( "top"=>"4px", "left"=>"140px", "width"=>"200px" ),
																"properties"=>array( "tabindex"=>'2' ),
															),
															array(
																"type"=>"ctlLabel",
																"name"=>"namel",
																"class"=>"BoldLabel",
																"style"=>array( "top"=>"38px", "left"=>"10px" ),
																"properties"=>array( "text"=>CrmLang::_( 'Subiect:' ) ),
															),
															array(
																"type"=>"ctlTextBox",
																"name"=>"name",
																"style"=>array( "top"=>"34px", "left"=>"140px" ),
																"properties"=>array( "tabindex"=>'2' ),
															),
															array(
																"type"=>"ctlLabel",
																"name"=>"duetol",
																"class"=>"BoldLabel",
																"style"=>array( "top"=>"68px", "left"=>"10px" ),
																"properties"=>array( "text"=>CrmLang::_( 'Data:' ) ),
															),
															array(
																"type"=>"ctlCalendar",
																"name"=>"dueto",
																"style"=>array( "top"=>"64px", "left"=>"140px" ),
																"properties"=>array( "tabindex"=>'2' ),
															),
															array(
																"type"=>"ctlLabel",
																"name"=>"descriptionl",
																"class"=>"BoldLabel",
																"style"=>array( "top"=>"98px", "left"=>"10px" ),
																"properties"=>array( "text"=>CrmLang::_( 'Descriere:' ) ),
															),
															array(
																"type"=>"ctlTextBox",
																"name"=>"description",
																"style"=>array( "top"=>"94px", "left"=>"140px", "height"=>"70px", "width"=>"550px" ),
																"properties"=>array( "tabindex"=>'2', "multiline"=>'true' ),
															),
															array(
																"type"=>"ctlLabel",
																"name"=>"leadl",
																"class"=>"BoldLabel",
																"style"=>array( "top"=>"8px", "left"=>"380px" ),
																"properties"=>array( "text"=>CrmLang::_( 'Contact:' ) ),
															),
															array(
																"type"=>"ctlSelect",
																"name"=>"lead",
																"style"=>array( "top"=>"4px", "left"=>"500px" ),
																"properties"=>array( "tabindex"=>'2' ),
															),
															array(
																"type"=>"ctlLabel",
																"name"=>"typel",
																"class"=>"BoldLabel",
																"style"=>array( "top"=>"38px", "left"=>"380px" ),
																"properties"=>array( "text"=>CrmLang::_( 'Tip Activitate:' ) ),
															),
															array(
																"type"=>"ctlSelect",
																"name"=>"type",
																"style"=>array( "top"=>"34px", "left"=>"500px" ),
																"properties"=>array( "tabindex"=>'2' ),
															),
															array(
																"type"=>"ctlLabel",
																"name"=>"statusull",
																"class"=>"BoldLabel",
																"style"=>array( "top"=>"68px", "left"=>"380px" ),
																"properties"=>array( "text"=>CrmLang::_( 'Status Activitate:' ) ),
															),
															array(
																"type"=>"ctlSelect",
																"name"=>"statusul",
																"style"=>array( "top"=>"64px", "left"=>"500px" ),
																"properties"=>array( "tabindex"=>'2' ),
															),
															array(
																"type"=>"ctlLabel",
																"name"=>"prioritatel",
																"class"=>"BoldLabel",
																"style"=>array( "top"=>"178px", "left"=>"10px" ),
																"properties"=>array( "text"=>CrmLang::_( 'Prioritate:' ) ),
															),
															array(
																"type"=>"ctlSelect",
																"name"=>"prioritate",
																"style"=>array( "top"=>"174px", "left"=>"140px" ),
																"properties"=>array( "tabindex"=>'2' ),
															),
															array(
																"type"=>"ctlLabel",
																"name"=>"notifyl",
																"class"=>"BoldLabel",
																"style"=>array( "top"=>"208px", "left"=>"10px" ),
																"properties"=>array( "text"=>CrmLang::_( 'Notificare:' ) ),
															),
															array(
																"type"=>"ctlCheckBox",
																"name"=>"notify",
																"style"=>array( "top"=>"208px", "left"=>"140px" ),
																"properties"=>array( "tabindex"=>'2', 'text'=>CrmLang::_( '(anunta angajatul pe email)' ), "value"=>"on" ),
															),
														),
													),
												),
											),

										),
									),
									//end ADD Activities

									//PANEL COFIG
									array(
										"type"=>"ctlControl",
										"class"=>"AdminPanel",
										"name"=>"SettingsPanel",
										"properties"=>array( "txt"=>CrmLang::_( 'Import' ) ),
										"csCode"=><<<eohtml
%INSTANCE_NAME%.onHide = function()
{
	var ctl = proc[this.pid].controls.TabList;
	$(ctl.cid + '_button_SettingsPanel_div').style.display = 'none';
};
%INSTANCE_NAME%.onShow = function()
{
	var ctl = proc[this.pid].controls.TabList;
	$(ctl.cid + '_button_SettingsPanel_div').style.display = 'block';
};
eohtml
										,
										"ctlDefs"=>array(
											array(
												"type"=>"ctlLabel",
												"name"=>"setintro",
												"style"=>array( "top"=>"4px", "left"=>"10px", "color"=>"blue" ),
												"properties"=>array( "text"=>'<img src="'.Layout :: getIconset( "16/info.png" ).'" />'.CrmLang::_( 'Pentru panoul de companii mutati in partea dreapta campurile care vor putea fi vizualizate.' ), "allowTags"=>true ),
											),
											//upload part
											array(
												"type"=>"ctlControl",
												"name"=>"ImportPanel",
												"properties"=>array(),
												"style"=>array( "border"=>"2px solid black", "position"=>"absolute", "top"=>"30px", "right"=>"10px", "left"=>"10px", "height"=>"340px" ),
												"ctlDefs"=>array(

													array(
														"type"=>"ctlLabel",
														"name"=>"intro",
														"style"=>array( "top"=>"4px", "left"=>"10px", "color"=>"blue", "position"=>"relative" ),
														"properties"=>array( "allowTags"=>"true", "text"=>CrmLang::_( "In cazul in care doriti sa incarcati mai multe inregistrari in baza de date, va rugam sa incarcati un fisier in format CSV." )." <a style='color:red' href='demofile.csv' target='_blank'>".CrmLang::_( "(descarca un exemplu de fisier)" )."</a><br/><br/><span style='color:red'>".CrmLang::_( "(*) Prima linie din fisier sa contina denumirile coloanelor!<br/><br/>(*) Dupa incarcare faceti click pe Asociere Campuri." )."</span>" ),
													),
													array(
														"type"=>"ctlUploadSingle",
														"name"=>"UploadFile",
														"style"=>array( "top"=>"100px", "left"=>"10px", "position"=>"relative" ),
														"properties"=>array( 'text'=>'' ),
													),
													array(
														"type"=>"ctlLabel",
														"name"=>"HelpLabel",
														"class"=>"BoldLabel",
														"style"=>array( "top"=>"5px", "left"=>"10px", "position"=>"relative" ),
														"properties"=>array( "allowTags"=>"true", "text"=>CrmLang::_( "Incarcati aici fisierul in format CSV." ) ),
													),
													array(
														"type"=>"ctlButton",
														"name"=>"CSVImport",
														"style"=>array( "top"=>"130px", "left"=>"10px", "width"=>"150px", "position"=>"relative" ),
														"properties"=>array( "tabindex"=>"7", "text"=>CrmLang::_( "Asociere Campuri" ), "icon"=>Layout :: getIconset( "16/save.png" ) ),
														"events"=>array( "click" ),
													),

												),
											),
											//end
											array(
												"type"=>"ctlWindow",
												"name"=>"UploadWindowStep2",
												"properties"=>array( "title"=>CrmLang::_( "Asociere Campuri" ), "movable"=>false, "resizable"=>false, "scrolling"=>true, "visible"=>"false", "registerInTaskBar"=>"false", "minimizable"=>"false" ),
												"style"=>array( "width"=>"650px", "height"=>"480px", "top"=>"5px", "left"=>"50px" ),
												"events"=>array( "close" ),
												"ctlDefs"=>array(
													array(
														"type"=>"ctlControl",
														"name"=>"ContainerPanel",
														"style"=>array( "overflow"=>"auto" ),
														"ctlDefs"=>array(

														),
													),
												),
											),
										),
									),

									//end panel config

								),
							),
							/*
END PARTS MANAGER
*/

						),
					),
				),
			),

		);

	}

}

?>

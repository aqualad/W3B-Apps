<?php

class ResCRMSendEmail
{
	public static function ctlDefs()
	{
		$allimgs = ImageConfig :: extString();
		$iconset = Layout :: getIconset();

		// Row Positions
		define( "R1_BASE", "44px" );
		define( "R2_BASE", "42px" );
		define( "ROW_SIZE", 30 ); //Row Size
		// Column Positions
		//  [COL_X_Y] ; X - Main Column Number, Y - Sub-Column Number
		define( "COL_1_1", "30px" ); // Col 1, ctlLabel
		define( "COL_1_2", "170px" ); // Col 1, (ctlTextBox|ctlAutoSelect|ctl...)
		define( "COL_2_1", "420px" ); // Col 2, ctlLabel
		define( "COL_2_2", "590px" ); // Col 2, (ctlTextBox|ctlAutoSelect|ctl...)

		$r1 = R1_BASE; //ctlLabel Base Row Size (Offset from top)
		$r2 = R2_BASE; //ctl[Input] Base Row Size (Offset from top)

		function inc( &$r, $v=null )
		{
			return is_null( $v ) ? ( ( $r+=ROW_SIZE )."px" ) : $r=$v;
		}

		return array(
			array(
				"type"=>"ctlWindow",
				"name"=>"SendEmailWindow",
				"properties"=>array("position"=>"center", "title"=>'Send Email', "icon"=>Layout :: getIconset("16/recovery.png"), "minheight"=>"420", "minwidth"=>"180"),
				"style"=>array("width"=>"820px", "height"=>"575px"),
				"events"=>array("close"),
				"ctlDefs"=>array(
					array(
						"type"=>"ctlControl",
						"class"=>"AdminUsers",
						"name"=>"EmailSContainer",
						"ctlDefs"=>array(
							array(
								"type"=>"ctlHiddenField",
								"name"=>"NewsID",
							),
							array(
								"type"=>"ctlHiddenField",
								"name"=>"UserIDS",
							),
							array(
								"type"=>"ctlTabList",
								"name"=>"TabList",
								"events"=>array("click"),
								"ctlDefs"=>array(
									array(
										'type'=>'ctlControl',
										'name'=>'CreateEmail',
										'class'=>'AdminPanel',
										'properties'=>array('txt'=>'Create Email'),
										"ctlDefs"=>array(
											array(
												"type"=>"ctlLabel",
												"name"=>"Tol",
												"class"=>"BoldLabel",
												"style"=>array("top"=>'14px', "left"=>'40px'),
												"properties"=>array("text"=>'To:'),
											),
											array(
												"type"=>"ctlLabel",
												"name"=>"To",
												"style"=>array("top"=>'12px', "left"=>'100px'),
												"properties"=>array("text"=>'', 'allowTags'=>true)
											),
											array(
												"type"=>"ctlLabel",
												"name"=>"Froml",
												"class"=>"BoldLabel",
												"style"=>array("top"=>$r1, "left"=>'40px'),
												"properties"=>array("text"=>'From:'),
											),
											array(
												"type"=>"ctlSelect",
												"name"=>"From",
												"style"=>array("top"=>$r2, "left"=>'100px', "width"=>"200px"),
												"properties"=>array("tabindex"=>"1", 'value'=>0)
											),
											array(
												"type"=>"ctlLabel",
												"name"=>"ReplyTol",
												"class"=>"BoldLabel",
												"style"=>array("top"=>$r1, "left"=>'380px'),
												"properties"=>array("text"=>'Reply-To:'),
											),
											array(
												"type"=>"ctlSelect",
												"name"=>"ReplyTo",
												"style"=>array("top"=>$r2, "left"=>'463px', "width"=>"200px"),
												"properties"=>array("tabindex"=>"2", 'value'=>0),
											),
											array(
												"type"=>"ctlLabel",
												"name"=>"Subjectl",
												"class"=>"BoldLabel",
												"style"=>array("top"=>inc($r1), "left"=>'40px'),
												"properties"=>array("text"=>'Subject:'),
											),
											array(
												"type"=>"ctlTextBox",
												"name"=>"Subject",
												"style"=>array("top"=>inc($r2), "left"=>'100px', "width"=>"557px"),
												"properties"=>array("maxlength"=>"32", "tabindex"=>"3"),
												"events"=>array(),
											),
											array(
												"type"=>"ctlTinyMCE",
												"name"=>"Body",
												"style"=>array("top"=>inc($r2), "left"=>'40px', "width"=>"580px", "height"=>"375px", "position"=>"absolute"),
												"properties"=>array(),
												"events"=>array(),
											),
											array(
												"type"=>"ctlMultiSelect",
												"name"=>"AttachmentsList",
												"style"=>array("top"=>inc($r2, $r2.'px'), "left"=>'625px', "height"=>'375px', "position"=>"absolute", "border"=>"1px solid black", "padding"=>"2px")
											),
											array(
												"type"=>"ctlButton",
												"name"=>"SendEmail",
												"style"=>array("top"=>"70px", "right"=>"25px", "width"=>"100px"),
												"properties"=>array("tabindex"=>"7", "text"=>"Send Email", "icon"=>Layout :: getIconset("16/add.png")),
												"events"=>array("click"),
											)
										)
									),
									array(
										'type'=>'ctlControl',
										'name'=>'PreviewEmail',
										'class'=>'AdminPanel',
										'properties'=>array('txt'=>'Preview'),
										"ctlDefs"=>array(
											array(
												'type'=>'ctlLabel',
												'name'=>'PreviewSubject',
												'style'=>array('top'=>'0px', 'left'=>'10px'),
												'properties'=>array('allowTags'=>true, 'text'=>'')
											),
											array(
												'type'=>'ctlLabel',
												'name'=>'PreviewBody',
												'style'=>array('top'=>'40px', 'left'=>'10px'),
												'properties'=>array('allowTags'=>true, 'text'=>'')
											)
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
}

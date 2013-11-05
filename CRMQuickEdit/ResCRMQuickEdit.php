<?php

class ResCRMQuickEdit extends ResCRMQuickAdd
{
	public static function createnotelist($userid, $activity, $table)
	{
		$prefix = db::prefix();

		// Check prerequisites
		if ($userid == null || $activity == null || $table == null)
			return;

		// Fetch notes from the database
		$notes_db_result = db::query(<<<SQL
SELECT a.ID, a.Message, a.DateAdded, b.Email
FROM $table a
LEFT JOIN {$prefix}users b ON a.UserID = b.UserID
WHERE a.UserID = {$userid}
	AND TaskID = {$activity}
ORDER BY a.DateAdded DESC
SQL
		);

		$defs[] = array(
			'type' => 'ctlTextBox',
			'name' => 'Message',
			'style' => array('left' => '10px', 'width' => '500px'),
		);

		// Create the Add New button
		$defs[] = array(
			'type' => 'ctlButton',
			'name' => 'addNewNote',
			'style' => array('left' => '540px', 'width' => '90px', 'height' => '21px'),
			'properties' => array( 'text' => 'Add', 'icon' => Layout :: getIconset( '16/add.png')),
			'events' => array('click')
		);

		// Create counter position variable
		$top = 32;

		while($row = $notes_db_result->fetch_object())
		{
			$defs[] = array(
				'type' => 'ctlLabel',
				'name' => 'Note'.$row->ID,
				'style' => array('top' => $top.'px', 'left' => '30px', 'overflow'=>'auto', 'height'=>'36px', 'maxWidth'=>'528px'),
				'properties' => array('text'=>$row->Message, 'tooltip'=>"Added on {$row->DateAdded} by {$row->Email}"),
			);

			$defs[] = array(
				'type' => 'ctlLabel',
				'name' => 'Note'.$row->ID.'Timestamp',
				'style' => array('top' => $top+1 . 'px', 'left' => '563px'),
				'properties' => array('text' => date('n/d/Y', strtotime($row->DateAdded)), 'tooltip' => date('l F jS, Y', strtotime($row->DateAdded))),
			);

			$defs[] = array(
				'type' => 'ctlLinkButton',
				'name' => 'UnlinkXDELIMXNoteXDELIMX'.$row->ID,
				'style' => array('left' => '620px', 'top' => $top.'px'),
				'properties' => array('text' => '[ X ]', 'tooltip' => 'Delete Note'),
				'events' => array('click')

			);

			$top += 40;
		}

		return $defs;

	}
}

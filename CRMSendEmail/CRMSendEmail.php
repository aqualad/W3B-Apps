<?php

class CRMSendEmail extends Application
{
	private $files = NULL;

	public function onBeforeInit()
	{
		$this->userid = Auth::get('UserID');
		$this->prefix = db::prefix();

		$params = Request :: getParams();

		$this->to = isset($params['to']) ? $params['to'] : NULL;

		if ( is_null($this->to) || empty($this->to) || validate::email($this->to, 'Recipient', true) !== true )
		{
			ErrorHandler::msgError('No Email specified for recipient');
			$this->close();
		}

		$this->name = isset($params['name']) ? $params['name'] : NULL;
		$this->type = isset($params['type']) ? ($params['type'] == 'LeadID' ? 'LeadID' : 'CustomerID') : NULL;
		$this->id = isset($params['id']) ? $params['id'] : NULL;
	}
	public function onBeforeLoadState()
	{
		// Populate fields: From and Reply-To
		$email_results = db::query(<<<SQL
SELECT e.Email AS employee_email, c.Email AS company_email
FROM {$this->prefix}crm_employees e
LEFT JOIN {$this->prefix}crm_mycompanies c ON e.Company = c.ID
WHERE e.UID = {$this->userid}
SQL
		);

		$emails = array(array('txt'=>'Select Email', 'value'=>0));

		if ($email_results->num_rows())
		{
			$email_object = $email_results->fetch_object();
			$emails[] = $email_object->employee_email ?: NULL;
			$emails[] = $email_object->company_email ?: NULL;
		}

		foreach($emails as $idx => $email)
			if ( empty($email) )
				unset($emails[$idx]);
			else
				if ( ! is_array($email) )
					$emails[$idx] = array('txt'=>$email, 'value'=>$email);

		$this->controls['From']->setProperty('items', $emails);
		$this->controls['From']->setProperty('value', $emails[2]['value']);
		$this->controls['ReplyTo']->setProperty('items', $emails);
		$this->controls['ReplyTo']->setProperty('value', $emails[1]['value']);

		$this->controls['To']->setProperty('text', "<strong>{$this->name}</strong> <i>{$this->to}</i>");

		// Build items for Attachments
		/*
			if ( is_null($this->type) )
				return;

			$items = array();
			$this->files = WebCRM::getFiles($this->type, $this->id);

			foreach($this->files as $idx => $file)
				$items[$file->FileID] = array('txt'=>$file->FileName, 'value'=>$file->FileID, 'tooltip'=>$file->FileName);

			$this->controls['AttachmentsList']->setProperty('items', $items);
		*/
		$this->controls['AttachmentsList']->close();
	}

	public function onSendEmailWindowClose()
	{
		$this->close();
	}

	public function onTabListClick()
	{
		$this->controls['PreviewSubject']->setProperty('text', $this->controls['Subject']->getProperty('value'));
		$this->controls['PreviewBody']->setProperty('text', $this->controls['Body']->getProperty('value'));
	}

	public function onSendEmailClick()
	{
		$from = db::escape($this->controls['From']->getProperty('value'));
		$replyto = db::escape($this->controls['ReplyTo']->getProperty('value'));

		if (validate::email($from, 'From', true) !== true || validate::email($replyto, 'Reply-To', true) !== true)
			return false;

		$mail = new Email();
		$mail->SetEncodedEmailHeader("To", $this->to, $this->to);
		$mail->SetEncodedEmailHeader("From", $from, $from);
		$mail->SetEncodedEmailHeader("Reply-To", $replyto, $replyto);
		$mail->SetHeader("Sender", $from);
		$mail->SetEncodedHeader("Subject", $this->controls['Subject']->getProperty('value'));
		$mail->AddQuotedPrintableHTMLPart($this->controls['Body']->getProperty('value'), "");

		if ( ! is_null($this->files) )
		{
			$values = explode(',', $this->controls['AttachmentsList']->getProperty('value'));

			if (count($values))
			{
				$ctr = 0;
				foreach($this->controls['AttachmentsList']->getProperty('items') as $id => $value)
				{
					if ($values[$ctr] == 'off')
						unset($this->files[$id]);
				}
			}
			if(count($values) && count($this->files))
			{
				// TODO: Handle files from FS
				foreach($this->files as $f)
				{
					$file = array();
					$file["FileName"] = $this->rootdir.'/'.$id.'/'.$f;
					$file["Name"] = $f;
					$file["Data"] = fopen($this->rootdir.'/'.$id.'/'.$f, 'r');

					$tmp = file::mimefile($this->rootdir.'/'.$id.'/'.$f, $this->rootdir.'/'.$id.'/'.$f);

					$file["Content-Type"] = $tmp['Name']; //"image/png"

					$file["Disposition"] = "attachment";
					$mail->AddFilePart2($file);
				}
			}
		}

		$error = $mail->Send();

		db::query("INSERT INTO {$this->prefix}crm_email_stats (UserID, Recipient, Files, Size) VALUES ({$this->userid}, '{$this->name}', '', '0.0 KB')");

		$this->close();
	}
}

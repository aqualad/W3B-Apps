<?php

class CRMOpportunity extends Application {

	const APP_TYPE = APP_TYPE_PANEL;

	public function onBeforeLoadState()
	{
		$this->prefix = db::prefix();
		$this->userid = Auth::get('UserID');

		$this->controls['Account']->setProperty('items', WebCRMclient::getClientsList());
		$this->controls['Currency']->setProperty('items', WebCRM::getCurrencies());

		$this->BuildReport();

		if (isset($_SESSION['op_id']))
		{
			if (is_numeric($_SESSION['op_id']))
				$this->onListOpsMenuEdit($_SESSION['op_id']);
			unset($_SESSION['op_id']);
		}
	}

	public function onAfterRender()
	{
		$this->BuildReport();
	}

	public function onViewOpportunitiesClick()
	{
		$this->controls['TabList']->setProperty( "value", 'ListPanel' );
	}

	public function onAddOpportunityClick()
	{
		$this->clearform();
		$this->controls['TabList']->setProperty('value', 'ViewPanel');
	}

	public function onRepOpportunityClick()
	{
		$this->controls['TabList']->setProperty( "value", 'ReportPanel' );
		$this->BuildReport();
	}

	public function onFilterResultChange()
	{
		$this->controls['ListOps']->setSql($this->controls['FilterResult']->getProperty('value')
			? WebCRM::getOpportunities($this->userid, true) . " AND a.Result = '" . db::escape($this->controls['FilterResult']->getProperty('value')) . "'"
			: WebCRM::getOpportunities($this->userid, true)
		);
	}

	public function onListOpsMenuEdit($id = NULL)
	{
		$this->clearform();

		if (is_null($id) || $id === 'null')
		{
			$items = $this->controls['ListOps']->getProperty( 'items' );
			$val = $this->controls['ListOps']->getProperty( 'value' );

			if (count($val))
			{
				$val = array_pop($val);

				$this->op_id = $items[$val]->ID;
			}
		}
		else
			if (is_numeric($id))
				$this->op_id = $id;
			else
				return;

		$result = db::query( <<<SQL
SELECT *
FROM {$this->prefix}crm_opportunities
WHERE ID={$this->op_id}
SQL
		);

		if ($result->num_rows() && $row = $result->fetch_assoc())
		{
			$row['Amount'] = '$'.number_format($row['Amount'], 2);
			$row['Probability'] = $row['Probability'].'%';

			$controls = WebCRM::getctlControls($this->controls['ViewPanel'], [], ['ctlTextBox'=>null, 'ctlAutoSelect'=>null, 'ctlCalendar'=>null, 'ctlRadio'=>null]);

			WebCRM::populateControls($controls, $row);
		}

		$this->controls['TabList']->setProperty('value', 'ViewPanel');

	}

	public function onSaveClick()
	{
		$errors = array();
		$values = db::escape(WebCRM::getctlControls( $this->controls['ViewPanel'], [], ['ctlTextBox'=>'value', 'ctlAutoSelect'=>'value', 'ctlCalendar'=>'value', 'ctlRadio'=>'value']));
		$values['ExpectedClose'] = strtotime($values['ExpectedClose']);
		$values['Currency'] = $values['Currency'] ?: NULL;
		$values['Amount'] = $values['Amount'] ? str_replace(',', '', ltrim($values['Amount'], '$')) : '';
		$values['Probability'] = $values['Probability'] ? rtrim($values['Probability'], '%') : '';
		$values['ManagedBy'] = $values['ManagedBy'] ?: 'NULL';

		validate::string($values['Name'], 'Name:', 1, 254, true, $errors);
		validate::number($values['Currency'], 'Currency:', NULL, NULL, true, $errors);
		validate::number($values['Amount'], 'Amount:', 0, NULL, true, $errors);
		validate::number($values['Probability'], 'Probability:', 0, 100, false, $errors);
		validate::string($values['Description'], 'Description:', 1, 5000, true, $errors);

		if ( ! count($errors))
		{

			if (isset($this->op_id))
			{
				$update_values = NULL;

				foreach($values as $col => $val)
				{
					if ($this->controls[$col]->getProperty('db_type') == 'int' || $this->controls[$col]->getProperty('db_type') == 'decimal')
						$update_values[] = is_numeric($val) ? "$col=$val" : "$col=NULL";
					else
						$update_values[] = "$col='$val'";
				}

				$update_values = implode(',', $update_values);

				db::query( <<<SQL
UPDATE {$this->prefix}crm_opportunities
SET $update_values
WHERE `ID`={$this->op_id}
SQL
				);
			}
			else
			{
				foreach($values as $key => $value)
				{
					if ($this->controls[$key]->getProperty('db_type') == 'int' || $this->controls[$key]->getProperty('db_type') == 'decimal')
						$values[$key] = is_numeric($value) ? $value : "NULL";
					else
						$values[$key] = "'$value'";
				}

				$cols = implode(',', array_keys($values));
				$vals = implode(',', $values);
				if (count($cols) == count($vals))
					db::query( <<<SQL
INSERT INTO {$this->prefix}crm_opportunities(`ID`, $cols, CreatedBy)
VALUES ('', $vals, $this->userid)
SQL
				);
				else ErrorHandler::msgError('An error has occured during save (column-value mismatch)');
			}

			$this->clearform();
			$this->onViewOpportunitiesClick();
		}
		else
		{
			foreach($errors as $error)
				ErrorHandler::msgError($error);
		}

		$this->BuildReport();
	}

	public function onCancelClick()
	{
		$this->clearform();
		$this->onViewOpportunitiesClick();
	}

	public function onListOpsMenuDelete()
	{
		$items = $this->controls['ListOps']->getProperty( 'items' );
		$val = $this->controls['ListOps']->getProperty( 'value' );

		if (count($val))
		{
			$val = array_pop($val);

			$this->op_id = $items[$val]->ID;

			db::query( <<<SQL
DELETE FROM {$this->prefix}crm_opportunities
WHERE ID={$this->op_id}
SQL
			);
		}

		$this->clearform();
		$this->BuildReport();
	}

	public function clearform()
	{
		$controls = WebCRM::getctlControls($this->controls['ViewPanel'], [], ['ctlTextBox'=>null,'ctlAutoSelect'=>null,'ctlCalendar'=>null]);
		$this->controls['Result']->setProperty('value', '3');
		foreach($controls as $ctl)
			$ctl->clear();
		unset($this->op_id);
	}

	public function BuildReport()
	{
		$amount = array('Success'=>array(),'Failure'=>array(), 'In-Progress'=>array());
		$success = HighCharts::lastXMonths(6);
		$failure = HighCharts::lastXMonths(6);
		$in_progress = HighCharts::lastXMonths(6);

		$r = db::query("SELECT ExpectedClose, Amount, Result FROM {$this->prefix}crm_opportunities WHERE 1 ORDER BY ExpectedClose asc");

		while($row = $r->fetch_object())
		{
			$month = date('M Y', $row->ExpectedClose);
			if( ! isset($amount[$row->Result][$month]) )
			{
				$amount[$row->Result][$month] = $row->Amount;
				$amount['Success'][$month] = isset($amount['Success'][$month]) ? $amount['Success'][$month] : 0;
				$amount['Failure'][$month] = isset($amount['Failure'][$month]) ? $amount['Failure'][$month] : 0;
				$amount['In-Progress'][$month] = isset($amount['In-Progress'][$month]) ? $amount['In-Progress'][$month] : 0;
			}
			else
				$amount[$row->Result][$month] += $row->Amount;

			if ( ! isset($categories[$month]) )
				$categories[$month] = "'".$month."'";

			switch($row->Result)
			{
				case 'Failure':
					if(isset($failure[$month]))
						$failure[$month] += 1;
					break;
				case 'Success':
					if(isset($success[$month]))
						$success[$month] += 1;
					break;
				case 'In-Progress':
					if(isset($in_progress[$month]))
						$in_progress[$month] += 1;
					break;
			}
		}

		$amount_success = HighCharts::GraphPreData('Succeeded').implode(',', $amount['Success']).HighCharts::GraphPostData();
		$amount_failure = HighCharts::GraphPreData('Failed').implode(',', $amount['Failure']).HighCharts::GraphPostData();
		$amount_in_progress = HighCharts::GraphPreData('In-Progress').implode(',', $amount['In-Progress']).HighCharts::GraphPostData();

		if(isset($this->controls['opportunitiesgraph']))
			$this->controls['opportunitiesgraph']->close();

		$def = HighCharts::BuildGraphBar('opportunitiesgraph', 'Opportunity Report', 'Amount', '<b>{series.name}: {point.y}</b>', implode(',', $categories), "$amount_success, $amount_failure, $amount_in_progress");

		$ctl = new ctlLabel($this, $def, $this->controls['graph']);

		if(isset($this->controls['opportunitiesresultgraph']))
			$this->controls['opportunitiesresultgraph']->close();

		$success = HighCharts::GraphPreData('Succeeded').implode(',', $success).HighCharts::GraphPostData();
		$failure = HighCharts::GraphPreData('Failed').implode(',', $failure).HighCharts::GraphPostData();
		$in_progress = HighCharts::GraphPreData('In-Progress').implode(',', $in_progress).HighCharts::GraphPostData();

		$months = array_keys(HighCharts::lastXMonths(6));

		foreach($months as $idx => $month)
			$months[$idx] = "'$month'";

		$def = HighCharts::BuildGraphBar('opportunitiesresultgraph', 'Opportunity Result Report (Last 6 Mo.)', 'Result', '<b>{series.name}: {point.y}</b>', implode(',', $months), "$success, $failure, $in_progress");
		new ctlLabel($this, $def, $this->controls['graph']);
	}

}

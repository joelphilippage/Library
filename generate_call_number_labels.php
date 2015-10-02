<?

include_once(dirname(__FILE__) . "/first.php");


$limit = urldecode($query[0]);

if($barcode_category != "action")
{
	if(! $accounts->account_record->ID)
	{
		exit_error("Not Signed In", "You must be signed into perform that action.");
	}; // end if

	if($accounts->account_record->librarian != "Y")
	{
		exit_error("Access Denied", "Only a librarian can perform that action.");
	}; // end if
}; // end if

if($limit == "clear")
{
	$clear_sql = "
		UPDATE
			`" . $db->table_library_item . "`
		SET
			`queue_call_number`='0'
		WHERE
			`queue_call_number`!='0'
	";
	mysql_query($clear_sql, $mysql->link);
	?>
	<script language="javascript"><!--
		alert("Call Number Queue Cleared");
	//--></script>
	<?
	exit();
}; // end if


include_once($db->include_dir . "/pdf_maker.php");

$xo_reset = 1;
$yo_reset = 0.4;

$xo = $xo_reset;
$yo = $yo_reset;

$cw = 2.6666666 / 3;
$ch = 1;

$pr = 8.25;
$pb = 10.75;

$pm->set_font("helvetica");


$mc = ceil(($pr - $yo_reset) / $cw);
$mr = ceil(($pb - $xo_reset) / $ch);

$total_per_page = $mc * $mr;


$sql = "
	SELECT
		`" . $db->table_library_item . "`.`ID` AS 'ID',
		`" . $db->table_library_item . "`.`call_number` AS 'call_number',
		`" . $db->table_library_category . "`.`call_number_line_length` AS 'call_number_line_length'
	FROM
		`" . $db->table_library_item . "`,
		`" . $db->table_library_category . "`
	WHERE
		`" . $db->table_library_item . "`.`queue_call_number`>0
		AND `" . $db->table_library_item . "`.`" . $db->field_library_category_ID . "`=`" . $db->table_library_category . "`.`ID`
		AND `" . $db->table_library_item . "`.`enabled`='Y'
	ORDER BY
		`" . $db->table_library_item . "`.`queue_call_number` DESC
";
$result = mysql_query($sql, $mysql->link);

$total_queued = mysql_num_rows($result);

$to_clear = array();

if($total_queued < $total_per_page && $limit == "nopartial")
{
	$total_queued = 0;
}; // end if

if($total_queued > 0)
{
	$label_col = 0;
	while($record = mysql_fetch_object($result))
	{
		if($xo > $pb)
		{
			$xo = $xo_reset;
			$yo += $cw;
			$label_col++;

			if($label_col > 2)
			{
				$yo += 0.1;
				$label_col = 0;
			}; // end if

			if($yo > $pr)
			{
				$yo = $yo_reset;
				$xo = $xo_reset;
				$label_col = 0;

				if($total_queued < $total_per_page && $limit == "nopartial")
				{
					break;
				}; // end if

				$pm->new_page();
			}; // end if
		}; // end if

		$call_numbers = explode(",", $record->call_number);
		$lxo = ((count($call_numbers) / 2) * 0.15) * -1;
		$row = 0;
		foreach($call_numbers as $call_number)
		{
			if($record->call_number_line_length > 0)
			{
				$call_number = substr($call_number, 0, $record->call_number_line_length);
			}; // end if
			$pm->text($xo + $lxo + ($row * 0.15), $yo, 0.15, "<b>" . strtoupper($call_number) . "<b>", "left");
			$total_queued--;
			$row++;
		}; // end foreach
		//$pm->text($xo + ($row * 0.15), $yo, 0.15, "<b>" . $record->ID . "<b>", "left");

		$to_clear[] = $record->ID;

		$xo += $ch;
	}; // end while
}; // end if


$pm->stream(date("YmdHis") . ".pdf", TRUE);

?>
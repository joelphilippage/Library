<?

include_once(dirname(__FILE__) . "/first.php");

$barcode = urldecode($query[0]);

if(! $kiosk_account_record->ID)
{
	exit_error("Not Signed In", "You must be signed into perform that action.");
}; // end if

if($kiosk_account_record->librarian != "Y")
{
	exit_error("Access Denied", "Only a librarian can perform that action.");
}; // end if

$db->show_search = FALSE;

if($barcode != "")
{
	$db->settings->library_call_number_queue_order++;
	save_db_settings();

	$sql = "
		SELECT
			*
		FROM
			`" . $db->table_library_item . "`
		WHERE
			`barcode`='" . mysql_escape_string($barcode) . "'
	";
	$result = mysql_query($sql, $mysql->link);
	if(mysql_num_rows($result) > 0)
	{
		$record = mysql_fetch_object($result);
		$last_call_number = $record->call_number;
	}; // end if

	$sql = "
		UPDATE
			`" . $db->table_library_item . "`
		SET
			`queue_call_number`='" . $db->settings->library_call_number_queue_order . "'
		WHERE
			`barcode`='" . mysql_escape_string($barcode) . "'
	";
	mysql_query($sql, $mysql->link);

	$db->kiosk_sound = $db->common_sound_url . "/kling.mp3";

}; // end if

$sql = "
	SELECT
		`" . $db->table_library_item . "`.`ID` AS 'ID'
	FROM
		`" . $db->table_library_item . "`,
		`" . $db->table_library_category . "`
	WHERE
		`" . $db->table_library_item . "`.`queue_call_number`>0
		AND `" . $db->table_library_item . "`.`" . $db->field_library_category_ID . "`=`" . $db->table_library_category . "`.`ID`
		AND `" . $db->table_library_item . "`.`enabled`='Y'
";
$result = mysql_query($sql, $mysql->link);
$total_queued = mysql_num_rows($result);

$left_on_sheet = 90 - ($total_queued % 90);


include_once(dirname(__FILE__) . "/kiosk_top.php");
?>
<div class="dialog_instructions" style="margin-top:30px;">Scan items that need a call number printed.</div>
<div style="font-size:60px;text-align:center;"><?= preg_replace("/,/", "<br>", $last_call_number); ?></div>
<div style="font-size:36px;text-align:center;">You need <span style="font-size:50px;"><?= $left_on_sheet; ?></span> more to fill a sheet.</div>
<?
$kiosk_mode = $db->barcode_queue_call_number;
$kiosk_value = $item_value;
include_once(dirname(__FILE__) . "/kiosk_bottom.php");

exit();


?>
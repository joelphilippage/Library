<?

include_once(dirname(__FILE__) . "/first.php");

$db->show_search = FALSE;

$item_ID = $query[0];

if(! $accounts->account_record->ID)
{
	exit_error("Not Signed In", "You must be signed into perform that action.");
}; // end if

if($accounts->account_record->librarian != "Y" && $accounts->account_record->admin != "Y")
{
	exit_error("Access Denied", "Only a librarian can perform that action.");
}; // end if


$checkout_sql = "
	UPDATE
		`" . $db->table_library_checkout . "`
	SET
		`in_datetime`='" . date("Y-m-d H:i:s") . "'
	WHERE
		`" . $db->field_library_item_ID . "`='" . mysql_escape_string($item_ID) . "'
		AND `lost_datetime`!='" . $db->blank_datetime . "'
		AND `in_datetime`='" . $db->blank_datetime . "'
";
mysql_query($checkout_sql, $mysql->link);

$sql = "
	UPDATE
		`" . $db->table_library_item . "`
	SET
		`lost_datetime`='" . $db->blank_datetime . "',
		`lost_account_ID`='0',
		`" . $db->field_checkout_ID . "`='0',
		`enabled`='Y'
	WHERE
		`ID`='" . mysql_escape_string($item_ID) . "'
";
mysql_query($sql, $mysql->link);

header("location:" . $db->url_root . "/item_details.php/ID/" . $item_ID);
exit();

?>
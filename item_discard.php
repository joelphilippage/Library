<?

include_once(dirname(__FILE__) . "/first.php");

$db->show_search = FALSE;

$item_ID = $query[0];
$option = $query[1];

if(! $accounts->account_record->ID)
{
	exit_error("Not Signed In", "You must be signed into perform that action.");
}; // end if

if($accounts->account_record->librarian != "Y")
{
	exit_error("Access Denied", "Only a librarian can perform that action.");
}; // end if


$checkout_sql = "
	SELECT
		*
	FROM
		`" . $db->table_library_checkout . "`
	WHERE
		`" . $db->field_library_item_ID . "`='" . mysql_escape_string($item_ID) . "'
		AND `in_datetime`='" . $db->blank_datetime . "'
";
$checkout_result = mysql_query($checkout_sql, $mysql->link);
if(mysql_num_rows($checkout_result) > 0)
{
	$checkout_record = mysql_fetch_object($checkout_result);

	$account_sql = "
		SELECT
			*
		FROM
			`" . $db->table_account . "`
		WHERE
			`ID`='" . $checkout_record->{$db->field_account_ID} . "'
	";
	$account_result = mysql_query($account_sql, $mysql->link);
	$account_record = mysql_fetch_object($account_result);
	exit_error("Cannot Discard Item", "The item you are trying to discard is currently checked out by " . $account_record->name);
}; // end if


$update_links_sql = "
	UPDATE
		`" . $db->table_library_item_link . "`
	SET
		`enabled`='N'
	WHERE
		`" . $db->field_library_item_ID . "`='" . mysql_escape_string($item_ID) . "'
";
mysql_query($update_links_sql, $mysql->link);


$sql = "
	UPDATE
		`" . $db->table_library_item . "`
	SET
		`deleted_datetime`='" . date("Y-m-d H:i:s") . "',
		`deleted_account_ID`='" . $accounts->account_record->ID . "',
		" . ($option == "clearbarcode" ? "`barcode`='0'," : "") . "
		`enabled`='N'
	WHERE
		`ID`='" . mysql_escape_string($item_ID) . "'
";
mysql_query($sql, $mysql->link);

if($option == "wizard")
{
	header("location:" . $db->url_root . "/wizard_item_discard.php");
}
else
{
	header("location:" . $db->url_root . "/item_details.php/ID/" . $item_ID);
}; // end if

exit();

?>
<?

include_once(dirname(__FILE__) . "/first.php");

$db->show_search = FALSE;

$item_ID = $query[0];
$option = $query[1];

if(! $accounts->account_record->ID)
{
	exit_error("Not Signed In", "You must be signed into perform that action.");
}; // end if

if($accounts->account_record->librarian != "Y" && $accounts->account_record->admin != "Y")
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
	exit_error("Cannot Purge Item", "The item you are trying to purge is currently checked out by " . $account_record->name);
}; // end if


$update_links_sql = "
	DELETE FROM
		`" . $db->table_library_item_link . "`
	WHERE
		`" . $db->field_library_item_ID . "`='" . mysql_escape_string($item_ID) . "'
";
mysql_query($update_links_sql, $mysql->link);


$sql = "
	DELETE FROM
		`" . $db->table_library_item . "`
	WHERE
		`ID`='" . mysql_escape_string($item_ID) . "'
";
mysql_query($sql, $mysql->link);

header("location:" . $db->url_root . "/index.php");
exit();

?>
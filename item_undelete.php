<?

include_once(dirname(__FILE__) . "/first.php");

$db->show_search = FALSE;

$item_ID = $query[0];

if(! $accounts->account_record->ID)
{
	exit_error("Not Signed In", "You must be signed into perform that action.");
}; // end if

if($accounts->account_record->librarian != "Y")
{
	exit_error("Access Denied", "Only a librarian can perform that action.");
}; // end if


$update_links_sql = "
	UPDATE
		`" . $db->table_library_item_link . "`
	SET
		`enabled`='Y'
	WHERE
		`" . $db->field_library_item_ID . "`='" . mysql_escape_string($item_ID) . "'
";
mysql_query($update_links_sql, $mysql->link);


$sql = "
	UPDATE
		`" . $db->table_library_item . "`
	SET
		`enabled`='Y'
	WHERE
		`ID`='" . mysql_escape_string($item_ID) . "'
";
mysql_query($sql, $mysql->link);

header("location:" . $db->url_root . "/item_details.php/ID/" . $item_ID);
exit();

?>
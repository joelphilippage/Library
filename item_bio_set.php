<?

include_once(dirname(__FILE__) . "/first.php");


$record_ID = urldecode($query[0]);
$call_number_suffix = urldecode($query[1]);

if(! $accounts->account_record->ID)
{
	exit_error("Not Signed In", "You must be signed into perform that action.");
}; // end if

if($accounts->account_record->librarian != "Y" && $accounts->account_record->admin != "Y")
{
	exit_error("Access Denied", "Only a librarian can perform that action.");
}; // end if


$sql = "
	UPDATE
		`" . $db->table_library_item . "`
	SET
		`" . $db->field_category_ID . "`='46',
		`call_number`='J,BIO," . mysql_escape_string($call_number_suffix) . "'
	WHERE
		`ID`='" . mysql_escape_string($record_ID) . "'
		OR `" . $db->field_library_copy_item_ID . "`='" . mysql_escape_string($record_ID) . "'
";
mysql_query($sql, $mysql->link);
header("location:" . $db->url_root . "/item_details.php/ID/" . $record_ID);
exit();



?>
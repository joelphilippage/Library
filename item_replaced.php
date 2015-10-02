<?

include_once(dirname(__FILE__) . "/first.php");

$db->show_search = FALSE;

$item_ID = $query[0];
$new_barcode = $query[1];

if(! $_SESSION['library_account_ID'])
{
	exit_error("Not Signed In", "You must be signed into perform that action.");
}; // end if

if(! $db->is_librarian_account)
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
		AND `in_datetime`='" . $db->blank_datetime . "'
";
//echo $checkout_sql . "<br><br>";
mysql_query($checkout_sql, $mysql->link);

$sql = "
	UPDATE
		`" . $db->table_library_item . "`
	SET
		`replaced_datetime`='" . date("Y-m-d H:i:s") . "'
	WHERE
		`ID`='" . mysql_escape_string($item_ID) . "'
";
//echo $sql . "<br><br>";
mysql_query($sql, $mysql->link);

header("location:" . $db->url_root . "/item_edit.php/replacement/" . $item_ID);
exit();


?>
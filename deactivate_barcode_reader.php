<?

include_once(dirname(__FILE__) . "/first.php");


$barcode = urldecode($query[0]);


if(! $_SESSION['library_account_ID'])
{
	exit_error("Not Signed In", "You must be signed into perform that action.");
}; // end if

if(! $db->is_librarian_account)
{
	exit_error("Access Denied", "Only a librarian can perform that action.");
}; // end if

$sql = "
	UPDATE
		`" . $db->table_computer . "`
	SET
		`barcode_reader`='N'
	WHERE
		`ID`='" . mysql_escape_string($accounts->computer_record->ID) . "'
";
mysql_query($sql, $mysql->link);

$db->home_reset = 4;

include_once(dirname(__FILE__) . "/top.php");

?><div class="dialog_title">Barcode Reader Deactivated</div><?

include_once(dirname(__FILE__) . "/bottom.php");


?>
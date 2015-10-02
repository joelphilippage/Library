<?

include_once(dirname(__FILE__) . "/first.php");


$barcode = urldecode($query[0]);


$sql = "
	UPDATE
		`" . $db->table_computer . "`
	SET
		`library_kiosk`='Y'
	WHERE
		`ID`='" . mysql_escape_string($accounts->computer_record->ID) . "'
";
mysql_query($sql, $mysql->link);

$accounts->computer_record->library_kiosk = "Y";
$db->home_reset = 4;
$_SESSION['library_account_ID'] = 0;

header("location:" . $db->url_root . "/index.php");
exit();
?>
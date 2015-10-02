<?

include_once(dirname(__FILE__) . "/first.php");


$item_ID = $query[0];
$item_barcode = $query[1];

if(strlen(trim($item_barcode)) != $db->barcode_length || substr(trim($item_barcode), 0, 1) != $db->barcode_item_prefix)
{
	exit_error("Invalid Barcode: " . $item_barcode, "The barcode you entered is not valid.");
}; // end if

$barcode_sql = "
	SELECT
		*
	FROM
		`" . $db->table_library_item . "`
	WHERE
		`barcode`='" . mysql_escape_string(trim($item_barcode)) . "'
		AND `ID`!='" . $item_ID . "'
";
$barcode_result = mysql_query($barcode_sql, $mysql->link);
if(mysql_num_rows($barcode_result) > 0)
{
	exit_error("Duplicate Barcode: " . $item_barcode, "The barcode you entered is already in use.");
}; // end if

$sql = "
	SELECT
		*
	FROM
		`" . $db->table_library_item . "`
	WHERE
		`ID`='" . $item_ID . "'
";
$result = mysql_query($sql, $mysql->link);
$record = mysql_fetch_object($result);


if(
		$record->{$db->field_category_ID} == 16
		|| $record->{$db->field_category_ID} == 7
		|| $record->call_number == "E"
		|| substr($record->call_number, 0, 2) == "E,"
	)
{
	$authors = lookup_author($record->ID);

	$new_call_number = "E," . strtoupper(substr($authors[0]->last_name, 0, 3));
}
else
{
	$new_call_number = $record->call_number;
}; // end if


$sql = "
	UPDATE
		`" . $db->table_library_item . "`
	SET
		`barcode`='" . mysql_escape_string($item_barcode) . "',
		`call_number`='" . $new_call_number . "'
	WHERE
		`ID`='" . $item_ID . "'
";
//exit($sql);
mysql_query($sql, $mysql->link);

header("location:" . $db->url_root . "/item_details.php/barcode/" . $item_barcode);
exit();

?>
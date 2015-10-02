<?

include_once(dirname(__FILE__) . "/first.php");


$sql = "
	SELECT
		*
	FROM
		`" . $db->table_library_item . "`
	WHERE
		`call_number` LIKE 'BIB,%'
";
$result = mysql_query($sql, $mysql->link);

while($record = mysql_fetch_object($result))
{
	$new_call_number = "REF," . $record->call_number;
	echo $new_call_number . "<br>";
	$update_sql = "
		UPDATE
			`" . $db->table_library_item . "`
		SET
			`call_number`='" . $new_call_number . "'
		WHERE
			`ID`='" . $record->ID . "'
	";
	mysql_query($update_sql, $mysql->link);
}; // end while


?>
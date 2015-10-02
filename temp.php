<?

exit("temp coding");

include_once(dirname(__FILE__) . "/first.php");

$sql = "
	SELECT
		*
	FROM
		`library_item`
	WHERE
		`enabled`='Y'
		AND `call_number` LIKE 'P,ARI,%'
";

$result = mysql_query($sql, $mysql->link);

while($record = mysql_fetch_object($result))
{
	$new_call_number = substr($record->call_number, 0, 2) . "AZH" . substr($record->call_number, 5, 9);
	//echo $record->call_number . "<br>" . $new_call_number . "<br><br>";
	$update_sql = "
		UPDATE
			`library_item`
		SET
			`call_number`='" . $new_call_number . "'
		WHERE
			`ID`='" . $record->ID . "'
	";
	mysql_query($update_sql, $mysql->link);
	echo $update_sql . "<br>";
}; // end while

?>
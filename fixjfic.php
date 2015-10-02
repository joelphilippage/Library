<?

include_once(dirname(__FILE__) . "/first.php");


$sql = "
	UPDATE
		`library_item`
	SET
		`location_ID`='1',
		`category_ID`='5'
	WHERE
		(
			`call_number` LIKE 'J,0%'
			OR `call_number` LIKE 'J,1%'
			OR `call_number` LIKE 'J,2%'
			OR `call_number` LIKE 'J,3%'
			OR `call_number` LIKE 'J,4%'
			OR `call_number` LIKE 'J,5%'
			OR `call_number` LIKE 'J,6%'
			OR `call_number` LIKE 'J,7%'
			OR `call_number` LIKE 'J,8%'
			OR `call_number` LIKE 'J,9%'
		)
		AND `call_number` NOT LIKE '%]'
		AND `enabled`='Y'
";
mysql_query($sql, $mysql->link);
exit();

$result = mysql_query($sql, $mysql->link);

echo mysql_num_rows($result) . "<br>";

while($record = mysql_fetch_object($result))
{
	echo $record->call_number . "<br>";

}; // end while

?>
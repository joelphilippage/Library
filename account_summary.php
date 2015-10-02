<?

include_once(dirname(__FILE__) . "/first.php");


$library_account_ID = urldecode($query[0]);


if(! $_SESSION['library_account_ID'])
{
	exit_error("Not Signed In", "You must be signed into perform that action.");
}; // end if

if(! $db->is_librarian_account && FALSE)
{
	exit_error("Access Denied", "Only a librarian can perform that action.");
}; // end if

if($library_account_ID < 1)
{
	$library_account_ID = $_SESSION['library_account_ID'];
}; // end if

include_once(dirname(__FILE__) . "/top.php");

$sql = "
	SELECT
		*
	FROM
		`" . $db->table_library_checkout . "`
	WHERE
		`" . $db->field_account_ID . "`='" . $library_account_ID . "'
		AND `in_datetime`='" . $db->blank_datetime . "'
";
$result = mysql_query($sql, $mysql->link);

if(mysql_num_rows($result) > 0)
{
	?><h1>Items Checked Out</h1><?

	while($record = mysql_fetch_object($result))
	{
		$item_sql = "
			SELECT
				*
			FROM
				`" . $db->table_library_item . "`
			WHERE
				`ID`='" . $record->item_ID . "'
		";
		$item_result = mysql_query($item_sql, $mysql->link);
		$item_record = mysql_fetch_object($item_result);

		echo $item_record->title . "<br>";

	}; // end while
}; // end if


include_once(dirname(__FILE__) . "/bottom.php");

?>
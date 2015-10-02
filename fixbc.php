<?

include_once(dirname(__FILE__) . "/first.php");


exit("I used this file to fix a mistake a made making one author the author of about 1,500 books.  Good thing I had made a backup the day before!!!! Whew!");

$library_account_ID = urldecode($query[0]);


if(! $_SESSION['library_account_ID'])
{
	exit_error("Not Signed In", "You must be signed into perform that action.");
}; // end if

if(! $db->is_librarian_account && FALSE)
{
	exit_error("Access Denied", "Only a librarian can perform that action.");
}; // end if

$sql = "
	SELECT
		*
	FROM
		`library_item_link`
	WHERE
		`record_ID`='577'
		AND `item_link_type_ID`!=5
		AND `item_link_type_ID`!=7
	GROUP BY
		`item_ID`
	LIMIT 100
";
$result = mysql_query($sql, $mysql->link);

while($record = mysql_fetch_object($result))
{
	echo $record->item_ID . "::" . $record->record_ID . "<br>";

	$clear_sql = "
		DELETE FROM
			`library_item_link`
		WHERE
			`ID`='" . $record->ID . "'
		LIMIT 1
	";
	mysql_query($clear_sql, $mysql->link);
	echo $clear_sql . "<br>";

	$backup_sql = "
		SELECT
			*
		FROM
			`library_item_link_backup`
		WHERE
			`item_ID`='" . $record->item_ID . "'
	";
	$backup_result = mysql_query($backup_sql, $mysql->link);
	while($backup_record = mysql_fetch_object($backup_result))
	{
		//echo "&nbsp; &nbsp;" . $backup_record->ID . "::" . $backup_record->record_ID . "<Br>";
		$update_sql = "
			INSERT INTO
				`library_item_link`
			SET
				`record_ID`='" . $backup_record->record_ID . "',
				`item_ID`='" . $backup_record->item_ID . "',
				`item_link_type_ID`='" . $backup_record->item_link_type_ID . "',
				`section_title`='" . $backup_record->section_title . "',
				`section_start`='" . $backup_record->section_start . "',
				`priority`='" . $backup_record->priority . "',
				`enabled`='" . $backup_record->enabled . "'
		";
		echo $update_sql . "<br>";
		mysql_query($update_sql, $mysql->link);
	

	}; // end while

	echo "<br><br>";

	//echo $record->ID . "<BR>";
	//print_r($record);
	//echo "<Br><Br>";
}; // end while

?>
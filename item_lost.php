<?

include_once(dirname(__FILE__) . "/first.php");

$db->show_search = FALSE;

$item_ID = $query[0];
$lost_fee = $query[1];

$lost_fee = preg_replace("/\$/", "", $lost_fee);
if($lost_fee < 3)
{
	$lost_fee = 3;
}; // end if

if(! $accounts->account_record->ID)
{
	exit_error("Not Signed In", "You must be signed into perform that action.");
}; // end if

if($accounts->account_record->librarian != "Y" && $accounts->account_record->admin != "Y")
{
	exit_error("Access Denied", "Only a librarian can perform that action.");
}; // end if


if($lost_fee < 1)
{
	exit_error("No Cost Specified", "You must specify how much the lost fine is.");
}
else
{
	$checkout_sql = "
		SELECT
			*
		FROM
			`" . $db->table_library_checkout . "`
		WHERE
			`" . $db->field_library_item_ID . "`='" . mysql_escape_string($item_ID) . "'
			AND `in_datetime`='" . $db->blank_datetime . "'
	";
	$checkout_result = mysql_query($checkout_sql, $mysql->link);
	if(mysql_num_rows($checkout_result) > 0)
	{
		$lost_type = "patron";

		$checkout_record = mysql_fetch_object($checkout_result);
		$patron_account_sql = "
			SELECT
				*
			FROM
				`" . $db->table_account . "`
			WHERE
				`ID`='" . $checkout_record->{$db->field_account_ID} . "'
		";
		$patron_account_result = mysql_query($patron_account_sql, $mysql->link);
		$patron_account_record = mysql_fetch_object($patron_account_result);
	}
	else
	{
		$lost_type = "librarian";
	}; // end if

	if($lost_type == "patron")
	{
		$checkout_sql = "
			UPDATE
				`" . $db->table_library_checkout . "`
			SET
				`lost_datetime`='" . date("Y-m-d H:i:s") . "',
				`lost_fee`='" . mysql_escape_string($lost_fee) . "'
			WHERE
				`" . $db->field_library_item_ID . "`='" . mysql_escape_string($item_ID) . "'
				AND `in_datetime`='" . $db->blank_datetime . "'
		";
		mysql_query($checkout_sql, $mysql->link);
	}; // end if

	$sql = "
		UPDATE
			`" . $db->table_library_item . "`
		SET
			`lost_datetime`='" . date("Y-m-d H:i:s") . "',
			`lost_account_ID`='" . ($lost_type == "patron" ? $patron_account_record->ID : "0") . "',
			`enabled`='N'
		WHERE
			`ID`='" . mysql_escape_string($item_ID) . "'
	";
	mysql_query($sql, $mysql->link);


	header("location:" . $db->url_root . "/item_details.php/ID/" . $item_ID);
	exit();
}; // end if


?>
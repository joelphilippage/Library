<?

include_once(dirname(__FILE__) . "/first.php");


$checkout_ID = urldecode($query[0]);
$new_datetime = urldecode($query[1]);


if(! $_SESSION['library_account_ID'])
{
	exit_error("Not Signed In", "You must be signed into perform that action.");
}; // end if

if(! $db->is_librarian_account)
{
	exit_error("Access Denied", "Only a librarian can perform that action.");
}; // end if

$checkout_sql = "
	SELECT
		*
	FROM
		`" . $db->table_library_checkout . "`
	WHERE
		`ID`='" . $checkout_ID . "'
";
$checkout_result = mysql_query($checkout_sql, $mysql->link);
$checkout_record = mysql_fetch_object($checkout_result);

$patron_sql = "
	SELECT
		*
	FROM
		`" . $db->table_account . "`
	WHERE
		`ID`='" . $checkout_record->{$db->field_account_ID} . "'
";
$patron_result = mysql_query($patron_sql, $mysql->link);
$patron_record = mysql_fetch_object($patron_result);




$due_date = date("Y-m-d 23:59:59", strtotime($new_datetime) + (60 * 60 * 24 * $patron_record->library_checkout_length_read));

$sql = "
	UPDATE
		`" . $db->table_library_checkout . "`
	SET
		`out_datetime`='" . mysql_escape_string($new_datetime) . "',
		`due_datetime`='" . $due_date . "'
	WHERE
		`ID`='" . $checkout_ID . "'
";
if(! mysql_query($sql, $mysql->link))
{
	?>
	<script language="javascript">
		alert("error setting checkout date");
	</script>
	<?
}; // end if

?>
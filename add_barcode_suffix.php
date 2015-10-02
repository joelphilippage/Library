<?

include_once(dirname(__FILE__) . "/first.php");

$db->show_search = FALSE;

$suffix = $query[0];

if(! $accounts->account_record->ID)
{
	exit_error("Not Signed In", "You must be signed into perform that action.");
}; // end if

if($accounts->account_record->librarian != "Y" && $accounts->account_record->admin != "Y")
{
	exit_error("Access Denied", "Only a librarian can perform that action.");
}; // end if


$submit_caption = "Go";

$the_subjects = array();


if($_POST['action'] == $submit_caption)
{
	$sql = "
		SELECT
			*
		FROM
			`" . $db->table_library_item . "`
		WHERE
			`barcode`='" . mysql_escape_string($_POST['barcode']) . "'
		LIMIT 1
	";
	$result = mysql_query($sql, $mysql->link);
	if(mysql_num_rows($result) > 0)
	{
		$record = mysql_fetch_object($result);
		$open = substr($record->call_number, strlen($record->call_number) - 3, 1);
		$close = substr($record->call_number, -1);

		if($open == "[" && $close == "]")
		{
			$old_cn = substr($record->call_number, 0, strlen($record->call_number) - 4);
		}
		else
		{
			$old_cn = $record->call_number;
		}; // end if

		$new_cn = $old_cn . ",[" . strtoupper($suffix) . "]";

		$sql = "
			UPDATE
				`" . $db->table_library_item . "`
			SET
				`call_number`='" . mysql_escape_string($new_cn) . "'
			WHERE
				`ID`='" . $record->ID . "'
		";
		mysql_query($sql, $mysql->link);
	}
	else
	{
		exit_error("Item Not Found", "Try scan again.");
	}; // end if

}; // end if

include_once(dirname(__FILE__) . "/top.php");

?>
<form method="post" action="" onsubmit="show_wait_message()">
	Add Suffix [<?= strtoupper($suffix); ?>] to...<br>
	<input id="barcode" name="barcode" type="text" size="8" autocomplete="off">
	<script language="javascript">
		document.getElementById('barcode').focus();
		document.getElementById('barcode').select();
	</script>
	<input type="submit" name="action" value="<?= $submit_caption; ?>">
</form>
<?

include_once(dirname(__FILE__) . "/bottom.php");

?>
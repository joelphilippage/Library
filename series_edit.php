<?

include_once(dirname(__FILE__) . "/first.php");

$db->show_search = FALSE;

$record_ID = $query[0];
$special_instructions = $query[2];
$special_instructions2 = $query[3];

if(! $accounts->account_record->ID)
{
	exit_error("Not Signed In", "You must be signed into perform that action.");
}; // end if

if($accounts->account_record->librarian != "Y" && $accounts->account_record->admin != "Y")
{
	exit_error("Access Denied", "Only a librarian can perform that action.");
}; // end if

$submit_caption = "Save Changes";

if($_POST['action'] == $submit_caption)
{
	if(trim($_POST['title']) == "")
	{
		$errors['title'] = TRUE;
	}; // end if

	if(count($errors) < 1)
	{
		$update_sql = "
		";

		$sql = "
			UPDATE
				`" . $db->table_series . "`
			SET
				`title`='" . mysql_escape_string(stripslashes($_POST['title'])) . "'
			WHERE
				`ID`='" . mysql_escape_string($record_ID) . "'
		";
		mysql_query($sql, $mysql->link);

		?>
		<script language="javascript">
			if(window.opener)
			{
				window.opener.location.reload();
			}; // end if
			window.close();
		</script>
		<?

		exit();
	}; // end if
}
else
{
	if($record_ID != "")
	{
		$sql = "
			SELECT
				*
			FROM
				`" . $db->table_series . "`
			WHERE
				`ID`='" . $record_ID . "'
		";

		$result = mysql_query($sql, $mysql->link);

		if(mysql_num_rows($result) < 1)
		{
			exit_error("Series #" . $record_ID . " not found.", "There is no series on record matching ID #" . $record_ID);
		}; // end if

		$record = mysql_fetch_array($result);

		foreach($record as $key=>$val)
		{
			$_POST[$key] = addslashes($val);
		}; // end foreach

	}; // end if
}; // end if






include_once(dirname(__FILE__) . "/top.php");

?>
<form enctype="multipart/form-data" method="post" action="" onsubmit="show_wait_message()">
<div class="heading">Edit Series</div>
<table class="edit_table" cellspacing="0" cellpadding="0" border="0">
	<tr>
		<td class="edit_caption<?= ($errors['title'] ? " edit_error" : ""); ?>"><nobr>Title:</nobr></td>
		<td class="edit_value">
			<input id="title" name="title" type="text" value="<?= htmlspecialchars(stripslashes($_POST['title'])); ?>" size="50" autocomplete="off">
		</td>
	</tr>
	<tr>
		<td></td>
		<td class="edit_value">
			<input type="submit" name="action" value="<?= $submit_caption; ?>">
		</td>
	</tr>
</table>
</form>
<?

include_once(dirname(__FILE__) . "/bottom.php");

?>
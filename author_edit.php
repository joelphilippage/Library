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
	if(trim($_POST['last_name']) == "")
	{
		$errors['last_name'] = TRUE;
	}; // end if

	if(count($errors) < 1)
	{
		$update_sql = "
		";

		$sql = "
			UPDATE
				`" . $db->table_author . "`
			SET
				`last_name`='" . mysql_escape_string(stripslashes($_POST['last_name'])) . "',
				`first_name`='" . mysql_escape_string(stripslashes($_POST['first_name'])) . "',
				`middle_name`='" . mysql_escape_string(stripslashes($_POST['middle_name'])) . "'
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
				`" . $db->table_author . "`
			WHERE
				`ID`='" . $record_ID . "'
		";

		$result = mysql_query($sql, $mysql->link);

		if(mysql_num_rows($result) < 1)
		{
			exit_error("Author #" . $record_ID . " not found.", "There is no authors on record matching ID #" . $record_ID);
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
<div class="heading">Edit Author</div>
<table class="edit_table" cellspacing="0" cellpadding="0" border="0">
	<tr>
		<td class="edit_caption<?= ($errors['last_name'] ? " edit_error" : ""); ?>"><nobr>Last Name:</nobr></td>
		<td class="edit_value">
			<input id="title" name="last_name" type="text" value="<?= htmlspecialchars(stripslashes($_POST['last_name'])); ?>" size="36" autocomplete="off">
		</td>
	</tr>
	<tr>
		<td class="edit_caption<?= ($errors['first_name'] ? " edit_error" : ""); ?>"><nobr>First Name:</nobr></td>
		<td class="edit_value">
			<input id="title" name="first_name" type="text" value="<?= htmlspecialchars(stripslashes($_POST['first_name'])); ?>" size="24" autocomplete="off">
		</td>
	</tr>
	<tr>
		<td class="edit_caption<?= ($errors['middle_name'] ? " edit_error" : ""); ?>"><nobr>Middle Name:</nobr></td>
		<td class="edit_value">
			<input id="title" name="middle_name" type="text" value="<?= htmlspecialchars(stripslashes($_POST['middle_name'])); ?>" size="24" autocomplete="off">
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
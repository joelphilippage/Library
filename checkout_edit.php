<?

include_once(dirname(__FILE__) . "/first.php");

$db->show_search = FALSE;

$record_ID = $query[0];

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
	if(count($errors) < 1)
	{
		$sorting_title = preg_replace("/^(The|A)\s/i", "", $_POST['title']);

		$update_sql = "
				`out_datetime`='" . mysql_escape_string(stripslashes($_POST['out_datetime'])) . "',
				`due_datetime`='" . mysql_escape_string(stripslashes($_POST['due_datetime'])) . "',
				`in_datetime`='" . mysql_escape_string(stripslashes($_POST['in_datetime'])) . "',
				`lost_datetime`='" . mysql_escape_string(stripslashes($_POST['lost_datetime'])) . "',
				`replaced_datetime`='" . mysql_escape_string(stripslashes($_POST['replaced_datetime'])) . "',
				`paid_datetime`='" . mysql_escape_string(stripslashes($_POST['paid_datetime'])) . "',
				`lost_fee`='" . mysql_escape_string(stripslashes($_POST['lost_fee'])) . "',
		";

		$sql = "
			UPDATE
				`" . $db->table_library_checkout . "`
			SET
				" . $update_sql . $unique_update_sql . "
				`duration`='" . date("Y-m-d H:i:s") . "'
			WHERE
				`ID`='" . mysql_escape_string($record_ID) . "'
		";
		mysql_query($sql, $mysql->link);

		?>
		<script language="javascript">
			window.opener.location.reload();
			window.close();
		</script>
		<?

		exit();
	}; // end if
}
else
{
	$sql = "
		SELECT
			*
		FROM
			`" . $db->table_library_checkout . "`
		WHERE
			`ID`='" . $record_ID . "'
	";

	$result = mysql_query($sql, $mysql->link);

	if(mysql_num_rows($result) < 1)
	{
		exit_error("Checkout #" . $item_ID . " not found.", "There are no checkout records matching ID #" . $item_ID);
	}; // end if

	$record = mysql_fetch_array($result);

	foreach($record as $key=>$val)
	{
		$_POST[$key] = addslashes($val);
	}; // end foreach
}; // end if






include_once(dirname(__FILE__) . "/top.php");


if(count($errors) > 0)
{
	foreach($errors as $key=>$val)
	{
		echo $key . "::" . $val . ", ";
	}; // end foreach
}; // end if

?>
<form enctype="multipart/form-data" method="post" action="" onsubmit="show_wait_message()">
<table class="edit_table" cellspacing="0" cellpadding="0" border="0">
	<tr>
		<td class="edit_caption<?= ($errors['out_datetime'] ? " edit_error" : ""); ?>"><nobr>Out Date/Time:</nobr></td>
		<td class="edit_value">
			<input id="out_datetime" name="out_datetime" type="text" value="<?= htmlspecialchars(stripslashes($_POST['out_datetime'])); ?>" size="32">
		</td>
	</tr>
	<tr>
		<td class="edit_caption<?= ($errors['due_datetime'] ? " edit_error" : ""); ?>"><nobr>Due Date/Time:</nobr></td>
		<td class="edit_value">
			<input id="due_datetime" name="due_datetime" type="text" value="<?= htmlspecialchars(stripslashes($_POST['due_datetime'])); ?>" size="32">
		</td>
	</tr>
	<tr>
		<td class="edit_caption<?= ($errors['in_datetime'] ? " edit_error" : ""); ?>"><nobr>In Date/Time:</nobr></td>
		<td class="edit_value">
			<input id="in_datetime" name="in_datetime" type="text" value="<?= htmlspecialchars(stripslashes($_POST['in_datetime'])); ?>" size="32">
		</td>
	</tr>
	<tr>
		<td class="edit_caption<?= ($errors['lost_datetime'] ? " edit_error" : ""); ?>"><nobr>Lost Date/Time:</nobr></td>
		<td class="edit_value">
			<input id="lost_datetime" name="lost_datetime" type="text" value="<?= htmlspecialchars(stripslashes($_POST['lost_datetime'])); ?>" size="32">
		</td>
	</tr>
	<tr>
		<td class="edit_caption<?= ($errors['replaced_datetime'] ? " edit_error" : ""); ?>"><nobr>Replaced Date/Time:</nobr></td>
		<td class="edit_value">
			<input id="replaced_datetime" name="replaced_datetime" type="text" value="<?= htmlspecialchars(stripslashes($_POST['replaced_datetime'])); ?>" size="32">
		</td>
	</tr>
	<tr>
		<td class="edit_caption<?= ($errors['replaced_datetime'] ? " edit_error" : ""); ?>"><nobr>Paid Date/Time:</nobr></td>
		<td class="edit_value">
			<input id="paid_datetime" name="paid_datetime" type="text" value="<?= htmlspecialchars(stripslashes($_POST['paid_datetime'])); ?>" size="32">
		</td>
	</tr>
	<tr>
		<td class="edit_caption<?= ($errors['lost_fee'] ? " edit_error" : ""); ?>"><nobr>Lost Fee:</nobr></td>
		<td class="edit_value">
			<input id="lost_fee" name="lost_fee" type="text" value="<?= htmlspecialchars(stripslashes($_POST['lost_fee'])); ?>" size="32">
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
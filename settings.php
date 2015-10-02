<?

include_once(dirname(__FILE__) . "/first.php");

$standalone_window = TRUE;
$title = "Library Settings";

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
		$db->settings->library_checkout_length_faculty = $_POST['library_checkout_length_faculty'];
		$db->settings->library_overdue_limit_faculty = $_POST['library_overdue_limit_faculty'];
		$db->settings->library_checkout_length_student = $_POST['library_checkout_length_student'];
		$db->settings->library_overdue_limit_student = $_POST['library_overdue_limit_student'];
		$db->settings->library_checkout_length_community = $_POST['library_checkout_length_community'];
		$db->settings->library_overdue_limit_community = $_POST['library_overdue_limit_community'];
		$db->settings->library_last_student_checkout_date = $_POST['library_last_student_checkout_date_year'] . "-" . $_POST['library_last_student_checkout_date_month'] . "-" . $_POST['library_last_student_checkout_date_day'];
		$db->settings->library_last_student_due_date = $_POST['library_last_student_due_date_year'] . "-" . $_POST['library_last_student_due_date_month'] . "-" . $_POST['library_last_student_due_date_day'];
		save_db_settings();

		?>
		<script language="javascript">
			window.close();
		</script>
		<?

		exit();
	}; // end if
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
<form class="form_container" enctype="multipart/form-data" method="post" action="" onsubmit="show_wait_message()">
	<table class="edit_table" cellspacing="0" cellpadding="0" border="0">
		<tr>
			<td class="edit_caption<?= ($errors['library_checkout_length_faculty'] ? " edit_error" : ""); ?>"><nobr>Checkout length for staff:</nobr></td>
			<td class="edit_value">
				<input id="library_checkout_length_faculty" name="library_checkout_length_faculty" type="text" value="<?= htmlspecialchars(stripslashes($db->settings->library_checkout_length_faculty)); ?>" size="3"> days
			</td>
		</tr>
		<tr>
			<td class="edit_caption<?= ($errors['library_overdue_limit_faculty'] ? " edit_error" : ""); ?>"><nobr>Overdue limit for staff:</nobr></td>
			<td class="edit_value">
				<input id="library_overdue_limit_faculty" name="library_overdue_limit_faculty" type="text" value="<?= htmlspecialchars(stripslashes($db->settings->library_overdue_limit_faculty)); ?>" size="3"> items
			</td>
		</tr>
		<tr>
			<td class="edit_caption<?= ($errors['library_checkout_length_student'] ? " edit_error" : ""); ?>"><nobr>Checkout length for students:</nobr></td>
			<td class="edit_value">
				<input id="library_checkout_length_student" name="library_checkout_length_student" type="text" value="<?= htmlspecialchars(stripslashes($db->settings->library_checkout_length_student)); ?>" size="3"> days
			</td>
		</tr>
		<tr>
			<td class="edit_caption<?= ($errors['library_overdue_limit_student'] ? " edit_error" : ""); ?>"><nobr>Overdue limit for student:</nobr></td>
			<td class="edit_value">
				<input id="library_overdue_limit_student" name="library_overdue_limit_student" type="text" value="<?= htmlspecialchars(stripslashes($db->settings->library_overdue_limit_student)); ?>" size="3"> items
			</td>
		</tr>
		<tr>
			<td class="edit_caption<?= ($errors['library_checkout_length_community'] ? " edit_error" : ""); ?>"><nobr>Checkout length for community:</nobr></td>
			<td class="edit_value">
				<input id="library_checkout_length_community" name="library_checkout_length_community" type="text" value="<?= htmlspecialchars(stripslashes($db->settings->library_checkout_length_community)); ?>" size="3"> days
			</td>
		</tr>
		<tr>
			<td class="edit_caption<?= ($errors['library_overdue_limit_community'] ? " edit_error" : ""); ?>"><nobr>Overdue limit for community:</nobr></td>
			<td class="edit_value">
				<input id="library_overdue_limit_community" name="library_overdue_limit_community" type="text" value="<?= htmlspecialchars(stripslashes($db->settings->library_overdue_limit_community)); ?>" size="3"> items
			</td>
		</tr>
		<tr>
			<td class="edit_caption<?= ($errors['library_last_student_checkout_date'] ? " edit_error" : ""); ?>"><nobr>Last checkout date for students:</nobr></td>
			<td class="edit_value">
				<select id="library_last_student_checkout_date_month" name="library_last_student_checkout_date_month">
					<option value="01"<?= (substr($db->settings->library_last_student_checkout_date, 5, 2) == "01" ? " selected" : ""); ?>>Jan</option>
					<option value="02"<?= (substr($db->settings->library_last_student_checkout_date, 5, 2) == "02" ? " selected" : ""); ?>>Feb</option>
					<option value="03"<?= (substr($db->settings->library_last_student_checkout_date, 5, 2) == "03" ? " selected" : ""); ?>>Mar</option>
					<option value="04"<?= (substr($db->settings->library_last_student_checkout_date, 5, 2) == "04" ? " selected" : ""); ?>>Apr</option>
					<option value="05"<?= (substr($db->settings->library_last_student_checkout_date, 5, 2) == "05" ? " selected" : ""); ?>>May</option>
					<option value="06"<?= (substr($db->settings->library_last_student_checkout_date, 5, 2) == "06" ? " selected" : ""); ?>>Jun</option>
					<option value="07"<?= (substr($db->settings->library_last_student_checkout_date, 5, 2) == "07" ? " selected" : ""); ?>>Jul</option>
					<option value="08"<?= (substr($db->settings->library_last_student_checkout_date, 5, 2) == "08" ? " selected" : ""); ?>>Aug</option>
					<option value="09"<?= (substr($db->settings->library_last_student_checkout_date, 5, 2) == "09" ? " selected" : ""); ?>>Sep</option>
					<option value="10"<?= (substr($db->settings->library_last_student_checkout_date, 5, 2) == "10" ? " selected" : ""); ?>>Oct</option>
					<option value="11"<?= (substr($db->settings->library_last_student_checkout_date, 5, 2) == "11" ? " selected" : ""); ?>>Nov</option>
					<option value="12"<?= (substr($db->settings->library_last_student_checkout_date, 5, 2) == "12" ? " selected" : ""); ?>>Dec</option>
				</select>
				<select id="library_last_student_checkout_date_day" name="library_last_student_checkout_date_day">
					<option value="01"<?= (substr($db->settings->library_last_student_checkout_date, 8, 2) == "01" ? " selected" : ""); ?>>1st</option>
					<option value="02"<?= (substr($db->settings->library_last_student_checkout_date, 8, 2) == "02" ? " selected" : ""); ?>>2nd</option>
					<option value="03"<?= (substr($db->settings->library_last_student_checkout_date, 8, 2) == "03" ? " selected" : ""); ?>>3rd</option>
					<option value="04"<?= (substr($db->settings->library_last_student_checkout_date, 8, 2) == "04" ? " selected" : ""); ?>>4th</option>
					<option value="05"<?= (substr($db->settings->library_last_student_checkout_date, 8, 2) == "05" ? " selected" : ""); ?>>5th</option>
					<option value="06"<?= (substr($db->settings->library_last_student_checkout_date, 8, 2) == "06" ? " selected" : ""); ?>>6th</option>
					<option value="07"<?= (substr($db->settings->library_last_student_checkout_date, 8, 2) == "07" ? " selected" : ""); ?>>7th</option>
					<option value="08"<?= (substr($db->settings->library_last_student_checkout_date, 8, 2) == "08" ? " selected" : ""); ?>>8th</option>
					<option value="09"<?= (substr($db->settings->library_last_student_checkout_date, 8, 2) == "09" ? " selected" : ""); ?>>9th</option>
					<option value="10"<?= (substr($db->settings->library_last_student_checkout_date, 8, 2) == "10" ? " selected" : ""); ?>>10th</option>
					<option value="11"<?= (substr($db->settings->library_last_student_checkout_date, 8, 2) == "11" ? " selected" : ""); ?>>11th</option>
					<option value="12"<?= (substr($db->settings->library_last_student_checkout_date, 8, 2) == "12" ? " selected" : ""); ?>>12th</option>
					<option value="13"<?= (substr($db->settings->library_last_student_checkout_date, 8, 2) == "13" ? " selected" : ""); ?>>13th</option>
					<option value="14"<?= (substr($db->settings->library_last_student_checkout_date, 8, 2) == "14" ? " selected" : ""); ?>>14th</option>
					<option value="15"<?= (substr($db->settings->library_last_student_checkout_date, 8, 2) == "15" ? " selected" : ""); ?>>15th</option>
					<option value="16"<?= (substr($db->settings->library_last_student_checkout_date, 8, 2) == "16" ? " selected" : ""); ?>>16th</option>
					<option value="17"<?= (substr($db->settings->library_last_student_checkout_date, 8, 2) == "17" ? " selected" : ""); ?>>17th</option>
					<option value="18"<?= (substr($db->settings->library_last_student_checkout_date, 8, 2) == "18" ? " selected" : ""); ?>>18th</option>
					<option value="19"<?= (substr($db->settings->library_last_student_checkout_date, 8, 2) == "19" ? " selected" : ""); ?>>19th</option>
					<option value="20"<?= (substr($db->settings->library_last_student_checkout_date, 8, 2) == "20" ? " selected" : ""); ?>>20th</option>
					<option value="21"<?= (substr($db->settings->library_last_student_checkout_date, 8, 2) == "21" ? " selected" : ""); ?>>21st</option>
					<option value="22"<?= (substr($db->settings->library_last_student_checkout_date, 8, 2) == "22" ? " selected" : ""); ?>>22nd</option>
					<option value="23"<?= (substr($db->settings->library_last_student_checkout_date, 8, 2) == "23" ? " selected" : ""); ?>>23rd</option>
					<option value="24"<?= (substr($db->settings->library_last_student_checkout_date, 8, 2) == "24" ? " selected" : ""); ?>>24th</option>
					<option value="25"<?= (substr($db->settings->library_last_student_checkout_date, 8, 2) == "25" ? " selected" : ""); ?>>25th</option>
					<option value="26"<?= (substr($db->settings->library_last_student_checkout_date, 8, 2) == "26" ? " selected" : ""); ?>>26th</option>
					<option value="27"<?= (substr($db->settings->library_last_student_checkout_date, 8, 2) == "27" ? " selected" : ""); ?>>27th</option>
					<option value="28"<?= (substr($db->settings->library_last_student_checkout_date, 8, 2) == "28" ? " selected" : ""); ?>>28th</option>
					<option value="29"<?= (substr($db->settings->library_last_student_checkout_date, 8, 2) == "29" ? " selected" : ""); ?>>29th</option>
					<option value="30"<?= (substr($db->settings->library_last_student_checkout_date, 8, 2) == "30" ? " selected" : ""); ?>>30th</option>
					<option value="31"<?= (substr($db->settings->library_last_student_checkout_date, 8, 2) == "31" ? " selected" : ""); ?>>31st</option>
				</select>
				<select id="library_last_student_checkout_date_year" name="library_last_student_checkout_date_year">
					<?
						$first_year = date("Y") - 2;
						$last_year = date("Y") + 3;

						for($a = $first_year; $a < $last_year; $a++)
						{
							?>
							<option value="<?= $a; ?>"<?= (substr($db->settings->library_last_student_checkout_date, 0, 4) == $a ? " selected" : ""); ?>><?= $a; ?></option>
							<?
						}; // end for
					?>
				</select>
			</td>
		</tr>
		<tr>
			<td class="edit_caption<?= ($errors['library_last_student_due_date'] ? " edit_error" : ""); ?>"><nobr>Last due date for students:</nobr></td>
			<td class="edit_value">
				<select id="library_last_student_due_date_month" name="library_last_student_due_date_month">
					<option value="01"<?= (substr($db->settings->library_last_student_due_date, 5, 2) == "01" ? " selected" : ""); ?>>Jan</option>
					<option value="02"<?= (substr($db->settings->library_last_student_due_date, 5, 2) == "02" ? " selected" : ""); ?>>Feb</option>
					<option value="03"<?= (substr($db->settings->library_last_student_due_date, 5, 2) == "03" ? " selected" : ""); ?>>Mar</option>
					<option value="04"<?= (substr($db->settings->library_last_student_due_date, 5, 2) == "04" ? " selected" : ""); ?>>Apr</option>
					<option value="05"<?= (substr($db->settings->library_last_student_due_date, 5, 2) == "05" ? " selected" : ""); ?>>May</option>
					<option value="06"<?= (substr($db->settings->library_last_student_due_date, 5, 2) == "06" ? " selected" : ""); ?>>Jun</option>
					<option value="07"<?= (substr($db->settings->library_last_student_due_date, 5, 2) == "07" ? " selected" : ""); ?>>Jul</option>
					<option value="08"<?= (substr($db->settings->library_last_student_due_date, 5, 2) == "08" ? " selected" : ""); ?>>Aug</option>
					<option value="09"<?= (substr($db->settings->library_last_student_due_date, 5, 2) == "09" ? " selected" : ""); ?>>Sep</option>
					<option value="10"<?= (substr($db->settings->library_last_student_due_date, 5, 2) == "10" ? " selected" : ""); ?>>Oct</option>
					<option value="11"<?= (substr($db->settings->library_last_student_due_date, 5, 2) == "11" ? " selected" : ""); ?>>Nov</option>
					<option value="12"<?= (substr($db->settings->library_last_student_due_date, 5, 2) == "12" ? " selected" : ""); ?>>Dec</option>
				</select>
				<select id="library_last_student_due_date_day" name="library_last_student_due_date_day">
					<option value="01"<?= (substr($db->settings->library_last_student_due_date, 8, 2) == "01" ? " selected" : ""); ?>>1st</option>
					<option value="02"<?= (substr($db->settings->library_last_student_due_date, 8, 2) == "02" ? " selected" : ""); ?>>2nd</option>
					<option value="03"<?= (substr($db->settings->library_last_student_due_date, 8, 2) == "03" ? " selected" : ""); ?>>3rd</option>
					<option value="04"<?= (substr($db->settings->library_last_student_due_date, 8, 2) == "04" ? " selected" : ""); ?>>4th</option>
					<option value="05"<?= (substr($db->settings->library_last_student_due_date, 8, 2) == "05" ? " selected" : ""); ?>>5th</option>
					<option value="06"<?= (substr($db->settings->library_last_student_due_date, 8, 2) == "06" ? " selected" : ""); ?>>6th</option>
					<option value="07"<?= (substr($db->settings->library_last_student_due_date, 8, 2) == "07" ? " selected" : ""); ?>>7th</option>
					<option value="08"<?= (substr($db->settings->library_last_student_due_date, 8, 2) == "08" ? " selected" : ""); ?>>8th</option>
					<option value="09"<?= (substr($db->settings->library_last_student_due_date, 8, 2) == "09" ? " selected" : ""); ?>>9th</option>
					<option value="10"<?= (substr($db->settings->library_last_student_due_date, 8, 2) == "10" ? " selected" : ""); ?>>10th</option>
					<option value="11"<?= (substr($db->settings->library_last_student_due_date, 8, 2) == "11" ? " selected" : ""); ?>>11th</option>
					<option value="12"<?= (substr($db->settings->library_last_student_due_date, 8, 2) == "12" ? " selected" : ""); ?>>12th</option>
					<option value="13"<?= (substr($db->settings->library_last_student_due_date, 8, 2) == "13" ? " selected" : ""); ?>>13th</option>
					<option value="14"<?= (substr($db->settings->library_last_student_due_date, 8, 2) == "14" ? " selected" : ""); ?>>14th</option>
					<option value="15"<?= (substr($db->settings->library_last_student_due_date, 8, 2) == "15" ? " selected" : ""); ?>>15th</option>
					<option value="16"<?= (substr($db->settings->library_last_student_due_date, 8, 2) == "16" ? " selected" : ""); ?>>16th</option>
					<option value="17"<?= (substr($db->settings->library_last_student_due_date, 8, 2) == "17" ? " selected" : ""); ?>>17th</option>
					<option value="18"<?= (substr($db->settings->library_last_student_due_date, 8, 2) == "18" ? " selected" : ""); ?>>18th</option>
					<option value="19"<?= (substr($db->settings->library_last_student_due_date, 8, 2) == "19" ? " selected" : ""); ?>>19th</option>
					<option value="20"<?= (substr($db->settings->library_last_student_due_date, 8, 2) == "20" ? " selected" : ""); ?>>20th</option>
					<option value="21"<?= (substr($db->settings->library_last_student_due_date, 8, 2) == "21" ? " selected" : ""); ?>>21st</option>
					<option value="22"<?= (substr($db->settings->library_last_student_due_date, 8, 2) == "22" ? " selected" : ""); ?>>22nd</option>
					<option value="23"<?= (substr($db->settings->library_last_student_due_date, 8, 2) == "23" ? " selected" : ""); ?>>23rd</option>
					<option value="24"<?= (substr($db->settings->library_last_student_due_date, 8, 2) == "24" ? " selected" : ""); ?>>24th</option>
					<option value="25"<?= (substr($db->settings->library_last_student_due_date, 8, 2) == "25" ? " selected" : ""); ?>>25th</option>
					<option value="26"<?= (substr($db->settings->library_last_student_due_date, 8, 2) == "26" ? " selected" : ""); ?>>26th</option>
					<option value="27"<?= (substr($db->settings->library_last_student_due_date, 8, 2) == "27" ? " selected" : ""); ?>>27th</option>
					<option value="28"<?= (substr($db->settings->library_last_student_due_date, 8, 2) == "28" ? " selected" : ""); ?>>28th</option>
					<option value="29"<?= (substr($db->settings->library_last_student_due_date, 8, 2) == "29" ? " selected" : ""); ?>>29th</option>
					<option value="30"<?= (substr($db->settings->library_last_student_due_date, 8, 2) == "30" ? " selected" : ""); ?>>30th</option>
					<option value="31"<?= (substr($db->settings->library_last_student_due_date, 8, 2) == "31" ? " selected" : ""); ?>>31st</option>
				</select>
				<select id="library_last_student_due_date_year" name="library_last_student_due_date_year">
					<?
						$first_year = date("Y") - 2;
						$last_year = date("Y") + 3;

						for($a = $first_year; $a < $last_year; $a++)
						{
							?>
							<option value="<?= $a; ?>"<?= (substr($db->settings->library_last_student_due_date, 0, 4) == $a ? " selected" : ""); ?>><?= $a; ?></option>
							<?
						}; // end for
					?>
				</select>
			</td>
		</tr>
		<tr>
			<td></td>
			<td class="edit_value">
				<input type="submit" name="action" value="<?= $submit_caption; ?>">
				<input type="button" name="cancel" value="Cancel" onclick="window.close();">
			</td>
		</tr>
	</table>
</form>
<?

include_once(dirname(__FILE__) . "/bottom.php");

?>
<?

include_once(dirname(__FILE__) . "/first.php");

$barcode = urldecode($query[0]);
$value = urldecode($query[1]);



if($accounts->computer_record->library_kiosk == "Y")
{
	// check if exists
	// if exits then check if redeemed

	$award_sql = "
		SELECT
			*
		FROM
			`" . $db->table_library_award . "`
		WHERE
			`barcode`='" . mysql_escape_string($barcode) . "'
			AND `enabled`='Y'
	";
	$award_result = mysql_query($award_sql, $mysql->link);


	if($_SESSION['library_account_ID'])
	{
		if(mysql_num_rows($award_result) > 0)
		{
			$award_record = mysql_fetch_object($award_result);

			if($award_record->{$db->field_account_ID} > 0)
			{
				exit_error("Already Redeemed", "This award has already been redeemed by someone else.");
			}
			else
			{
				$db->home_reset = 10;

				$points_available = $kiosk_account_record->library_award_points - $kiosk_account_record->library_award_spent;

				if($points_available < $award_record->value)
				{
					exit_error("Not enough points.", "You do not have enough points to redeem that award.");
				}
				else
				{
					$points_left = $kiosk_account_record->library_award_points - $kiosk_account_record->library_award_spent - $award_record->value;
					$new_points_spent = $kiosk_account_record->library_award_spent + $award_record->value;

					$award_sql = "
						UPDATE
							`" . $db->table_library_award . "`
						SET
							`account_ID`='" . mysql_escape_string($kiosk_account_record->ID) . "'
						WHERE
							`barcode`='" . mysql_escape_string($barcode) . "'
					";
					//echo $award_sql;
					mysql_query($award_sql, $mysql->link);


					$account_sql = "
						UPDATE
							`" . $db->table_account . "`
						SET
							`library_award_spent`='" . $new_points_spent . "'
						WHERE
							`ID`='" . mysql_escape_string($kiosk_account_record->ID) . "'
					";
					//echo $account_sql;
					mysql_query($account_sql, $mysql->link);


					$db->kiosk_sound = $db->common_sound_url . "/award.mp3";

					include_once(dirname(__FILE__) . "/kiosk_top.php");

					?><div class="action_bar"><span class="action_heading">Reading Award: <?= $award_record->value; ?> Points</span></div><?
					?><div class="action_bar"><span class="action_status_success">AWARD REDEEMED</span></div><?
					?><div class="action_bar"><span class="action_heading">You have <b><?= $points_left; ?></b> point<?= ($points_left != 1 ? "s" : ""); ?> left.</span></div><?

					include_once(dirname(__FILE__) . "/kiosk_bottom.php");
					exit();
				}; // end if

			}; // end if

		}
		else
		{
			exit_error("Unknown Value", "Please see the librarian.");
		}; // end if
		exit("redeem");
	}
	else
	{
		if(mysql_num_rows($award_result) > 0)
		{
			$award_record = mysql_fetch_object($award_result);

			if($award_record->{$db->field_account_ID} > 0)
			{
				exit_error("Already Redeemed", "This award has already been redeemed.");
			}
			else
			{
				$db->home_reset = 10;

				include_once(dirname(__FILE__) . "/kiosk_top.php");

				?><div class="action_bar"><span class="action_heading">Reading Award</span></div><?
				?><div class="action_bar"><span class="action_status_success"><?= $award_record->value; ?> Points</span></div><?
				?><div class="action_bar"><span class="action_heading">Sign in to redeem award.</span></div><?

				include_once(dirname(__FILE__) . "/kiosk_bottom.php");
				exit();
			}; // end if
		}
		else
		{
			exit_error("Unknown Value", "Please see the librarian.");
		}; // end if
	}; // end if
}
else
{
	if(! $_SESSION['library_account_ID'])
	{
		exit_error("Not Signed In", "You must be signed into perform that action.");
	}; // end if

	if(! $db->is_librarian_account)
	{
		exit_error("Access Denied", "Only a librarian can perform that action.");
	}; // end if

	$award_sql = "
		SELECT
			*
		FROM
			`" . $db->table_library_award . "`
		WHERE
			`barcode`='" . mysql_escape_string($barcode) . "'
			AND `enabled`='Y'
	";
	$award_result = mysql_query($award_sql, $mysql->link);

	if(mysql_num_rows($award_result) > 0)
	{
		if($value != "")
		{
			$award_sql = "
				UPDATE
					`" . $db->table_library_award . "`
				SET
					`value`='" . mysql_escape_string($value) . "',
					`" . $db->field_account_ID . "`='0'
				WHERE
					`barcode`='" . mysql_escape_string($barcode) . "'
			";
			mysql_query($award_sql, $mysql->link);

			$db->settings->library_last_award_value = $value;
			save_db_settings();

			header("location:" . $db->url_root . "/index.php");
			exit();
		}; // end if

		$award_record = mysql_fetch_object($award_result);
		$preset = $award_record->value;
		$update = TRUE;
	}
	else
	{
		if($value != "")
		{
			$award_sql = "
				INSERT INTO
					`" . $db->table_library_award . "`
				SET
					`barcode`='" . mysql_escape_string($barcode) . "',
					`value`='" . mysql_escape_string($value) . "'
			";
			mysql_query($award_sql, $mysql->link);

			$db->settings->library_last_award_value = $value;
			save_db_settings();

			header("location:" . $db->url_root . "/index.php");
			exit();
		}; // end if

		$preset = $db->settings->library_last_award_value;
		$update = FALSE;
	}; // end if


	include_once(dirname(__FILE__) . "/kiosk_top.php");

	
	/*
	K-1: 20
	2-3: 40
	4: 60
	5-6: 80
	7-8: 100
	9-12: 120
	staff: 140
	*/

	?>
	<script language="javascript">
		function submit_award_value()
		{
			if(document.getElementById('value_input').value.length == <?= $db->barcode_length; ?>)
			{
				document.location.href = "<?= $db->url_root; ?>/search.php/query/" + document.getElementById('value_input').value;
			}
			else
			{
				document.location.href = "<?= $db->url_root; ?>/award_process.php/<?= $barcode; ?>/" + document.getElementById('value_input').value;
			}; // end if
		}; // end function
	</script>
	<div class="dialog_instructions"><?= ($update ? "Update" : "Enter"); ?> award value:</div>
	<div class="dialog_input"><input id="value_input" type="text" value="<?= $preset; ?>" size="3" onkeypress="if(event.keyCode==13){submit_award_value();};"></div>
	<script language="javascript">
		document.getElementById('value_input').focus();
		document.getElementById('value_input').select();
	</script>
	<?
	include_once(dirname(__FILE__) . "/kiosk_bottom.php");
}; // end if


?>
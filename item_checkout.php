<?

include_once(dirname(__FILE__) . "/first.php");


$checkout_type = urldecode($query[0]);
$item_barcode = urldecode($query[1]);
$as_account_ID = urldecode($query[2]);


if(! $kiosk_account_record->ID)
{
	exit_error("You are not signed in.", "Please sign in before attempting to check out an item");
}; // end if

if($as_account_ID > 0 && $db->is_librarian_account)
{
	$checkout_account_ID = $as_account_ID;
}
else
{
	$checkout_account_ID = $kiosk_account_record->ID;
}; // end if

if($kiosk_account_record->control_level <= $db->power_levels['faculty'])
{
	$checkout_days = $db->settings->library_checkout_length_faculty;
	$overdue_limit = $db->settings->library_overdue_limit_faculty;
}
else
{
	if($kiosk_account_record->control_level <= $db->power_levels['student'])
	{
		$checkout_days = $db->settings->library_checkout_length_student;
		$overdue_limit = $db->settings->library_overdue_limit_student;
	}
	else
	{
		$checkout_days = $db->settings->library_checkout_length_community;
		$overdue_limit = $db->settings->library_overdue_limit_community;
	}; // end if
}; // end if


if($db->item_overdue_count > $overdue_limit && $kiosk_account_record->library_allow_overdue_checkout < 1)
{
	exit_error("Overdue Limit", "You have too many overdue items.  You must return them before you can checkout more.");
}; // end if


$sql = "
	SELECT
		*
	FROM
		`" . $db->table_library_item . "`
	WHERE
		`barcode`='" . mysql_escape_string($item_barcode) . "'
		AND `enabled`='Y'
";
$result = mysql_query($sql, $mysql->link);
if(mysql_num_rows($result) < 1)
{
	exit_error("Item does not exist.", "The item you are attempting to checkout does not exist in the system. Please see the librarian for assistance.");
}; // end if

$item_record = mysql_fetch_object($result);

// Student checkout deny for end of semester or school year
if(date("Y-m-d") > $db->settings->library_last_student_checkout_date && $kiosk_account_record->student == "Y")
{
	exit_error("Denied", "You may not checkout items anymore this semester.");
}; // end if


if($item_record->{$db->field_library_series_ID} > 0)
{
	$series_sql = "
		SELECT
			*
		FROM
			`" . $db->table_library_series . "`
		WHERE
			`ID`='" . $item_record->{$db->field_library_series_ID} . "'
			AND `enabled`='Y'
	";
	$series_result = mysql_query($series_sql, $mysql->link);
	$series_record = mysql_fetch_object($series_result);
}; // end if

if($item_record->allow_checkout == "N" && $kiosk_account_record->control_level >= $db->power_levels['student'] && $kiosk_account_record->library_allow_overdue_checkout < 1)
{
	exit_error("Restricted Item", "This item cannot be checked out.");
}; // end if

$already_checked_out = FALSE;

if($item_record->{$db->field_checkout_ID} > 0)
{
	header("location:" . $db->url_root . "/item_return.php/checkout/" . $item_record->barcode);
	exit();
}; // end if



$due_date = date("Y-m-d 23:59:59", time() + (60 * 60 * 24 * $checkout_days));

// Student checkout duedate override for end of semester or school year
if($due_date > $db->settings->library_last_student_due_date . " 23:59:59" && $kiosk_account_record->student == "Y")
{
	$due_date = $db->settings->library_last_student_due_date . " 23:59:59";
}; // end if


if(! $already_checked_out)
{
	$checkout_sql = "
		INSERT INTO
			`" . $db->table_library_checkout . "`
		SET
			`" . $db->field_account_ID . "`='" . $checkout_account_ID . "',
			`" . $db->field_library_item_ID . "`='" . $item_record->ID . "',
			`out_datetime`='" . date("Y-m-d H:i:s") . "',
			`due_datetime`='" . $due_date . "'
	";
	mysql_query($checkout_sql, $mysql->link);
	$checkout_ID = mysql_insert_id($mysql->link);

	$sql = "
		UPDATE
			`" . $db->table_library_item . "`
		SET
			`" . $db->field_checkout_ID . "`='" . $checkout_ID . "'
		WHERE
			`barcode`='" . $item_barcode . "'
	";
	mysql_query($sql, $mysql->link);

	$sql = "
		UPDATE
			`" . $db->table_account . "`
		SET
			`library_allow_overdue_checkout`=0
		WHERE
			`ID`='" . $checkout_account_ID . "'
	";
	mysql_query($sql, $mysql->link);

}; // end if



switch($checkout_type)
{
	case "onbehalfof":
		header("location:" . $db->url_root . "/item_details.php/ID/" . $item_record->ID);
		exit();
		break;
	case "auto":
		$db->home_reset = 10;

		$db->kiosk_sound = $db->common_sound_url . "/success.mp3";

		include_once(dirname(__FILE__) . "/kiosk_top.php");
		
		if($item_record->{$db->field_library_series_ID} > 0)
		{
			?><div class="action_bar"><span class="action_heading"><?= $series_record->title; ?><?= ($item_record->series_number > 0 ? " - #" . $item_record->series_number : ""); ?></span></div><?
		}; // end if
		if($item_record->title != "")
		{
			?><div class="action_bar"><span class="action_heading"><?= $item_record->title; ?></span></div><?
		}; // end if
		?><div class="action_bar"><span class="action_status_success">CHECKED OUT</span></div><?
		?><div class="action_bar"><span class="action_message">Return by <?= sql_date_format("F jS", $due_date); ?></span></div><?

		?><div class="action_bar break"><span class="action_instructions">Scan next item.</span></div><?
		
		include_once(dirname(__FILE__) . "/kiosk_bottom.php");
		exit();
		break;
}; // end switch

?>
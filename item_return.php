<?

include_once(dirname(__FILE__) . "/first.php");


$checkout_type = urldecode($query[0]);
$item_barcode = urldecode($query[1]);

if($_SESSION['library_account_ID'] && $checkout_type != "checkout")
{
	exit_error("Already Signed In", "You cannot be signed in to return an item.");
}; // end if

$item_sql = "
	SELECT
		*
	FROM
		`" . $db->table_library_item . "`
	WHERE
		`barcode`='" . mysql_escape_string($item_barcode) . "'
		AND (`enabled`='Y' OR `lost_datetime`!='" . $db->blank_datetime . "')
";
$item_result = mysql_query($item_sql, $mysql->link);
if(mysql_num_rows($result) < 1)
{
	exit_error("Item does not exist.", "The item you are attempting to return does not exist in the system.");
}; // end if
$item_record = mysql_fetch_object($item_result);

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

$type_sql = "
	SELECT
		*
	FROM
		`" . $db->table_library_type . "`
	WHERE
		`ID`='" . $item_record->{$db->field_library_type_ID} . "'
";

$type_result = mysql_query($type_sql, $mysql->link);
$type_record = mysql_fetch_object($type_result);


$checkout_sql = "
	SELECT
		*
	FROM
		`" . $db->table_library_checkout . "`
	WHERE
		`" . $db->field_library_item_ID . "`='" . mysql_escape_string($item_record->ID) . "'
		AND `in_datetime`='" . $db->blank_datetime . "'
";
$checkout_result = mysql_query($checkout_sql, $mysql->link);

$awards_points_earned = 0;

if(mysql_num_rows($checkout_result) > 0)
{
	$checkout_record = mysql_fetch_object($checkout_result);

	$account_sql = "
		SELECT
			*
		FROM
			`" . $db->table_account . "`
		WHERE
			`ID`='" . mysql_escape_string($checkout_record->{$db->field_account_ID}) . "'
	";
	$account_result = mysql_query($account_sql, $mysql->link);
	$account_record = mysql_fetch_object($account_result);

	$sql = "
		UPDATE
			`" . $db->table_library_checkout . "`
		SET
			`in_datetime`='" . date("Y-m-d H:i:s") . "',
			`awards_applied`='Y'
		WHERE
			`ID`='" . $checkout_record->ID . "'
	";
	mysql_query($sql, $mysql->link);

	if($type_record->reading_awards == "Y")
	{
		if(strtotime($checkout_record->out_datetime) + (60 * 60 * $db->settings->library_reading_awards_minimum_hours) < time())
		{
			$awards_points_earned += $db->settings->library_reading_awards_return;
		}; // end if

		if($checkout_record->due_datetime < date("Y-m-d H:i:s"))
		{
			$awards_points_earned += $db->settings->library_reading_awards_overdue;
		}; // end if

		if($checkout_record->lost_datetime != $db->blank_datetime)
		{
			$awards_points_earned += $db->settings->library_reading_awards_paid;
		}; // end if
	}; // end if

	$sql = "
		UPDATE
			`" . $db->table_library_item . "`
		SET
			`" . $db->field_checkout_ID . "`='0',
			`lost_datetime`='" . $db->blank_datetime . "',
			`lost_account_ID`='0',
			`enabled`='Y'
		WHERE
			`ID`='" . $item_record->ID . "'
	";
	mysql_query($sql, $mysql->link);

	$updated_library_award_points = $account_record->library_award_points + $awards_points_earned;

	$sql = "
		UPDATE
			`" . $db->table_account . "`
		SET
			`library_award_points`='" . mysql_escape_string($updated_library_award_points) . "'
		WHERE
			`ID`='" . $account_record->ID . "'
	";
	mysql_query($sql, $mysql->link);
	$already_returned = FALSE;
}
else
{
	$already_returned = TRUE;
}; // end if

switch($checkout_type)
{
	case "checkout":
		header("location:" . $db->url_root . "/item_checkout.php/auto/" . $item_record->barcode);
		exit();
		break;
	case "return":
		$db->home_reset = 5;

		$location_record = lookup_designation($db->table_location, $item_record->{$db->field_location_ID});
		$category_record = lookup_designation($db->table_category, $item_record->{$db->field_category_ID});
		$call_number = implode(" &nbsp;", explode(",", $item_record->call_number));

		if($location_record->ID == $db->ID_location_main)
		{
			$db->kiosk_sound = $db->common_sound_url . "/double.mp3";
		}
		else
		{
			$db->kiosk_sound = $db->common_sound_url . "/heartput.mp3";
		}; // end if

		include_once(dirname(__FILE__) . "/kiosk_top.php");

		if($item_record->{$db->field_library_series_ID} > 0)
		{
			?><div class="action_bar"><span class="action_heading"><?= $series_record->title; ?><?= ($record->series_number > 0 ? " - #" . $item_record->series_number : ""); ?></span></div><?
		}; // end if
		if($item_record->title != "")
		{
			?><div class="action_bar"><span class="action_heading"><?= $item_record->title; ?></span></div><?
		}; // end if
		?><div class="action_bar"><span class="kiosk_key_message kiosk_blue_message">ITEM RETURNED</span></div><?

		if(! $already_returned)
		{
			?><div class="action_bar"><span class="action_instructions">Thank you <?= $account_record->name; ?>!</span></div><?
		}; // end if
		if($location_record->ID != $db->ID_location_main)
		{
			?><div class="action_bar"><span class="action_instructions"><?= $category_record->title; ?></span></div><?
			?><div class="action_bar"><span class="action_instructions"><?= $location_record->title; ?></span></div><?
		}; // end if

		//? ><div class="action_bar"><br><br>(< ?= $awards_points_earned; ? >)</div>< ?

		include_once(dirname(__FILE__) . "/kiosk_bottom.php");
		exit();

		break;
	default:
		exit_error("Unknown Return Method", "The method for returning you are using is not recognized.");
		break;
}; // end switch

?>
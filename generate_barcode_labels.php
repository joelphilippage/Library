<?

include_once(dirname(__FILE__) . "/first.php");

$barcode_category = urldecode($query[0]);
$starting_number = urldecode($query[1]);
$pages = (isset($query[2]) ? urldecode($query[2]) : 1);
$option = urldecode($query[3]);

if($barcode_category != "action")
{
	if(! $accounts->account_record->ID)
	{
		exit_error("Not Signed In", "You must be signed into perform that action.");
	}; // end if

	if($accounts->account_record->librarian != "Y")
	{
		exit_error("Access Denied", "Only a librarian can perform that action.");
	}; // end if
}; // end if



switch($barcode_category)
{
	case "cards":
		$group_sql = "
			SELECT
				`" . $db->table_account . "`.`name` AS 'name',
				`" . $db->table_account . "`.`library_barcode` AS 'library_barcode'
			FROM
				`" . $db->table_group_member . "`,
				`" . $db->table_account . "`
			WHERE
				`" . $db->table_group_member . "`.`" . $db->field_account_ID . "`=`" . $db->table_account . "`.`ID`
				AND `" . $db->table_group_member . "`.`" . $db->field_group_ID . "`='" . mysql_escape_string($starting_number) . "'
				AND `" . $db->table_group_member . "`.`enabled`='Y'
				AND `" . $db->table_account . "`.`enabled`='Y'
				AND `" . $db->table_account . "`.`library_barcode`!=''
				AND `" . $db->table_account . "`.`library_barcode`!='0'
			ORDER BY
				`" . $db->table_account . "`.`name`
		";
		$group_result = mysql_query($group_sql, $mysql->link);
		if(mysql_num_rows($group_result) < 1)
		{
			exit_error("No Active Library Cards", "There are not active library cards in the group you chose.");
		}
		else
		{
			$pages = ceil(mysql_num_rows($group_result) / 30);
		}; // end if
	case "account":
		$prefix = $db->barcode_account_prefix;
		break;
	case "action":
		$prefix = $db->barcode_action_prefix;
		$starting_number = -2;
		break;
	case "award":
		$prefix = $db->barcode_award_prefix;
		break;
	case "item":
		$prefix = $db->barcode_item_prefix;
		break;
	default:
		exit("unknown type");
		break;
}; // end switch

$current_number = $starting_number;

require_once($db->include_dir . "/fonts/times.php");
include_once($db->include_dir . "/pdf_maker.php");

$xo_reset = 0.799;
$yo_reset = 0.344;

$xo = $xo_reset;
$yo = $yo_reset;
$bw = 2.25;
$bh = 0.403;

$cw = 2.781;
$ch = 1;

$pm->set_font("times");




for($bp_a = 0; $bp_a < $pages; $bp_a++)
{
	set_time_limit(30);

	if($bp_a > 0)
	{
		$pm->new_page();
		$xo = $xo_reset;
		$yo = $yo_reset;
	}; // end if
	for($bc_a = 0; $bc_a < 3; $bc_a++)
	{
		for($br_a = 0; $br_a < 10; $br_a++)
		{
			switch($barcode_category)
			{
				case "cards":
					if($group_record = mysql_fetch_object($group_result))
					{
						$new_barcode = $group_record->library_barcode;
					}
					else
					{
						$new_barcode = $db->barcode_unknown_action;
					}; // end if
					break;
				case "action":
					switch($current_number)
					{
						case -2:
							$new_barcode = "10000000";
							break;
						case -1:
							$new_barcode = "10000001";
							break;
						default:
							$new_barcode = $prefix . str_pad($db->action_keys[$current_number], 7, "0", STR_PAD_LEFT);
							break;
					}; // end switch
					break;
				default:
					$new_barcode = $prefix . str_pad($current_number, 7, "0", STR_PAD_LEFT);
					break;
			}; // end switch

			$barcode_type = "AB";
			$barcode_code = $new_barcode;
			$barcode_height = "60";
			$barcode_command = "save";
			include($db->common_image_dir . "/barcode.php");

			switch($barcode_category)
			{
				case "cards":
					$text = $group_record->name;
					break;
				case "action":
					switch($current_number)
					{
						case -2:
							$text = "Sign Out";
							break;
						case -1:
							$text = "Librarian Library Card";
							break;
						default:
							$text = $db->action_codes[$new_barcode]->title;
							break;
					}; // end switch
					break;
				case "award":
					$text = "IM Library Awards Program";
					break;
				default:
					$text = "Immanuel Mission Library";
					break;
			}; // end switch

			if($new_barcode != $db->barcode_unknown_action)
			{
				$pm->text($xo  - 0.25, $yo + ($bw/2), 0.15, $text, "center");
				//$pm->image_jpeg($db->common_image_dir . "/temp/" . $new_barcode . ".jpg", $xo, $yo, $bw, $bh);
				$pm->image_jpeg($barcode_filename, $xo, $yo, $bw, $bh);
				unlink($barcode_filename);
				$pm->text($xo + $bh + 0.02, $yo + ($bw/2), 0.15, "" . trim(preg_replace("/(.)/", "\\1     ", $new_barcode)) . "", "center");
			}; // end if

			$xo+=$ch;

			if($br_a % 2 || $barcode_category == "account" || $barcode_category == "action" || $barcode_category == "cards" || $barcode_category == "award" || $option == "single")
			{
				$current_number++;
			}; // end if
		}; // end for
		$xo = $xo_reset;
		$yo+=$cw;
	}; // end for
}; // end for

switch($barcode_category)
{
	case "award":
	case "account":
	case "item":
		if($current_number > $db->settings->{"library_" . $barcode_category . "_barcode"})
		{
			$db->settings->{"library_" . $barcode_category . "_barcode"} = $current_number;
			save_db_settings();
		}; // end if
		break;
	default:
		break;
}; // end switch

$pm->stream(date("YmdHis") . ".pdf", TRUE);

?>
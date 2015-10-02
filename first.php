<?

/*
need to subtract points when items is marked as lost (reverse if UNlost)
need to add points when items is marked as paid (reverse if UNpaid)

award points cannot be redeemed if there are any overdue or lost items or any inpaid fines

*/

include_once(dirname(__FILE__) . "/../common/first.php");
$db->current_application = "library";



$db->isbndb_access_key = "OQ8CFYIK";

$db->isbn_length = 10;
$db->barcode_length = 8;
$db->pin_length = 4;
$db->activity_timeout = 500;
$db->home_reset = 0;
$db->lookup_iframe = FALSE;

$db->table_prefix = "library_";

$db->table_category = $db->table_prefix . "category";
$db->table_circulation = $db->table_prefix . "circulation";
$db->table_style = $db->table_prefix . "style";
$db->table_age = $db->table_prefix . "age";
$db->table_location = $db->table_prefix . "location";
$db->table_publisher = $db->table_prefix . "publisher";
$db->table_author = $db->table_prefix . "author";
$db->table_series = $db->table_prefix . "series";
$db->table_subject = $db->table_prefix . "subject";
$db->table_library_item_link = $db->table_prefix . "item_link";
$db->table_library_image = $db->table_prefix . "image";
$db->table_library_item_link_type = $db->table_prefix . "item_link_type";

$db->field_old_item_ID = "old_item_ID";
$db->field_account_ID = "account_ID";
$db->field_updated_account_ID = "updated_account_ID";
$db->field_deleted_account_ID = "deleted_account_ID";
$db->field_lost_account_ID = "lost_account_ID";
$db->field_category_ID = "category_ID";
$db->field_circulation_ID = "circulation_ID";
$db->field_style_ID = "style_ID";
$db->field_age_ID = "age_ID";
$db->field_location_ID = "location_ID";
$db->field_publisher_ID = "publisher_ID";
$db->field_author_ID = "author_ID";
$db->field_subject_ID = "subject_ID";
$db->field_image_ID = "image_ID";
$db->field_checkout_ID = "checkout_ID";
$db->field_record_ID = "record_ID";
$db->field_library_item_link_type_ID = "item_link_type_ID";


$db->old_ID_upper_limit = 9999;
$db->barcode_account_prefix = 1;
$db->barcode_action_prefix = 2;
$db->barcode_item_prefix = 3;
$db->barcode_award_prefix = 7;
$db->barcode_scan_prefix = 9;

$db->action_codes = array();
$db->action_keys = array();



//////////////////////////////////////////////////////////////////////////////////////////////////////
// GET LIST OF LINK TYPES (authors/subjects)
// eventually make this a saved db setting that is only updated when author types are edited

$item_link_type_sql = "
	SELECT
		`ID`,
		`table`
	FROM
		`" . $db->table_library_item_link_type . "`
";
$item_link_type_result = mysql_query($item_link_type_sql, $mysql->link);
$db->all_type_IDs = array();
$db->author_type_IDs = array();
$db->subject_type_IDs = array();
if(mysql_num_rows($item_link_type_result) > 0)
{
	while($item_link_type_record = mysql_fetch_object($item_link_type_result))
	{
		switch($item_link_type_record->table)
		{
			case "author":
				$db->all_type_IDs[] = $item_link_type_record->ID;
				$db->author_type_IDs[] = $item_link_type_record->ID;
				break;
			case "subject":
				$db->all_type_IDs[] = $item_link_type_record->ID;
				$db->subject_type_IDs[] = $item_link_type_record->ID;
				$db->ID_item_link_type_subject = $item_link_type_record->ID;
				break;
		}; // end switch
	}; // end while
}; // end if

//////////////////////////////////////////////////////////////////////////////////////////////////////

function add_action($barcode, $variable, $title, $category)
{
	global $db;
	$db->{$variable} = intval($barcode);
	$db->action_codes[$db->{$variable}] = (object) NULL;
	$db->action_codes[$db->{$variable}]->category = $category;
	$db->action_codes[$db->{$variable}]->title = $title;
	$action_key = intval(substr($barcode, -7));
	if($action_key > 0)
	{
		$db->action_keys[] = intval(substr($barcode, -7));
	}; // end if
}; // end function

add_action("20000000", "barcode_unknown_action", "Unkown Action", "None");
add_action("20000001", "barcode_activate_library_card", "Activate Library Card", "Librarian");
add_action("20000002", "barcode_reset_pin_number", "Reset PIN", "Librarian");
add_action("20000003", "barcode_set_shelving_category", "Set Shelving", "Librarian");
add_action("20000004", "barcode_queue_call_number", "Queue Call Number Label", "Librarian");
add_action("20000005", "barcode_assign_award_value", "Assign Award Value", "Librarian");


if($accounts->computer_record->barcode_reader == "Y")
{
	$db->entry_mode_caption_present_tense = "scan";
	$db->entry_mode_caption_future_tense = "scan";
	$db->entry_mode_caption_past_tense = "scanned";
}
else
{
	$db->entry_mode_caption_present_tense = "enter";
	$db->entry_mode_caption_future_tense = "enter";
	$db->entry_mode_caption_past_tense = "entered";
}; // end if

$db->url_root = "/library";
$db->image_url = $db->url_root . "/images";
$db->cover_image_url = $db->image_url . "/cover";
$db->cover_thumbs_image_url = $db->cover_image_url . "/thumbs";
$db->scan_image_url = $db->image_url . "/scan";
$db->import_image_url = $db->image_url . "/import";

$db->root_dir = dirname(__FILE__);
$db->image_dir = $db->root_dir . "/images";
$db->cover_image_dir = $db->image_dir . "/cover";
$db->cover_thumbs_image_dir = $db->cover_image_dir . "/thumbs";
$db->scan_image_dir = $db->image_dir . "/scan";
$db->import_image_dir = $db->image_dir . "/import";
$db->include_dir = $db->root_dir . "/include";


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$db->category_defaults = array();
$sql = "
	SELECT
		*
	FROM
		`" . $db->table_category . "`
	WHERE
		`enabled`='Y'
";
$result = mysql_query($sql, $mysql->link);
if(mysql_num_rows($result) > 0)
{
	while($record = mysql_fetch_object($result))
	{
		$db->category_defaults[$record->ID] = $record;
	}; // end while
}; // end if



$db->show_search = TRUE;


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// LOAD KIOSK ACCOUNT

$kiosk_wait_mode = FALSE;
$kiosk_signed_in = FALSE;
if($_SESSION['library_account_ID'] > 0)
{
	$kiosk_account_sql = "
		SELECT
			*
		FROM
			`" . $db->table_account . "`
		WHERE
			`ID`='" . mysql_escape_string($_SESSION['library_account_ID']) . "'
			AND `enabled`='Y'
	";
	$kiosk_account_result = mysql_query($kiosk_account_sql, $mysql->link);
	if(mysql_num_rows($kiosk_account_result) > 0)
	{
		$kiosk_account_record = mysql_fetch_object($kiosk_account_result);
		$kiosk_signed_in = TRUE;
	}; // end if
}; // end if
$kiosk_general_mode = "general";
$kiosk_mode = $kiosk_general_mode;
$kiosk_value = "";

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// DETERMINE CHECKED OUT COUNT

$checkout_sql = "
	SELECT
		`" . $db->table_library_checkout . "`.`ID`,
		`" . $db->table_library_type . "`.`checkout_type` AS 'checkout_type'
	FROM
		`" . $db->table_library_checkout . "`,
		`" . $db->table_library_item . "`,
		`" . $db->table_library_type . "`
	WHERE
		`" . $db->table_library_checkout . "`.`" . $db->field_library_item_ID . "`=`" . $db->table_library_item . "`.`ID`
		AND `" . $db->table_library_item . "`.`" . $db->field_library_type_ID . "`=`" . $db->table_library_type . "`.`ID`
		AND `" . $db->field_account_ID . "`='" . $kiosk_account_record->ID . "'
		AND `in_datetime`='" . $db->blank_datetime . "'
		AND `paid_datetime`='" . $db->blank_datetime . "'
";
$checkout_result = mysql_query($checkout_sql, $mysql->link);
$db->item_checked_out_count = 0;
$db->item_checked_out_count_read = 0;
$db->item_checked_out_count_listen = 0;
$db->item_checked_out_count_watch = 0;
$db->item_checked_out_count_use = 0;

if(mysql_num_rows($checkout_result) > 0)
{
	while($checkout_record = mysql_fetch_object($checkout_result))
	{
		$db->item_checked_out_count++;
		$db->{"item_checked_out_count_" . $checkout_record->checkout_type}++;
	}; // end while
}; // end if

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// DETERMINE OVERDUE ITEM COUNT

$overdue_checkout_sql = "
	SELECT
		*
	FROM
		`" . $db->table_library_checkout . "`
	WHERE
		`" . $db->field_account_ID . "`='" . $kiosk_account_record->ID . "'
		AND `in_datetime`='" . $db->blank_datetime . "'
		AND `paid_datetime`='" . $db->blank_datetime . "'
		AND `due_datetime`<'" . date("Y-m-d H:i:s") . "'
";
$overdue_checkout_result = mysql_query($overdue_checkout_sql, $mysql->link);
$db->item_overdue_count = mysql_num_rows($overdue_checkout_result);

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/*
barcode
missing
damaged
incomplete
removed
removed_datetime

account_ID
item_ID
borrowed_datetime
due_datetime
returned_datetime
returned_incomplete
returned_damaged
notes


*/







function lookup_designation($table, $item_ID = -1, $where = "")
{
	global $mysql, $db;


	$sql = "
		SELECT
			*
		FROM
			`" . $table . "`
		WHERE
			" . ($where != "" ? $where . " AND " : "") . "
			`enabled`='Y'
	";
	if($item_ID > -1)
	{
		$sql .= "
				AND `ID`='" . $item_ID . "'
		";
	}
	else
	{
		$sql .= "
			ORDER BY
				`title`
		";
		$record = array();
	}; // end if
	$result = mysql_query($sql, $mysql->link);
	if(mysql_num_rows($result) > 0)
	{
		if($item_ID > 0)
		{
			$record = mysql_fetch_object($result);
		}
		else
		{
			while($type_record = mysql_fetch_object($result))
			{
				$record[] = $type_record;
			}; // end while
		}; // end if
	}
	else
	{
		$record = FALSE;
	}; // end if
	return $record;
}; // end function


function lookup_author($item_ID, $compilation = FALSE, $include_deleted = FALSE)
{
	global $mysql, $db;

	$authors = array();

	$sql = "
		SELECT
			`" . $db->table_author . "`.`ID` AS 'ID',
			`" . $db->table_author . "`.`first_name` AS 'first_name',
			`" . $db->table_author . "`.`middle_name` AS 'middle_name',
			`" . $db->table_author . "`.`last_name` AS 'last_name',
			`" . $db->table_library_item_link . "`.`section_title` AS 'section_title',
			`" . $db->table_library_item_link . "`.`section_start` AS 'section_start',
			`" . $db->table_library_item_link_type . "`.`ID` AS 'type',
			`" . $db->table_library_item_link_type . "`.`by_text` AS 'by_text'
		FROM
			`" . $db->table_library_item_link . "`,
			`" . $db->table_library_item_link_type . "`,
			`" . $db->table_author . "`
		WHERE
			`" . $db->table_library_item_link . "`.`" . $db->field_library_item_ID . "`='" . $item_ID . "'
			AND `" . $db->table_library_item_link . "`.`record_ID`=`" . $db->table_author . "`.`ID`
			AND `" . $db->table_library_item_link . "`.`" . $db->field_library_item_link_type_ID . "`=`" . $db->table_library_item_link_type . "`.`ID`
			AND `" . $db->table_library_item_link_type . "`.`table`='author'
			AND `" . $db->table_library_item_link . "`.`section_title`" . ($compilation ? "!=" : "=") . "''
			" . ($include_deleted ? "" : "AND `" . $db->table_library_item_link . "`.`enabled`='Y'") . "
		ORDER BY
			`priority` ASC
	";
	//echo $sql;
	$result = mysql_query($sql, $mysql->link);
	while($record = mysql_fetch_object($result))
	{
		$authors[] = $record;
	}; // end while
	return $authors;
}; // end function

function lookup_group($group_ID, $field_name)
{
	global $mysql, $db;

	$records = array();

	$sql = "
		SELECT
			*
		FROM
			`" . $db->table_library_item . "`
		WHERE
			`" . mysql_escape_string($field_name) . "`='" . mysql_escape_string($group_ID) . "'
			AND `enabled`='Y'
	";
	$result = mysql_query($sql, $mysql->link);
	while($record = mysql_fetch_object($result))
	{
		$records[] = $record;
	}; // end while
	return $records;
}; // end function

function lookup_subject($item_ID, $type = "S", $include_deleted = FALSE)
{
	global $mysql, $db;

	$subjects = array();

	$sql = "
		SELECT
			`" . $db->table_subject . "`.`ID` AS 'ID',
			`" . $db->table_subject . "`.`title` AS 'title'
		FROM
			`" . $db->table_library_item_link . "`,
			`" . $db->table_library_item_link_type . "`,
			`" . $db->table_subject . "`
		WHERE
			`" . $db->table_library_item_link . "`.`" . $db->field_library_item_ID . "`='" . $item_ID . "'
			AND `" . $db->table_library_item_link . "`.`record_ID`=`" . $db->table_subject . "`.`ID`
			AND `" . $db->table_library_item_link . "`.`" . $db->field_library_item_link_type_ID . "`=`" . $db->table_library_item_link_type . "`.`ID`
			AND `" . $db->table_library_item_link_type . "`.`table`='subject'
			" . ($include_deleted ? "" : "AND `" . $db->table_library_item_link . "`.`enabled`='Y'") . "
		ORDER BY
			`priority` ASC
	";
	$result = mysql_query($sql, $mysql->link);
	while($record = mysql_fetch_object($result))
	{
		$subjects[] = $record;
	}; // end while
	return $subjects;
}; // end function

function lookup_account($ID)
{
	global $mysql, $db;

	$sql = "
		SELECT
			*
		FROM
			`" . $db->table_account . "`
		WHERE
			`ID`='" . $ID . "'
	";
	$result = mysql_query($sql, $mysql->link);
	if(mysql_num_rows($result) > 0)
	{
		$record = mysql_fetch_object($result);
		return $record;
	}
	else
	{
		return FALSE;
	}; // end if
}; // end function


function build_author_name($author_record, $last_first = TRUE, $search_results = FALSE)
{
	$field_name_prefix = ($search_results ? "author_" : "");

	if($last_first)
	{
		return
			($author_record->{$field_name_prefix . "last_name"} != "" ? "" . $author_record->{$field_name_prefix . "last_name"} . (strlen($author_record->{$field_name_prefix . "last_name"}) < 2 ? "." : "") : "") .
			($author_record->{$field_name_prefix . "first_name"} != "" ? ", " . $author_record->{$field_name_prefix . "first_name"} . (strlen($author_record->{$field_name_prefix . "first_name"}) < 2 ? "." : "") : "") .
			($author_record->{$field_name_prefix . "middle_name"} != "" ? " " . $author_record->{$field_name_prefix . "middle_name"} . (strlen($author_record->{$field_name_prefix . "middle_name"}) < 2 ? "." : "") : "");
	}
	else
	{
		return
			($author_record->{$field_name_prefix . "first_name"} != "" ? $author_record->{$field_name_prefix . "first_name"} . (strlen($author_record->{$field_name_prefix . "first_name"}) < 2 ? "." : "") : "") .
			($author_record->{$field_name_prefix . "middle_name"} != "" ? " " . $author_record->{$field_name_prefix . "middle_name"} . (strlen($author_record->{$field_name_prefix . "middle_name"}) < 2 ? "." : "") : "") .
			($author_record->{$field_name_prefix . "last_name"} != "" ? " " . $author_record->{$field_name_prefix . "last_name"} . (strlen($author_record->{$field_name_prefix . "last_name"}) < 2 ? "." : "") : "");
	}; // end if
}; // end function


function exit_error($title, $description)
{
	global $db, $mysql, $accounts;

	$db->home_reset = 7;

	$db->kiosk_sound = $db->common_sound_url . "/failure.mp3";

	include_once(dirname(__FILE__) . "/kiosk_top.php");
	?>
	<center>
	<h1 class="error"><?= $title; ?></h1>
	<h3 class="error"><?= $description; ?></h3>
	</center>
	<?
	include_once(dirname(__FILE__) . "/kiosk_bottom.php");
	exit();
}; // end function

function format_title($title)
{
	return ucfirst(preg_replace(array('/\band\b/i', '/\bthe\b/i', '/\bin\b/i', '/\bis\b/i', '/\ban\b/i', '/\bor\b/i', '/\bat\b/i', '/\bof\b/i', '/\ba\b/i'), array('and', 'the', 'in', 'is', 'an', 'or', 'at', 'of', 'a'), ucwords($title)));
}; // end function

function is_valid_isbn_number($value)
{
	if((strlen($value) == 10 || strlen($value) == 13) && strlen(preg_replace("/\d/", "", $value)) < 3)
	{
		return TRUE;
	}
	else
	{
		return FALSE;
	}; // end if
}; // end function

function output_compilation_titles_and_authors($record, $output_type = "detail", $show_pages = TRUE, $search_words = array())
{
	global $db;

	$author_list = array();
	$main_author_records = lookup_author($record->ID, FALSE, TRUE);
	//$hide_main_author_ID = (count($main_author_records) == 1 ? $main_author_records[0]->ID : 0);
	$hide_main_author_ID = $main_author_records[0]->ID;
	
	if(count($author_records = lookup_author($record->ID, TRUE, TRUE)))
	{
		$last_section_title = "";
		$last_section_start = -1;
		$last_author_type = 0;
		$hold_section_start = -1;
		$output_title = FALSE;
		$delayed_author_html = "";
		foreach($author_records as $author_record)
		{
			$this_section_title = $author_record->section_title;
			$this_section_start = $author_record->section_start;
			$this_by_text = $author_record->by_text;
			if($hold_section_start == -1)
			{
				$last_section_title = $this_section_title;
				$hold_section_title = $this_section_title;
				$hold_section_start = $this_section_start;
			}; // end if

			$this_author_type = $author_record->type;

			if($output_title)
			{
				$output_pages = $last_section_start - $hold_section_start;
				?>
				<div class="<?= $output_type; ?>_compilation_title"><?= highlight_search_query($output_title_text, $search_words); ?><?
					if($show_pages && $author_record->section_start > 0)
					{
						?> <span class="<?= $output_type; ?>_compilation_pages">(<?= ($output_pages == 0 ? "1" : $output_pages); ?> page<?= ($output_pages > 1 ? "s" : ""); ?>)</span><?
					}; // end if
				?></div>
				<?

				if($delayed_author_html != "")
				{
					echo $delayed_author_html;
					$delayed_author_html = "";
				}; // end if
				$output_title = FALSE;
				$hold_section_start = $last_section_start;
			}; // end if

			if($this_section_title != $last_section_title)
			{
				if(count($author_list) > 0)
				{
					$delayed_author_html .= '<div class="' . $output_type . '_compilation_author">' . $last_by_text . ' ' . implode("; ", $author_list) . '</div>';
				}; // end if
				$author_list = array();
				if($author_record->ID != $hide_main_author_ID)
				{
					$author_list[] = '<a href="' . $db->url_root . '/search.php/author/' . $author_record->ID . '">' . highlight_search_query(build_author_name($author_record), $search_words) . '</a>';
				}; // end if
				$output_title_text = $last_section_title;
				$output_title = TRUE;
			}
			else
			{

				if(($this_author_type != $last_author_type && $last_author_type != 0))
				{
					if(count($author_list) > 0)
					{
						$delayed_author_html .= '<div class="' . $output_type . '_compilation_author">' . $last_by_text . ' ' . implode("; ", $author_list) . '</div>';
					}; // end if
					$author_list = array();
				}; // end if
				if($author_record->ID != $hide_main_author_ID)
				{
					$author_list[] = '<a href="' . $db->url_root . '/search.php/author/' . $author_record->ID . '">' . highlight_search_query(build_author_name($author_record), $search_words) . '</a>';
				}; // end if
			}; // end if

			$last_section_start = $this_section_start;
			$last_section_title = $this_section_title;
			$last_by_text = $this_by_text;
			$last_author_type = $this_author_type;
		}; // end foreach

		if($output_title)
		{
			$output_pages = $last_section_start - $hold_section_start;
			?>
			<div class="<?= $output_type; ?>_compilation_title"><?= highlight_search_query($output_title_text, $search_words); ?><?
				if($show_pages && $author_record->section_start > 0)
				{
					?> <span class="<?= $output_type; ?>_compilation_pages">(<?= ($output_pages == 0 ? "1" : $output_pages); ?> page<?= ($output_pages > 1 ? "s" : ""); ?>)</span><?
				}; // end if
			?></div>
			<?

			if($delayed_author_html != "")
			{
				echo $delayed_author_html;
				$delayed_author_html = "";
			}; // end if
			$output_title = FALSE;
			$hold_section_start = $last_section_start;
		}; // end if

		$output_pages = $record->length - $last_section_start + 1;
		?>
		<div class="<?= $output_type; ?>_compilation_title"><?= highlight_search_query($this_section_title, $search_words); ?><?
			if($show_pages && $author_record->section_start > 0)
			{
				?> <span class="<?= $output_type; ?>_compilation_pages">(<?= ($output_pages == 0 ? "1" : $output_pages); ?> page<?= ($output_pages > 1 ? "s" : ""); ?>)</span><?
			}; // end if
		?></div>
		<?
		if(count($author_list) > 0)
		{
			if($delayed_author_html != "")
			{
				echo $delayed_author_html;
			}; // end if
			?>
			<div class="<?= $output_type; ?>_compilation_author"><?= $author_record->by_text; ?> <?= implode("; ", $author_list); ?></div>
			<?
		}; // end if
	}; // end if
}; // end function

function highlight_search_query($text, $search_words)
{
	if(count($search_words) > 0)
	{
		$out_text = $text;

		foreach($search_words as $word)
		{
			$out_text = preg_replace("/(" . preg_quote($word) . ")/i", '@!@\\1@&@', $out_text);
		}; // end foreach
		$out_text = preg_replace("/@!@/i", '<span class="search_highlight">', $out_text);
		$out_text = preg_replace("/@&@/i", '</span>', $out_text);
		return $out_text;
	}
	else
	{
		return $text;
	}; // end if
}; // end function

function reduce($input_pathname, $output_pathname, $max_width, $max_height, $compression = 75)  //  Simple.  Original.  Elegant.  By flash services.
{
	$im = imagecreatefromjpeg($input_pathname);
	$im_width = imagesx($im);
	$im_height = imagesy($im);

	if($im_width > $max_width || $im_height > $max_height)
	{
		if($im_width >= $im_height)
		{
			$new_width = $max_width;
			$new_height = ceil($new_width * ($im_height / $im_width));
			if($new_height >= $max_height)
			{
				$new_height = $max_height;
				$new_width = ceil($new_height * ($im_width / $im_height));
			}; // end if
		}
		else
		{
			$new_height = $max_height;
			$new_width = ceil($new_height * ($im_width / $im_height));
			if($new_width >= $max_width)
			{
				$new_width = $max_width;
				$new_height = ceil($new_width * ($im_height / $im_width));
			}; // end if
		}; // end if
	}
	else
	{
		$new_height = $im_height;
		$new_width = $im_width;
	}; // end if

	$new_image = imagecreatetruecolor($new_width, $new_height);
	imagecopyresampled($new_image, $im, 0, 0, 0, 0, $new_width + 1, $new_height + 1, $im_width, $im_height);
	imagejpeg($new_image, $output_pathname, $compression);
	imagedestroy($new_image);

} // end method

?>
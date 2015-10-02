<?

include_once(dirname(__FILE__) . "/first.php");


$starting_number = 1;

if(! $accounts->account_record->ID)
{
	exit_error("Not Signed In", "You must be signed into perform that action.");
}; // end if

if($accounts->account_record->librarian != "Y")
{
	exit_error("Access Denied", "Only a librarian can perform that action.");
}; // end if


require_once($db->include_dir . "/fonts/times.php");
include_once($db->include_dir . "/pdf_maker.php");

$bw = 1.5;
$bh = 1;

$pm->set_font("times");

for($current_number = -1; $current_number < count($db->action_keys); $current_number++)
{
	switch($current_number)
	{
		case -2:
			$new_barcode = "10000000";
			$text = "Sign Out";
			break;
		case -1:
			$new_barcode = "10000001";
			$text = "Librarian Sign-In";
			break;
		default:
			$new_barcode = $db->barcode_action_prefix . str_pad($db->action_keys[$current_number], 7, "0", STR_PAD_LEFT);
			$text = $db->action_codes[$new_barcode]->title;
			break;
	}; // end switch

	$barcode_type = "AB";
	$barcode_code = $new_barcode;
	$barcode_height = "60";
	$barcode_command = "save";
	include($db->common_image_dir . "/barcode.php");

	if($current_number % 2)
	{
		$xo = 0.799;
		$yo = 0.344;
	}
	else
	{
		$xo = 0.799;
		$yo = 0.344 + 4.25;
	}; // end if

	if($new_barcode != $db->barcode_unknown_action)
	{
		$pm->text($xo  + 9.5, $yo, 0.3, $text, "left");
		$pm->image_jpeg($barcode_filename, $xo, $yo, $bw, $bh);
		unlink($barcode_filename);
		//$pm->text($xo + $bh + 0.02, $yo + ($bw/2), 0.15, "" . trim(preg_replace("/(.)/", "\\1     ", $new_barcode)) . "", "center");
	}; // end if
	$xo+=$ch;

	if(! ($current_number % 2) && $current_number < count($db->action_keys) - 1)
	{
		$pm->new_page();
	}; // end if

}; // end for



$pm->stream();

?>
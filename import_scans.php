<?

include_once(dirname(__FILE__) . "/first.php");

if(! $_SESSION['library_account_ID'])
{
	exit_error("Not Signed In", "You must be signed into perform that action.");
}; // end if

if(! $db->is_librarian_account && FALSE)
{
	exit_error("Access Denied", "Only a librarian can perform that action.");
}; // end if




$scan_dir_handle = opendir($db->scan_image_dir);
$found = FALSE;
while($dir_entry = readdir($scan_dir_handle))
{
	switch($dir_entry)
	{
		case ".":
		case "..":
			break;
		default:
			if(! is_dir($db->scan_image_dir . "/" . $dir_entry))
			{
				$ext = strtolower(preg_replace("/.*\./", "", $dir_entry));
				if($ext == "jpg")
				{
					$new_barcode = $db->barcode_scan_prefix . str_pad($db->settings->library_scan_barcode, 7, "0", STR_PAD_LEFT);
					$db->settings->library_scan_barcode++;
					save_db_settings();

					if(copy($db->scan_image_dir . "/" . $dir_entry, $db->import_image_dir . "/" . $new_barcode . ".jpg"))
					{
						unlink($db->scan_image_dir . "/" . $dir_entry);
					}; // end if
				}; // end if
			}; // end if
			break;
	}; // end switch
}; // end while
closedir($scan_dir_handle);

if($found && FALSE)
{
	?>
	<form action="" method="post">
	<input name="image_filename" type="hidden" value="<?= $dir_entry; ?>">
	<div style="width:210px;height:95px;background-image:url('<?= $db->scan_image_url; ?>/<?= $dir_entry; ?>');background-repeat:no-repeat;background-position:-25px -60px;"></div>
	<div style="width:210px;padding:3px 0px 0px 70px;">
		<input name="barcode_prefix" type="text" value="3000" size="4" style="text-align:right;"><input id="barcode_suffix" name="barcode_suffix" type="text" value="" size="4"><input name="action" type="submit" value="<?= $link_image_submit_label; ?>">
	</div>
	<script language="javascript">
		document.getElementById('barcode_suffix').focus();
		document.getElementById('barcode_suffix').select();
	</script>
	</form>
	<?
}; // end if



////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// CREATE PDF FILE


require_once($db->include_dir . "/fonts/times.php");
include_once($db->include_dir . "/pdf_maker.php");

$xo_reset = 0.25;
$yo_reset = 0.25;

$xo = $xo_reset;
$yo = $yo_reset;
$bw = 2.25;
$bh = 0.6;

$cw = 2.66;
$ch = 1.5;

$pm->set_font("times");



$scan_dir_handle = opendir($db->import_image_dir);
$new_page = FALSE;
while($dir_entry = readdir($scan_dir_handle))
{
	if($new_page)
	{
		$pm->new_page();
		$new_page = FALSE;
	}; // end if
	switch($dir_entry)
	{
		case ".":
		case "..":
			break;
		default:
			if(! is_dir($db->scan_image_dir . "/" . $dir_entry))
			{
				$ext = strtolower(preg_replace("/.*\./", "", $dir_entry));
				if($ext == "jpg")
				{
					$new_barcode = preg_replace("/\..*/", "", $dir_entry);

					$barcode_type = "AB";
					$barcode_code = $new_barcode;
					$barcode_height = 90;
					$barcode_command = "save";
					include($db->common_image_dir . "/barcode.php");

					$pm->text($xo  - 0.25, $yo + ($bw/2), 0.15, $text, "center");
					$pm->image_jpeg($db->common_image_dir . "/temp/" . $new_barcode . ".jpg", $xo, $yo + ($cw / 2) - ($bw / 2), $bw, $bh);
					//$pm->text($xo + $bh + 0.02, $yo + ($cw / 2), 0.15, "" . trim(preg_replace("/(.)/", "\\1     ", $new_barcode)) . "", "center");
					unlink($filename);

					$scanned_barcode = imagecreatetruecolor(220, 90);
					$scan_image = imagecreatefromjpeg($db->import_image_dir . "/" . $dir_entry);
					imagecopyresampled($scanned_barcode, $scan_image, 0, 0, 20, 111, 220, 90, 220, 5);

					imagefilter($scanned_barcode, IMG_FILTER_BRIGHTNESS, -25);
					imagefilter($scanned_barcode, IMG_FILTER_CONTRAST, -25);

					$scan_output_filename = $db->common_image_dir . "/temp/" . $new_barcode . "_scan.jpg";
					imagejpeg($scanned_barcode, $scan_output_filename);
					$pm->image_jpeg($scan_output_filename, $xo + $bh + 0.1, $yo + ($cw / 2) - (2.25 / 2), 2.25, 0.6);
					unlink($scan_output_filename);


					$xo+=$ch;

					if($xo + $ch > 10.75)
					{
						$xo = $xo_reset;
						$yo += $cw;

						if($yo + $cw > 8.25)
						{
							$xo = $xo_reset;
							$yo = $yo_reset;
							$new_page = TRUE;
						}; // end if

					}; // end if

					//echo $filename . "<br>";
				}; // end if
			}; // end if
			break;
	}; // end switch
}; // end while
closedir($scan_dir_handle);

$pm->stream();

if($found && FALSE)
{
	?>
	<form action="" method="post">
	<input name="image_filename" type="hidden" value="<?= $dir_entry; ?>">
	<div style="width:210px;height:95px;background-image:url('<?= $db->scan_image_url; ?>/<?= $dir_entry; ?>');background-repeat:no-repeat;background-position:-25px -60px;"></div>
	<div style="width:210px;padding:3px 0px 0px 70px;">
		<input name="barcode_prefix" type="text" value="3000" size="4" style="text-align:right;"><input id="barcode_suffix" name="barcode_suffix" type="text" value="" size="4"><input name="action" type="submit" value="<?= $link_image_submit_label; ?>">
	</div>
	<script language="javascript">
		document.getElementById('barcode_suffix').focus();
		document.getElementById('barcode_suffix').select();
	</script>
	</form>
	<?
}; // end if


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


?>
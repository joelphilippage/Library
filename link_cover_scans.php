<?

include_once(dirname(__FILE__) . "/first.php");


$barcode = urldecode($query[0]);


if(! $_SESSION['library_account_ID'])
{
	exit_error("Not Signed In", "You must be signed into perform that action.");
}; // end if

if(! $db->is_librarian_account && FALSE)
{
	exit_error("Access Denied", "Only a librarian can perform that action.");
}; // end if

$link_image_submit_label = "Go";


if($_POST['scanned_barcode'] != "")
{
	if(strlen($_POST['scanned_barcode']) == $db->barcode_length && substr($_POST['scanned_barcode'], 0, 1) == $db->barcode_scan_prefix)
	{
		header("location:" . $db->url_root . "/link_cover_scans.php/" . $_POST['scanned_barcode']);
		exit();
	}; // end if

	if(strlen($_POST['scanned_barcode']) == $db->barcode_length && substr($_POST['scanned_barcode'], 0, 1) == $db->barcode_item_prefix)
	{
		$item_sql = "
			SELECT
				*
			FROM
				`" . $db->table_library_item . "`
			WHERE
				`barcode`='" . mysql_escape_string($_POST['scanned_barcode']) . "'
				AND `enabled`='Y'
		";
		$item_result = mysql_query($item_sql, $mysql->link);
		if(mysql_num_rows($item_result) > 0)
		{
			$item_record = mysql_fetch_object($item_result);

			$insert_sql = "
				INSERT INTO
					`" . $db->table_library_image . "`
				SET
					`" . $db->field_library_item_ID . "`='" . $item_record->ID . "'
			";
			$insert_result = mysql_query($insert_sql, $mysql->link);
			$new_image_ID = mysql_insert_id($mysql->link);

			$update_item_sql = "
				UPDATE
					`" . $db->table_library_item . "`
				SET
					`" . $db->field_image_ID . "`='" . $new_image_ID . "'
				WHERE
					`ID`='" . $item_record->ID . "'
			";
			mysql_query($update_item_sql, $mysql->link);

			reduce($db->import_image_dir . "/" . $barcode . ".jpg", $db->cover_thumbs_image_dir . "/" . substr($new_image_ID, 0, 1) . "/" . $new_image_ID . ".jpg", 200, 200);
			if(copy($db->import_image_dir . "/" . $barcode . ".jpg", $db->cover_image_dir . "/" . substr($new_image_ID, 0, 1) . "/" . $new_image_ID . ".jpg"))
			{
				unlink($db->import_image_dir . "/" . $barcode . ".jpg");
			}; // end if
			header("location:" . $db->url_root . "/item_details.php/barcode/" . $_POST['scanned_barcode']);
			exit();
		}
		else
		{
			exit_error("Item Not Found", "And item with the barcode you entered could not be found!");
		}; // end if
	}
	else
	{
		exit_error("Try Again", "Scan top barcode again.");
	}; // end if

}; // end if


if(! file_exists($db->import_image_dir . "/" . $barcode . ".jpg"))
{
	exit_error("Already Done", "Try another one.");
}; // end if


include_once(dirname(__FILE__) . "/top.php");


?>
<form action="" method="post">
<div class="dialog_input"><input id="scanned_barcode" name="scanned_barcode" type="text" value="" size="<?= $db->barcode_length; ?>" maxlength="<?= $db->barcode_length; ?>" onkeypress="if(event.keyCode==13){submit_barcode();};"></div>
<script language="javascript">
	document.getElementById('scanned_barcode').focus();
	document.getElementById('scanned_barcode').select();
</script>
</form>
<?



include_once(dirname(__FILE__) . "/bottom.php");


?>
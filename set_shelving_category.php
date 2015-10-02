<?

include_once(dirname(__FILE__) . "/first.php");


$location_ID = urldecode($query[0]);
$category_ID = urldecode($query[1]);
$barcode = urldecode($query[2]);


if(! $kiosk_account_record->ID && ! $accounts->account_record->ID)
{
	exit_error("Not Signed In", "You must be signed into perform that action.");
}; // end if

if($kiosk_account_record->librarian != "Y" && $accounts->account_record->librarian != "Y" && $accounts->account_record->admin != "Y")
{
	exit_error("Access Denied", "Only a librarian can perform that action.");
}; // end if



if($location_ID == "" || $category_ID == "")
{
	include_once(dirname(__FILE__) . "/top.php");

	?>
	<div class="dialog_instructions">Choose category where items will be shelved:</div>
	<?

	$location_records = lookup_designation($db->table_location);
	$category_records = lookup_designation($db->table_category);

	?>
	<script language="javascript">
		function set_place()
		{
			if(document.getElementById('location_ID').value != "" && document.getElementById('category_ID').value != "")
			{
				document.location.href = "<?= $db->url_root; ?>/set_shelving_category.php/" + document.getElementById('location_ID').value + "/" + document.getElementById('category_ID').value;
			}; // end if
		}; // end function
	</script>
	<div style="text-align:center;padding-top:20px;">
	<select class="large_input" id="location_ID" onchange="set_place();">
		<option></option>
		<?
			foreach($location_records as $location_record)
			{
				?><option value="<?= $location_record->ID; ?>"<?= ($_POST[$db->field_location_ID] == $location_record->ID ? " selected" : ""); ?>><?= $location_record->title; ?></option><?
			}; // end foreach
		?>
	</select>
	<select class="large_input" id="category_ID" onchange="set_place();">
		<option></option>
		<?
			foreach($category_records as $category_record)
			{
				?><option value="<?= $category_record->ID; ?>"<?= ($_POST[$db->field_category_ID] == $category_record->ID ? " selected" : ""); ?>><?= $category_record->title; ?></option><?
			}; // end foreach
		?>
	</select>
	</div>
	<?


	include_once(dirname(__FILE__) . "/bottom.php");
	exit();
}; // end if

if($barcode != "")
{
	$sql = "
		UPDATE
			`" . $db->table_library_item . "`
		SET
			`" . $db->field_location_ID . "`='" . mysql_escape_string($location_ID) . "',
			`" . $db->field_category_ID . "`='" . mysql_escape_string($category_ID) . "'
		WHERE
			`barcode`='" . mysql_escape_string($barcode) . "'
	";
	mysql_query($sql, $mysql->link);
	header("location:" . $db->url_root . "/set_shelving_category.php/" . $location_ID . "/" . $category_ID);
	exit();
}; // end if

$category_record = lookup_designation($db->table_category, $category_ID);

include_once(dirname(__FILE__) . "/top.php");


$sql = "
	SELECT
		*
	FROM
		`" . $db->table_account . "`
	WHERE
		`ID`='" . $account_ID . "'
		AND `enabled`='Y'
";
$result = mysql_query($sql, $mysql->link);
$record = mysql_fetch_object($result);

?>
<script language="javascript">
	function submit_barcode()
	{
		if(document.getElementById('barcode_input').value.length == <?= $db->barcode_length; ?> && document.getElementById('barcode_input').value.substring(0, 1) == "<?= $db->barcode_item_prefix; ?>")
		{
			document.location.href = "<?= $db->url_root; ?>/set_shelving_category.php/<?= $location_ID; ?>/<?= $category_ID; ?>/" + document.getElementById('barcode_input').value;
		}
		else
		{
			document.location.href = "<?= $db->url_root; ?>/search.php/query/" + document.getElementById('barcode_input').value;
		}; // end if
	}; // end function
</script>
<div class="dialog_instructions"><?= ucwords($db->entry_mode_caption_present_tense); ?> items to place in <?= $category_record->title; ?></div>
<div class="dialog_title"><?= $record->name; ?></div>
<div class="dialog_input"><input id="barcode_input" name="barcode" type="text" value="" size="<?= $db->barcode_length; ?>" maxlength="<?= $db->barcode_length; ?>" onkeypress="if(event.keyCode==13){submit_barcode();};"></div>
<script language="javascript">
	document.getElementById('barcode_input').focus();
	document.getElementById('barcode_input').select();
</script>
<?
if($accounts->computer_record->barcode_reader != "Y")
{
	?>
	<div class="dialog_instructions">and press Enter</div>
	<?
}; // end if

include_once(dirname(__FILE__) . "/bottom.php");


?>
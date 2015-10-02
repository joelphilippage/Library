<?

exit("needs updating to use barcodes instead of item IDs");

include_once(dirname(__FILE__) . "/first.php");


$item_ID = urldecode($query[0]);
$barcode = urldecode($query[1]);


if(! $_SESSION['library_account_ID'])
{
	exit_error("Not Signed In", "You must be signed into perform that action.");
}; // end if

if(! $db->is_librarian_account)
{
	exit_error("Access Denied", "Only a librarian can perform that action.");
}; // end if



if($barcode != "")
{
	$sql = "
		SELECT
			*
		FROM
			`" . $db->table_account . "`
		WHERE
			`library_barcode`='" . $barcode . "'
			AND `enabled`='Y'
	";
	$result = mysql_query($sql, $mysql->link);
	if(mysql_num_rows($result) > 0)
	{
		$record = mysql_fetch_object($result);
		header("location:" . $db->url_root . "/item_checkout.php/onbehalfof/" . $item_ID . "/" . $record->ID);
		exit();
	}
	else
	{
		exit_error("Invalid Library Card", "The card you have " . $db->entry_mode_caption_past_tense . " is invalid.");
	}; // end if
}
else
{
	$db->show_search = FALSE;

	include_once(dirname(__FILE__) . "/top.php");

	?>
	<script language="javascript">
		function submit_barcode()
		{
			if(document.getElementById('barcode_input').value.length == <?= $db->barcode_length; ?> && document.getElementById('barcode_input').value.substring(0, 1) == "<?= $db->barcode_account_prefix; ?>")
			{
				document.location.href = "<?= $db->url_root; ?>/item_checkout_as.php/<?= $item_ID; ?>/" + document.getElementById('barcode_input').value;
			}
			else
			{
				document.location.href = "<?= $db->url_root; ?>/search.php/query/" + document.getElementById('barcode_input').value;
			}; // end if
		}; // end function
	</script>
	<div class="dialog_instructions"><?= ucwords($db->entry_mode_caption_present_tense); ?> library card...</div>
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

}; // end if

exit();
	include_once(dirname(__FILE__) . "/top.php");

	?>
	<div class="title">Scan library card...</div>
	<div style="text-align:center;">
	<?
	$sql = "
		SELECT
			*
		FROM
			`" . $db->table_account . "`
		WHERE
			`enabled`='Y'
		ORDER BY
			`name`
	";
	$result = mysql_query($sql, $mysql->link);
	while($record = mysql_fetch_object($result))
	{
		?><div class="lookup_value"><a href="<?= $db->url_root; ?>/activate_card.php/<?= $record->ID; ?>"><?= $record->name; ?></a></div><?
	}; // end while
	?>
	</div>
	<?

	include_once(dirname(__FILE__) . "/bottom.php");

?>
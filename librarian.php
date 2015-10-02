<?

include_once(dirname(__FILE__) . "/first.php");

$db->current_sub_application = "librarian";

if(! $accounts->account_record->ID)
{
	exit_error("Not Signed In", "You must be signed into perform that action.");
}; // end if

if($accounts->account_record->librarian != "Y")
{
	exit_error("Access Denied", "Only a librarian can perform that action.");
}; // end if

include_once(dirname(__FILE__) . "/top.php");

?><div class="title">Librarian Tools</div><?

?>Overdue Letters - <a href="<?= $db->url_root; ?>/overdue_letter.php" target="_blank">View/Print</a><?

$sql = "
	SELECT
		*
	FROM
		`" . $db->table_group . "`
	WHERE
		`" . $db->field_account_ID . "`='" . $accounts->account_record->ID . "'
		AND `enabled`='Y'
	ORDER BY
		`title`
";
$result = mysql_query($sql, $mysql->link);
if(mysql_num_rows($result) > 0)
{
	?><br><br><?
	while($record = mysql_fetch_object($result))
	{
		?><?= $record->title; ?> - <a href="<?= $db->url_root; ?>/group_checked_out.php/<?= $record->ID; ?>">View</a><br><?
	}; // end while
}; // end if




?><br><br><?
?>

<div class="heading">Resources</div>
<div><a target="_blank" href="<?= $db->url_root; ?>/call_number_guide.php">Call Number Guide</a></div>
<div><a target="_blank" href="http://en.wikipedia.org/wiki/List_of_Dewey_Decimal_classes">List of Dewey Decimal classes</a> <span class="small"><i>(Wikipedia)</i></span></div>
<div><a target="_blank" href="http://www.oclc.org/dewey/resources/summaries/default.htm">Dewey Decimal Classification summaries</a> <span class="small"><i>(OCLC)</i></span></div>
<div><a target="_blank" href="<?= $db->url_root; ?>/00-02407.pdf">Auto Barcode Scanner Manual</a></div>
<br>

<iframe id="action_iframe" src="about:blank" style="display:none;"></iframe>
<script language="javascript">
	function clear_queued_call_numbers()
	{
		if(confirm("Are you sure you want to clear the call number queue?"))
		{
			document.getElementById('action_iframe').src = "generate_call_number_labels.php/clear";
		}; // end if
	}; // end function
</script>

<div class="heading">Print Labels</div>
<div>New Items: <a href="<?= $db->url_root; ?>/generate_barcode_labels.php/item/<?= $db->settings->library_item_barcode; ?>">1 Sheet</a>, <a href="<?= $db->url_root; ?>/generate_barcode_labels.php/item/<?= $db->settings->library_item_barcode; ?>/5">5 Sheets</a>, <a href="<?= $db->url_root; ?>/generate_barcode_labels.php/item/<?= $db->settings->library_item_barcode; ?>/10">10 Sheets</a></div>
<div>New Cards: <a href="<?= $db->url_root; ?>/generate_barcode_labels.php/account/<?= $db->settings->library_account_barcode; ?>">1 Sheet</a>, <a href="<?= $db->url_root; ?>/generate_barcode_labels.php/account/<?= $db->settings->library_account_barcode; ?>/5">5 Sheets</a></div>
<div>New Awards: <a href="<?= $db->url_root; ?>/generate_barcode_labels.php/award/<?= $db->settings->library_award_barcode; ?>">1 Sheet</a>, <a href="<?= $db->url_root; ?>/generate_barcode_labels.php/award/<?= $db->settings->library_award_barcode; ?>/5">5 Sheets</a></div>
<div>Action: <a href="<?= $db->url_root; ?>/generate_barcode_labels.php/action">Labels</a>, <a href="<?= $db->url_root; ?>/generate_action_sheets.php">Sheets</a></div>
<div>Queued Call Numbers: <a href="generate_call_number_labels.php">Labels</a>, <a href="javascript:" onclick="clear_queued_call_numbers();">Clear</a></div>
<br>
<div>Library Cards: <?

$sql = "
	SELECT
		*
	FROM
		`" . $db->table_group . "`
	WHERE
		`" . $db->field_account_ID . "`='" . $accounts->account_record->ID . "'
		AND `enabled`='Y'
	ORDER BY
		`title`
";
$result = mysql_query($sql, $mysql->link);
if(mysql_num_rows($result) > 0)
{
	while($record = mysql_fetch_object($result))
	{
		?><a href="<?= $db->url_root; ?>/generate_barcode_labels.php/cards/<?= $record->ID; ?>"><?= $record->title; ?></a>, <?
	}; // end while
}; // end if

?></div>
<br>
<a href="<?= $db->url_root; ?>/item_edit.php">Add Item</a><br>
<br>
<a href="<?= $db->url_root; ?>/import_scans.php">Import Cover Image Scans</a><br>
<?


include_once(dirname(__FILE__) . "/bottom.php");

?>
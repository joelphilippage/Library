<?

include_once(dirname(__FILE__) . "/first.php");

$db->current_sub_application = "index";

//$db->kiosk_sound = $db->common_sound_url . "/success.mp3";

include_once(dirname(__FILE__) . "/top.php");

if($accounts->account_record->teacher == "Y" || $accounts->account_record->librarian == "Y")
{
	?>
	<h3>Tools</h3>
	<div>New library cards are located in the library storage cabinet in top drawer of the black file cabinet.</div>
	<div><a href="<?= $db->url_root; ?>/teacher_activate_card.php">Activate New Student Library Card</a></div>
	<div><a href="<?= $db->url_root; ?>/teacher_reset_pin.php">Reset Student Library Card PIN</a></div>
	<div><a href="<?= $db->url_root; ?>/teacher_checkout_restriction_override.php" target="_blank">Override checkout restriction</a></div>
	<?
}; // end if
?>
<h3>Special Searches</h3>
<?
?>
<div><a href="<?= $db->url_root; ?>/search.php/special/multiplecopies">Items with multiple copies</a></div>
<div><a href="<?= $db->url_root; ?>/search.php/special/compilations">Compilations</a></div>
<br>
<div>Items checked out by: <?
	$group_sql = "
		SELECT
			*
		FROM
			`" . $db->table_group . "`
		WHERE
			`account_ID`='" . $accounts->account_record->ID . "'
			AND `enabled`='Y'
	";
	$group_result = mysql_query($group_sql, $mysql->link);
	while($group_record = mysql_fetch_object($group_result))
	{
		?><a href="<?= $db->url_root; ?>/group_checked_out.php/<?= $group_record->ID; ?>"><?= $group_record->title; ?></a>, <?
	}; // end while
?><a href="<?= $db->url_root; ?>/search.php/special/checkedout">Everyone</a><?
?></div><?
include_once(dirname(__FILE__) . "/bottom.php");


exit();

?>
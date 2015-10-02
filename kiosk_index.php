<?

include_once(dirname(__FILE__) . "/first.php");



//$db->kiosk_sound = $db->common_sound_url . "/success.mp3";

if($kiosk_signed_in)
{
	include_once(dirname(__FILE__) . "/kiosk_top.php");

	if($db->item_overdue_count > $kiosk_account_record->library_item_overdue_limit && $kiosk_account_record->library_item_overdue_limit >= 0)
	{
		?><div class="action_bar"><span class="action_warning">You have too many overdue items.  You must return them before you can checkout more.</span></div><?
	}
	else
	{
		?><div class="action_bar"><span class="action_instructions">Scan items to checkout.</span></div><?
	}; // end if


	$checkout_sql = "
		SELECT
			*
		FROM
			`" . $db->table_library_checkout . "`
		WHERE
			`" . $db->field_account_ID . "`='" . $_SESSION['library_account_ID'] . "'
			AND `in_datetime`='" . $db->blank_datetime . "'
			AND `paid_datetime`='" . $db->blank_datetime . "'
	";
	$checkout_result = mysql_query($checkout_sql, $mysql->link);

	if(mysql_num_rows($checkout_result) > 0)
	{
		?>
		<center>
		<h1>Currently Checked Out</h1><?

		while($checkout_record = mysql_fetch_object($checkout_result))
		{
			$item_sql = "
				SELECT
					*
				FROM
					`" . $db->table_library_item . "`
				WHERE
					`ID`='" . $checkout_record->item_ID . "'
			";
			$item_result = mysql_query($item_sql, $mysql->link);
			$item_record = mysql_fetch_object($item_result);

			?><div><?= $item_record->title; ?> <span class="small">- due <?= sql_date_format("F jS, Y", $checkout_record->due_datetime); ?></span><?

			if($checkout_record->due_datetime < date("Y-m-d H:i:s"))
			{
				?> - <span class="error_highlighted">OVERDUE</span><?
			}; // end if

			?></div><?

		}; // end while
		?>
		</center>
		<?
	}; // end if

	?><div class="action_bar"><span class="action_status_success"><b><?= ($kiosk_account_record->library_award_points - $kiosk_account_record->library_award_spent); ?></b> reading award points.</span></div><?

	include_once(dirname(__FILE__) . "/kiosk_bottom.php");
}
else
{
	$kiosk_wait_mode = TRUE;
	include_once(dirname(__FILE__) . "/kiosk_top.php");
	?>
	<table cellspacing="0" cellpadding="0" border="0" width="100%" height="100%">
		<tr>
			<td valign="middle">
				<div id="wait_message" class="kiosk_wait_message">Scan library card<br>- or -<br>scan items to return</div>
			</td>
		</tr>
	</table>
	<script language="javascript">
		function random_wait_message_positon()
		{
			topPad = Math.floor(Math.random() * 400);
			bottomPad = 400 - topPad;
			fontSize = 36 + Math.floor(Math.random() * 30);
			document.getElementById('wait_message').style.paddingTop = topPad + "px";
			document.getElementById('wait_message').style.paddingBottom = bottomPad + "px";
			document.getElementById('wait_message').style.fontSize = fontSize + "pt";
		}; // end function
		setInterval("random_wait_message_positon()", 7000);
		parent.waiting = true;
		parent.reset_activity();
	</script>
	<?
	include_once(dirname(__FILE__) . "/kiosk_bottom.php");
}; // end if



exit();

?>
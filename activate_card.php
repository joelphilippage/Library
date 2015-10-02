<?

include_once(dirname(__FILE__) . "/first.php");


$account_ID = urldecode($query[0]);
$barcode = urldecode($query[1]);


if(! $accounts->account_record->ID)
{
	exit_error("Not Signed In", "You must be signed into perform that action.");
}; // end if

$access_allowed = FALSE;
if($accounts->account_record->teacher == "Y")
{
	$access_allowed = TRUE;
}; // end if
if($accounts->account_record->librarian == "Y")
{
	$access_allowed = TRUE;
}; // end if
if($accounts->account_record->admin == "Y")
{
	$access_allowed = TRUE;
}; // end if

if(! $access_allowed)
{
	exit_error("Access Denied", "Only a librarian can perform that action.");
}; // end if

if($account_ID > 0)
{
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
			exit_error("Card Already In Use", "The card you have " . $db->entry_mode_caption_past_tense . " is already in use by " . $record->name);
		}
		else
		{
			$sql = "
				UPDATE
					`" . $db->table_account . "`
				SET
					`library_barcode`='" . $barcode . "',
					`pin`=''
				WHERE
					`ID`='" . $account_ID . "'
					AND `enabled`='Y'
			";
			mysql_query($sql, $mysql->link);
			header("location:" . $db->url_root . "/reset_pin.php/" . $barcode);
			exit();
		}; // end if
	}; // end if

	$db->show_search = FALSE;

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
			if(document.getElementById('barcode_input').value.length == <?= $db->barcode_length; ?> && document.getElementById('barcode_input').value.substring(0, 1) == "<?= $db->barcode_account_prefix; ?>")
			{
				document.location.href = "<?= $db->url_root; ?>/activate_card.php/<?= $account_ID; ?>/" + document.getElementById('barcode_input').value;
			}
			else
			{
				document.location.href = "<?= $db->url_root; ?>/search.php/query/" + document.getElementById('barcode_input').value;
			}; // end if
		}; // end function
	</script>
	<div class="dialog_instructions"><?= ucwords($db->entry_mode_caption_present_tense); ?> new library card for</div>
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
}
else
{
	include_once(dirname(__FILE__) . "/top.php");

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
	$total = mysql_num_rows($result);
	$per_col = ceil($total / 6);

	?>
	<div class="title">Activate library card for...</div>
	<div style="text-align:center;">
		<table cellspacing="0" cellpadding="0" border="0" width="100%">
			<tr>
				<td align="center" valign="top">
				<?
					$counter = 0;
					while($record = mysql_fetch_object($result))
					{
						$counter++;
						if($counter > $per_col)
						{
							$counter = 1;
							?>
							</td>
							<td align="center" valign="top">
							<?
						}; // end if
						?><div style="font-size:10pt;<?= ($record->library_barcode == "" || $record->library_barcode == "0" ? 'font-weight:bold;' : ""); ?>"><nobr><a href="<?= $db->url_root; ?>/activate_card.php/<?= $record->ID; ?>"><?= $record->name; ?></a></nobr></div><?
					}; // end while
				?>
				</td>
			</tr>
		</table>
	</div>
	<?

	include_once(dirname(__FILE__) . "/bottom.php");
}; // end if


?>
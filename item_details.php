<?

include_once(dirname(__FILE__) . "/first.php");


$lookup_type = $query[0];
$item_number = $query[1];


$sql = "
	SELECT
		*
	FROM
		`" . $db->table_library_item . "`
	WHERE
";

switch($lookup_type)
{
	case "ID":
		$sql .= "
				`ID`='" . $item_number . "'
		";
		break;
	case "legacy":
		$sql .= "
				`" . $db->field_old_item_ID . "`='" . $item_number . "'
		";
		break;
	case "barcode":
		$sql .= "
				`barcode`='" . $item_number . "'
		";
		break;
}; // end switch


$result = mysql_query($sql, $mysql->link);

if(mysql_num_rows($result) < 1)
{
	if(strlen($item_number) == $db->barcode_length && substr($item_number, 0, 1) == $db->barcode_item_prefix)
	{
		header("location:" . $db->url_root . "/item_edit.php/new/barcode=" . $item_number . ($_SESSION['last_searched_query'] != "" ? "|title=" . $_SESSION['last_searched_query'] : ""));
		exit();
	}
	else
	{
		exit_error("Item #" . $item_number . " not found.", "There is no items on record matching ID #" . $item_number);
	}; // end if
}; // end if

$record = mysql_fetch_object($result);

include_once(dirname(__FILE__) . "/top.php");

if($accounts->account_record->ID && ($accounts->account_record->librarian == "Y" || $accounts->account_record->admin == "Y"))
{
	?>
	<script language="javascript">
		function drop_down(name)
		{
			document.getElementById(name + '_button').style.backgroundColor = "FFFFFF";
			document.getElementById(name + '_list').style.top = document.getElementById(name + '_button').offsetTop + document.getElementById(name + '_button').offsetHeight - 1;
			document.getElementById(name + '_list').style.left = document.getElementById(name + '_button').offsetLeft;
			document.getElementById(name + '_list').style.display = 'inline';
			document.getElementById(name + '_list').style.zIndex = 1000;
		}; // end function

		function try_drop_up(name)
		{
			if(event.toElement.className != "method_list" && event.toElement.className != "method_item" && event.toElement.className != "method_break")
			{
				drop_up(name);
			}; // end if
		}; // end function

		function drop_up(name)
		{
			document.getElementById(name + '_button').style.backgroundColor = "";
			document.getElementById(name + '_list').style.display = 'none';
		}; // end function

		function report_lost()
		{
			var lost_fee = prompt("How much is the lost fee? ($3.00 minimum)", "");
			document.location.href='<?= $db->url_root;?>/item_lost.php/<?= $record->ID; ?>/' + lost_fee;
		}; // end function

	</script>
	<div class="method_toolbar">
		<a class="method_tool" href="<?= $db->url_root;?>/item_edit.php/<?= $record->ID; ?>">Edit</a>

		<div id="duplicate_button" class="method_tool" onmouseover="drop_down('duplicate');" onmouseout="try_drop_up('duplicate');">Add</div><div id="duplicate_list" class="method_list" onmouseout="try_drop_up('duplicate');">
			<div class="method_item" onclick="document.location.href='<?= $db->url_root;?>/item_edit.php/newcopy/<?= ($record->{$db->field_library_copy_item_ID} ? $record->{$db->field_library_copy_item_ID} : $record->ID); ?>/<?= $record->{$db->field_location_ID}; ?>';">Another Exact Copy</div>
			<?
				if($record->series_ID > 0)
				{
					?><div class="method_item" onclick="document.location.href='<?= $db->url_root;?>/item_edit.php/newvolume/<?= $record->ID; ?>';">Another Volume/Issue in Series</div><?
				}; // end if
			?>
			<div class="method_item" onclick="document.location.href='<?= $db->url_root;?>/item_edit.php/newbyauthors/<?= $record->ID; ?>';">Another by Same Author</div>
			<div class="method_item" onclick="document.location.href='<?= $db->url_root;?>/item_edit.php/newedition/<?= $record->ID; ?>';">Another Newer Edition</div>
		</div>
		<div id="mark_button" class="method_tool" onmouseover="drop_down('mark');" onmouseout="try_drop_up('mark');">Mark</div><div id="mark_list" class="method_list" onmouseout="try_drop_up('mark');">
			<?
				if($record->lost_datetime == $db->blank_datetime)
				{
					if($record->enabled == "Y")
					{
						?>
						<div class="method_item" onclick="report_lost();">Lost</div>
						<div class="method_item" onclick="document.location.href='<?= $db->url_root;?>/item_discard.php/<?= $record->ID; ?>';">Discarded</div>
						<?
						if($account->account_record->control_level < 1)
						{
							?>
							<div class="method_item" onclick="if(confirm('ARE YOU ABSOLUTELY SURE!?!?!?  THIS SERIOUSLY CANNOT BE UNDONE!!!')){document.location.href='<?= $db->url_root;?>/item_purge.php/<?= $record->ID; ?>';};">Purged</a></div>
							<?
						}; // end if
					}
					else
					{
						?>
						<div class="method_item" onclick="document.location.href='<?= $db->url_root;?>/item_undelete.php/<?= $record->ID; ?>';">UN-Discarded</div>
						<?
					}; // end if
				}
				else
				{
					$checkout_sql = "
						SELECT
							*
						FROM
							`" . $db->table_library_checkout . "`
						WHERE
							`ID`='" . mysql_escape_string($record->{$db->field_library_checkout_ID}) . "'
							AND `in_datetime`='" . $db->blank_datetime . "'
					";
					$checkout_result = mysql_query($checkout_sql, $mysql->link);
					$checkout_record = mysql_fetch_object($checkout_result);

					if($checkout_record->paid_datetime == $db->blank_datetime)
					{
						?><div class="method_item" onclick="document.location.href='<?= $db->url_root;?>/item_paid.php/<?= $record->ID; ?>';">Paid</div><?
					}; // end if
					?> <div class="method_item" onclick="document.location.href='<?= $db->url_root;?>/item_unlost.php/<?= $record->ID; ?>';">Found</div><?
					if($record->replaced_datetime == $db->blank_datetime)
					{
						?> <div class="method_item" onclick="document.location.href='<?= $db->url_root;?>/item_replaced.php/<?= $record->ID; ?>';">Replace</div><?
					}; // end if
				}; // end if

			?>
		</div>
		<?
				if($accounts->account_record->librarian == "Y" || $accounts->account_record->librarian == "Y")
				{
					?>
					<div id="print_button" class="method_tool" onmouseover="drop_down('print');" onmouseout="try_drop_up('print');">Print</div><div id="print_list" class="method_list" onmouseout="try_drop_up('print');">
						<div class="method_item" onclick="document.location.href='<?= $db->url_root; ?>/print_cover.php/<?= $record->ID; ?>/dvdslim';">Slim DVD Case Cover</div>
						<div class="method_item" onclick="document.location.href='<?= $db->url_root; ?>/print_cover.php/<?= $record->ID; ?>/disc';">Standard DVD Case Cover</div>
						<div class="method_item" onclick="document.location.href='<?= $db->url_root; ?>/print_cover.php/<?= $record->ID; ?>/map';">Map Insert</div>
					</div>
					<script language="javascript">
						function bio_set()
						{
							var call_number = prompt("Call Number", "<?= strtoupper(substr($record->call_number, -3)); ?>");
							document.location.href='<?= $db->url_root;?>/item_bio_set.php/<?= $record->ID; ?>/' + call_number;
						}; // end function
					</script>
					<a class="method_tool" href="<?= $db->url_root;?>/item_bio_set.php/<?= $record->ID; ?>/<?= strtoupper(substr($record->call_number, -3)); ?>">Auto Bio</a>
					<a class="method_tool" href="javascript:" onclick="bio_set();">Bio</a>
					<?
				}; // end if
		?>
	</div>
	<?
}; // end if

if($db->is_librarian_account && strlen($record->barcode) < $db->barcode_length && $accounts->computer_record->barcode_reader == "Y" && $accounts->computer_record->library_kiosk == "N" && $record->enabled == "Y")
{
	?>
	<script language="javascript">
		function submit_new_barcode()
		{
			show_wait_message();
			document.location.href = "<?= $db->url_root; ?>/item_new_barcode.php/<?= $record->ID; ?>/" + document.getElementById('new_barcode_input').value;
		}; // end function
	</script>
	<input id="new_barcode_input" type="text" value="" onkeypress="if(event.keyCode==13){submit_new_barcode();};">
	<script language="javascript">
		document.getElementById('new_barcode_input').focus();
		document.getElementById('new_barcode_input').select();
	</script>
	<?
}; // end if


if($record->enabled == "N")
{
	if($record->lost_datetime != $db->blank_datetime)
	{
		$checkout_sql = "
			SELECT
				*
			FROM
				`" . $db->table_library_checkout . "`
			WHERE
				`" . $db->field_library_item_ID . "`='" . mysql_escape_string($record->ID) . "'
				AND `in_datetime`='" . $db->blank_datetime . "'
		";
		$checkout_result = mysql_query($checkout_sql, $mysql->link);
		$checkout_record = mysql_fetch_object($checkout_result);

		?>
		<div class="detail_error">ITEM LOST
		<?
				?> on <?
					echo sql_date_format("F jS, Y", $record->lost_datetime);

					if($record->{$db->field_lost_account_ID} > 0)
					{
						if($lost_account_record = lookup_account($record->{$db->field_lost_account_ID}))
						{
							?> by <?= $lost_account_record->name; ?><?
						}; // end if
					}; // end if
		?> ($<?= number_format($checkout_record->lost_fee, 2); ?>)</div>
		<?

		if($checkout_record->paid_datetime != $db->blank_datetime)
		{
			?><div class="detail_error">
				ITEM PAID on <?= sql_date_format("F jS, Y", $checkout_record->paid_datetime); ?>
			</div><?
		}; // end if
		
		if($record->replaced_datetime != $db->blank_datetime)
		{
			?>
			<div class="detail_error">ITEM REPLACED
			<?
					?> on <?
						echo sql_date_format("F jS, Y", $record->replaced_datetime);
			?></div>
			<?
		}; // end if
	}
	else
	{
		?>
		<div class="detail_error">ITEM DISCARDED
		<?
			if($record->deleted_datetime != $db->blank_datetime)
			{
				?> on <?
					echo sql_date_format("M jS, Y", $record->deleted_datetime);
					if(substr($record->deleted_datetime, -8) != $db->blank_time)
					{
						echo sql_date_format(" @ g:ia", $record->deleted_datetime);
					}; // end if

					if($record->{$db->field_deleted_account_ID} > 0)
					{
						if($deleted_account_record = lookup_account($record->{$db->field_deleted_account_ID}))
						{
							?> by <?= $deleted_account_record->name; ?><?
						}; // end if
					}; // end if
			}; // end if
		?></div>
		<?
	}; // end if
}; // end if

?>
<table cellspacing="0" cellpadding"0" border="0" width="100%">
	<tr>
		<td valign="top" width="0%">
			<?
				if($record->lost_datetime != $db->blank_datetime && $record->in_datetime != $db->blank_datetime)
				{
					?>
					<div class="detail_status_lost" title="on <?= sql_date_format("F jS, Y", $record->lost_datetime); ?><?
							if($record->{$db->field_lost_account_ID} > 0)
							{
								if($lost_account_record = lookup_account($record->{$db->field_lost_account_ID}))
								{
									?> by <?= $lost_account_record->name; ?><?
								}; // end if
							}; // end if
						?>">
						Lost
					</div>
					<?
				}
				else
				{
					if($record->{$db->field_checkout_ID})
					{
						$checkout_sql = "
							SELECT
								*
							FROM
								`" . $db->table_library_checkout . "`
							WHERE
								`ID`='" . $record->{$db->field_checkout_ID} . "'
						";
						$checkout_result = mysql_query($checkout_sql, $mysql->link);
						$checkout_record = mysql_fetch_object($checkout_result);

						$checkout_account_sql = "
							SELECT
								*
							FROM
								`" . $db->table_account . "`
							WHERE
								`ID`='" . $checkout_record->{$db->field_account_ID} . "'
						";
						$checkout_account_result = mysql_query($checkout_account_sql, $mysql->link);
						$checkout_account_record = mysql_fetch_object($checkout_account_result);

						?>
						<div class="detail_status_checkedout" title="by <?= $checkout_account_record->name; ?>">
							Checked-Out
						</div>
						<?
					}
					else
					{
						if($record->allow_checkout == "Y")
						{
							?>
							<div class="detail_status_available">
								Available
							</div>
							<?
							
							if($db->is_librarian_account && $_SESSION['library_account_ID'] > 0 && FALSE)
							{
								?>
								- <a href="<?= $db->url_root; ?>/item_checkout_as.php/<?= $record->ID; ?>">Checkout as...</a>
								<?
							}; // end if
						}
						else
						{
							?>
							<div class="detail_status_restricted">
								Restricted
							</div>
							<?
						}; // end if
					}; // end if
				}; // end if
			?>
			<div class="detail_photo_container"><?
				$image_sql = "
					SELECT
						*
					FROM
						`" . $db->table_library_image . "`
					WHERE
						`" . $db->field_library_item_ID . "`='" . $record->ID . "'
						AND `type`='C'
						AND `enabled`='Y'
				";
				$image_result = mysql_query($image_sql, $mysql->link);
				if(mysql_num_rows($image_result) > 0)
				{
					$image_record = mysql_fetch_object($image_result);
					?><img id="thumb_image" src="<?= $db->cover_thumbs_image_url; ?>/<?= substr($image_record->ID, 0, 1); ?>/<?= $image_record->ID; ?>.jpg" onclick="this.style.display='none';document.getElementById('full_image').style.display='inline';"><?
					?><img id="full_image" src="<?= $db->cover_image_url; ?>/<?= substr($image_record->ID, 0, 1); ?>/<?= $image_record->ID; ?>.jpg" style="display:none;" onclick="this.style.display='none';document.getElementById('thumb_image').style.display='inline';"><?
				}
				else
				{
					?><img src="<?= $db->cover_thumbs_image_url; ?>/0/0.jpg"><?
				}; // end if
			?></div>
			<div class="detail_description_container">
				<?
					if($age_record = lookup_designation($db->table_age, $record->{$db->field_age_ID}))
					{
						?><div class="detail_age"><?= $age_record->title; ?></div><?
					}; // end if
					if($type_record = lookup_designation($db->table_library_type, $record->{$db->field_library_type_ID}))
					{
						?><div class="detail_type"><?= $type_record->title; ?></div><?
					}; // end if
					if($style_record = lookup_designation($db->table_style, $record->{$db->field_style_ID}))
					{
						?><div class="detail_style">(<?= $style_record->title; ?>)</div><?
					}; // end if
					if($record->length > 0)
					{
						?><div class="detail_length"><?= $record->length . " " . ($record->length != 1 ? $type_record->length_name_plural : $type_record->length_name_singular); ?></div><?
					}; // end if
				?>
			</div>
		</td>
		<td class="detail_info_container" valign="top" width="100%">
			<table cellspacing="0" cellpadding="0" border="0" width="100%">
				<tr>
					<td valign="top" width="100%">
						<?
							if($series_record = lookup_designation($db->table_library_series, $record->{$db->field_library_series_ID}))
							{
								?>
								<div class="detail_series"><a href="<?= $db->url_root; ?>/search.php/series/<?= $record->series_ID; ?>" title="Series"><?= $series_record->title; ?></a><?
									if($record->title != "")
									{
										?><span title="Number in Series"><?= ($record->series_number > 0 ? " - #" . $record->series_number : ""); ?></span><?
									}; // end if
								?></div>
								<?
							}; // end if
						?>
						<div class="detail_title" title="Title"><?= ($record->title != "" ? $record->title : "#" . $record->series_number); ?><?
							if($record->edition != "")
							{
								?> <span class="detail_edition">(<?= $record->edition; ?>)</span><?
							}; // end if
							if($record->copy_item_ID > 0)
							{
								$copy_lookup_sql = "
									SELECT
										ID
									FROM
										`" . $db->table_library_item . "`
									WHERE
										`" . $db->field_library_copy_item_ID . "`='" . $record->{$db->field_library_copy_item_ID} . "'
										AND `enabled`='Y'
								";
								$copy_lookup_result = mysql_query($copy_lookup_sql, $mysql->link);

								$total_copies = mysql_num_rows($copy_lookup_result);

								if($total_copies > 1)
								{
									?> <span class="detail_copy">(Copy <?= $record->copy_number; ?> of <a href="<?= $db->url_root; ?>/search.php/copies/<?= $record->{$db->field_library_copy_item_ID}; ?>"><?= $total_copies; ?></a>)</span><?
								}; // end if
							}; // end if
						?></div>
						<?
							if($record->parallel_title != "")
							{
								?>
								<div class="detail_parallel_title"><?= $record->parallel_title; ?></div>
								<?
							}; // end if

							////////////////////////////////////////////////////////////////////////////////////////////////////////////////
							// NON-COMPILATION AUTHORS

							if(count($author_records = lookup_author($record->ID, FALSE, TRUE)))
							{
								$author_list = array();
								$last_author_type = 0;
								foreach($author_records as $author_record)
								{
									$this_by_text = $author_record->by_text;
									$this_author_type = $author_record->type;
									if($this_author_type != $last_author_type && $last_author_type != 0)
									{
										?>
										<div class="detail_authors"><?= $last_by_text; ?> <?= implode("; ", $author_list); ?></div>
										<?
										$author_list = array();
									}; // end if

									$author_list[] = '<a href="' . $db->url_root . '/search.php/author/' . $author_record->ID . '">' . build_author_name($author_record) . '</a>';
									$last_by_text = $this_by_text;
									$last_author_type = $this_author_type;
								}; // end foreach
							}; // end if
							if(count($author_list) > 0)
							{
								?>
								<div class="detail_authors"><?= $author_record->by_text; ?> <?= implode("; ", $author_list); ?></div>
								<?
							}; // end if

							////////////////////////////////////////////////////////////////////////////////////////////////////////////////
							// COMPILATION TITLES & AUTHORS

							output_compilation_titles_and_authors($record);


							////////////////////////////////////////////////////////////////////////////////////////////////////////////////
						?>
					</td>
					<td valign="top">
						<div class="detail_call_number" title="Call Number"><nobr><?
							if($record->call_number != "")
							{
								?><?= implode("<br>", explode(",", $record->call_number)); ?><?
							}; // end if
						?></nobr></div>
					</td>
				</tr>
			</table>
			<?
				if($record->summary != "")
				{
					?>
					<div class="detail_summary" title="Summary"><?= nl2br($record->summary); ?></div>
					<?
				}; // end if
				if($publisher_record = lookup_designation($db->table_publisher, $record->{$db->field_publisher_ID}))
				{
					?><div class="detail_publisher">Published by <a href="<?= $db->url_root; ?>/search.php/publisher/<?= $publisher_record->ID; ?>"><?= $publisher_record->title . ($publisher_record->location != "" ? " - " . $publisher_record->location : ""); ?></a></div><?
				}; // end if
			?>
		</td>
		<td width="0%" valign="top">
			<div class="detail_location_container">
				<?
					if($location_record = lookup_designation($db->table_location, $record->{$db->field_location_ID}))
					{
						?><div class="detail_location"><?= $location_record->title; ?></div><?
					}; // end if
					if($category_record = lookup_designation($db->table_category, $record->{$db->field_category_ID}))
					{
						?><div class="detail_category"><?= $category_record->title; ?></div><?
					}; // end if
				?>
			</div>
			<?
				if(count($subject_records = lookup_subject($record->ID, "S", TRUE)))
				{
					?>
					<div class="detail_other_container">
					<?
					foreach($subject_records as $subject_record)
					{
						?><div class="detail_subject"><xa href="<?= $db->url_root; ?>/search.php/subject/<?= $subject_record->ID; ?>"><?= $subject_record->title; ?></xa></div><?
					}; // end foreach
					?>
					</div>
					<?
				}; // end if
				if($db->is_librarian_account)
				{
					?>
					<div class="detail_system_container">
						<nobr>System ID: <?= $record->ID; ?></nobr>
						<?= ($record->{$db->field_old_item_ID} > 0 ? "<br><nobr>Legacy ID: " . $record->{$db->field_old_item_ID} . "</nobr>" : ""); ?>
						<?= ($record->isbn != "" ? "<br><nobr>ISBN: " . $record->isbn . "</nobr>" : ""); ?>
						<?= ($record->lc_control_number != "" ? "<br><nobr>LC Control #: " . $record->lc_control_number . "</nobr>" : ""); ?>
						<?
							if($record->barcode > 0)
							{
								$barcode = str_pad($record->barcode, $db->barcode_length, "0", STR_PAD_LEFT);
								?>
								<br><nobr>Barcode ID: <?= $barcode; ?></nobr>
								<?
							}; // end if
						?>
					</div>
					<?
				}; // end if
			?>
			<img src="<?= $db->void_image_url; ?>" width="265px" height="1px">
		</td>
	</tr>
</table>
<?


?>
<table cellspacing="0" cellpadding="0" border="0" width="100%">
<?
	?>
	<?
		if($record->notes != "")
		{
			?>
			<tr>
				<td class="detail_caption"></td>
				<td class="detail_value"><?= $record->notes; ?></td>
			</tr>
			<?
		}; // end if




		if($accounts->account_record->admin == "Y" || $accounts->account_record->librarian == "Y")
		{
			?>
			<tr>
				<td class="detail_caption"><nobr>History:</nobr></td>
				<td class="detail_value">
					<?
						$history_sql = "
							SELECT
								*
							FROM
								`" . $db->table_library_checkout . "`
							WHERE
								`" . $db->field_library_item_ID . "`='" . $record->ID . "'
								AND `out_datetime`!='" . $db->blank_datetime . "'
						";
						$history_result = mysql_query($history_sql, $mysql->link);
						while($history_record = mysql_fetch_object($history_result))
						{
							$who_sql = "
								SELECT
									*
								FROM
									`" . $db->table_account . "`
								WHERE
									`ID`='" . $history_record->{$db->field_account_ID} . "'
							";
							$who_result = mysql_query($who_sql, $mysql->link);
							$who_record = mysql_fetch_object($who_result);

							?><?= $history_record->out_datetime; ?>: <?= $who_record->name; ?><br><?
						}; // end while
					?>
				</td>
			</tr>
			<?
		}; // end if

	?>
</table>
<?

include_once(dirname(__FILE__) . "/bottom.php");

?>
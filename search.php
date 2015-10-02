<?

include_once(dirname(__FILE__) . "/first.php");


$search_type = urldecode($query[0]);
$search_query = urldecode($query[1]);
$special_query = urldecode($query[2]);
$refine_query = urldecode($query[3]);

switch($search_type)
{
	case "special":
		break;
	case "copies":
		break;
	case "series":
		break;
	case "publisher":
		break;
	case "author":
		break;
	case "subject":
		break;
	case "query":
	default:
		if($search_query == "")
		{
			header("location:" . $db->url_root . "/index.php");
			exit();
		}; // end if

		$search_requery = $search_query;
		if(strlen(trim($search_query)) == $db->isbn_length && preg_replace("/\d/", "", trim($search_query) == ""))
		{
			exit_error("ISBN LOOKUP", "Feature not implemented yet.");
		}; // end if
		if(strlen($search_query) == $db->barcode_length && preg_replace("/\d/", "", $search_query) == "")
		{
			if($special_query > 300 && $accounts->computer_record->library_kiosk == "Y")
			{
				exit_error("Keypad entry not allowed!", "You must use the barcode scanner.");
			}; // end if

			switch(substr($search_query, 0, 1))
			{
				case $db->barcode_account_prefix:
					if($_SESSION['library_account_ID'] > 0)
					{
						if($accounts->library_account_record->library_barcode == $search_query)
						{
							header("location:" . $db->url_root . "/signout.php/" . $search_query);
							exit();
						}
						else
						{
							header("location:" . $db->url_root . "/signin.php/" . $search_query);
							exit();
						}; // end if
					}
					else
					{
						header("location:" . $db->url_root . "/signin.php/" . $search_query);
						exit();
					}; // end if
					break;
				case $db->barcode_action_prefix:
					switch($search_query)
					{
						///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
						///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
						///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
						///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

						case $db->barcode_view_actions:
							?>
							<script language="javascript">
								window.open("<?= $db->url_root; ?>/generate_barcode_labels.php/action", "library_actions");
								document.location.replace("<?= $db->url_root; ?>/index.php");
							</script>
							<?
							exit();
							break;
						case $db->barcode_activate_library_card: header("location:" . $db->url_root . "/activate_card.php"); exit(); break;
						case $db->barcode_reset_pin_number: header("location:" . $db->url_root . "/reset_pin.php"); exit();  break;
						case $db->barcode_deactivate_library_card: header("location:" . $db->url_root . "/deactivate_card.php"); exit();  break;
						case $db->barcode_return_items: header("location:" . $db->url_root . "/activate_return_mode.php"); exit();  break;
						case $db->barcode_set_shelving_category: header("location:" . $db->url_root . "/set_shelving_category.php"); exit();  break;
						case $db->barcode_checked_out_items: header("location:" . $db->url_root . "/search.php/special/checkedout"); exit();  break;
						case $db->barcode_search_multiple_copies: header("location:" . $db->url_root . "/search.php/special/multiplecopies"); exit();  break;
//						case $db->barcode_textbook_checkout: header("location:" . $db->url_root . "/item_checkout.php/textbook"); exit();  break;
						case $db->barcode_view_overdue_items: header("location:" . $db->url_root . "/group_checked_out.php/overdue"); exit();  break;
						case $db->barcode_sign_out: header("location:" . $db->url_root . "/signout.php"); exit();  break;
						case $db->barcode_search_compilations: header("location:" . $db->url_root . "/search.php/special/compilations"); exit();  break;
						case $db->barcode_queue_call_number: header("location:" . $db->url_root . "/queue_call_number.php"); exit();  break;
						case $db->barcode_assign_award_value: header("location:" . $db->url_root . "/assign_award_value.php"); exit();  break;
						case $db->barcode_wizard_discard_item: header("location:" . $db->url_root . "/wizard_item_discard.php"); exit();  break;

						///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
						///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
						///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
						///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
						default:
							exit_error("Unknown Action", "The action you have " . $db->entry_mode_caption_past_tense . " is not recognized.");
							break;
					}; // end switch
					break;
				case $db->barcode_award_prefix:
					header("location:" . $db->url_root . "/award_process.php/" . $search_query);
					exit();
					break;
				case $db->barcode_item_prefix:
					if($accounts->computer_record->library_kiosk == "Y")
					{
						if(! $_SESSION['library_account_ID'])
						{
							header("location:" . $db->url_root . "/item_return.php/return/" . $search_query);
							exit();
						}
						else
						{
							header("location:" . $db->url_root . "/item_checkout.php/auto/" . $search_query);
							exit();
						}; // end if
					}
					else
					{
						header("location:" . $db->url_root . "/item_details.php/barcode/" . $search_query);
						exit();
					}; // end if
					break;
				case $db->barcode_scan_prefix:
					header("location:" . $db->url_root . "/link_cover_scans.php/" . $search_query);
					exit();
					break;
			}; // end switch
		}; // end if

		if($accounts->computer_record->library_kiosk == "Y")
		{
			exit_error("Invalid", "Try again.");
		}; // end if

		if(intval($search_query) > 0)
		{
			header("location:" . $db->url_root . "/item_details.php/legacy/" . $search_query);
			exit();
		}; // end if

		break;
}; // end switch


include_once(dirname(__FILE__) . "/top.php");

if(trim($search_query) != "")
{
	$result_IDs = array();
	$result_records = array();

	$default_lookup = TRUE;

	$common_fields_sql = "
		`" . $db->table_library_item . "`.`ID` AS 'ID',
		`" . $db->table_library_item . "`.`title` AS 'title',
		`" . $db->table_library_item . "`.`parallel_title` AS 'parallel_title',
		`" . $db->table_library_item . "`.`edition` AS 'edition',
		`" . $db->table_library_item . "`.`summary` AS 'summary',
		`" . $db->table_library_item . "`.`" . $db->field_library_copy_item_ID . "` AS '" . $db->field_library_copy_item_ID . "',
		`" . $db->table_library_item . "`.`copy_number` AS 'copy_number',
		`" . $db->table_library_item . "`.`compilation` AS 'compilation',
		`" . $db->table_library_item . "`.`call_number` AS 'call_number',
		`" . $db->table_library_item . "`.`" . $db->field_library_series_ID . "` AS '" . $db->field_library_series_ID . "',
		`" . $db->table_library_item . "`.`series_number` AS 'series_number',
		`" . $db->table_library_item . "`.`image_ID` AS 'image_ID',
		`" . $db->table_library_item . "`.`lost_datetime` AS 'lost_datetime',
		`" . $db->table_library_item . "`.`barcode` AS 'barcode',
		`" . $db->table_library_item . "`.`allow_checkout` AS 'allow_checkout',
		`" . $db->table_library_item . "`.`" . $db->field_checkout_ID . "` AS '" . $db->field_checkout_ID . "',
		`" . $db->table_library_item_link_type . "`.`by_text` AS 'by_text',
		`" . $db->table_location . "`.`title` AS 'location_title',
		`" . $db->table_category . "`.`title` AS 'category_title',
		`" . $db->table_category . "`.`icon` AS 'category_icon',
		`" . $db->table_library_series . "`.`title` AS 'series_title',
		`" . $db->table_author . "`.`ID` AS '" . $db->field_author_ID . "',
		`" . $db->table_author . "`.`last_name` AS 'author_last_name',
		`" . $db->table_author . "`.`first_name` AS 'author_first_name',
		`" . $db->table_author . "`.`middle_name` AS 'author_middle_name'
	";
	$common_tables_sql = "
		`" . $db->table_library_item . "` LEFT JOIN `" . $db->table_library_series . "` ON `" . $db->table_library_item . "`.`" . $db->field_library_series_ID . "` = `" . $db->table_library_series . "`.`ID`,
		`" . $db->table_location . "`,
		`" . $db->table_category . "`,
		`" . $db->table_library_item_link . "`,
		`" . $db->table_library_item_link_type . "`,
		`" . $db->table_author . "`
	";
	$common_where_sql = "
		AND `" . $db->table_library_item . "`.`" . $db->field_location_ID . "` = `" . $db->table_location . "`.`ID`
		AND `" . $db->table_library_item . "`.`" . $db->field_category_ID . "` = `" . $db->table_category . "`.`ID`
		AND `" . $db->table_library_item_link . "`.`" . $db->field_library_item_ID . "`=`" . $db->table_library_item . "`.`ID`
		AND `" . $db->table_library_item_link . "`.`" . $db->field_library_item_link_type_ID . "`=`" . $db->table_library_item_link_type . "`.`ID`
		AND `" . $db->table_library_item_link . "`.`priority`=0
		AND `" . $db->table_library_item_link_type . "`.`table`='author'
		AND `" . $db->table_library_item_link . "`.`" . $db->field_record_ID . "`=`" . $db->table_author . "`.`ID`
		" . ($accounts->account_record->student == "Y" ? "AND `" . $db->table_location . "`.`visible_to_students`='Y'" : "") . "
		AND `" . $db->table_library_item . "`.`enabled`='Y'
		AND `" . $db->table_location . "`.`enabled`='Y'
		AND `" . $db->table_category . "`.`enabled`='Y'
		AND `" . $db->table_library_item_link . "`.`enabled`='Y'
		AND `" . $db->table_author . "`.`enabled`='Y'
	";

	$search_words = array();

	switch($search_type)
	{
		case "special":
			switch($search_query)
			{
				case "checkedout":
					$checkout_sql = "
						SELECT
							*
						FROM
							`" . $db->table_library_checkout . "`
						WHERE
							`in_datetime`='" . $db->blank_datetime . "'
					";
					$checkout_result = mysql_query($checkout_sql, $mysql->link);
					$checkout_IDs = array();
					while($checkout_record = mysql_fetch_object($checkout_result))
					{
						$checkout_IDs[] = $checkout_record->{$db->field_library_item_ID};
					}; // end while

					$sql = "
						SELECT
							" . $common_fields_sql . "
						FROM
							" . $common_tables_sql . "
						WHERE
							(`" . $db->table_library_item . "`.`ID`='" . implode("' OR `" . $db->table_library_item . "`.`ID`='", $checkout_IDs) . "')
							" . $common_where_sql . "
					";
					$result = mysql_query($sql, $mysql->link);
					while($record = mysql_fetch_object($result))
					{
						$result_IDs[$record->ID] = levenshtein(strtolower($record->title), strtolower($like_query)) . $record->copy_number;
						$result_records[$record->ID] = $record;
					}; // end while
					$default_lookup = FALSE;
					$search_heading = "Checked-Out Items";
					break;
				case "usedby":
					$checkout_sql = "
						SELECT
							*
						FROM
							`" . $db->table_library_checkout . "`
						WHERE
							`in_datetime`!='" . $db->blank_datetime . "'
							AND `out_datetime`!='" . $db->blank_datetime . "'
							AND `" . $db->field_account_ID . "`='" . mysql_escape_string($special_query) . "'
					";
					$checkout_result = mysql_query($checkout_sql, $mysql->link);
					$checkout_IDs = array();
					while($checkout_record = mysql_fetch_object($checkout_result))
					{
						$checkout_IDs[] = $checkout_record->{$db->field_library_item_ID};
					}; // end while

					$account_sql = "
						SELECT
							*
						FROM
							`" . $db->table_account . "`
						WHERE
							`ID`='" . mysql_escape_string($special_query) . "'
							AND `enabled`='Y'
					";
					$account_result = mysql_query($account_sql, $mysql->link);
					$account_record = mysql_fetch_object($account_result);

					$sql = "
						SELECT
							" . $common_fields_sql . "
						FROM
							" . $common_tables_sql . "
						WHERE
							(`" . $db->table_library_item . "`.`ID`='" . implode("' OR `" . $db->table_library_item . "`.`ID`='", $checkout_IDs) . "')
							AND `" . $db->table_library_item . "`.`" . $db->field_library_type_ID . "`='" . $refine_query . "'
							" . $common_where_sql . "
						ORDER BY
							`" . $db->table_library_item . "`.`title`
					";
					echo $sql;
					$result = mysql_query($sql, $mysql->link);
					while($record = mysql_fetch_object($result))
					{
						$result_IDs[$record->ID] = levenshtein(strtolower($record->title), strtolower($like_query)) . $record->copy_number;
						$result_records[$record->ID] = $record;
					}; // end while
					$default_lookup = FALSE;
					$search_heading = "Items read by " . $account_record->name;
					break;
				case "multiplecopies":
					$sql = "
						SELECT
							count(*) AS 'count',
							" . $common_fields_sql . "
						FROM
							" . $common_tables_sql . "
						WHERE
							`" . $db->table_library_item . "`.`" . $db->field_library_copy_item_ID . "`>0
							" . $common_where_sql . "
						GROUP BY
							`" . $db->table_library_item . "`.`" . $db->field_library_copy_item_ID . "`,
							`" . $db->table_library_item_link . "`.`" . $db->field_record_ID . "`
						HAVING
							`count`>1
						ORDER BY
							`count` DESC
					";
					//echo $sql;
					$search_heading = "Items with multiple copies";
					break;
				case "compilations":
					$sql = "
						SELECT
							" . $common_fields_sql . "
						FROM
							" . $common_tables_sql . "
						WHERE
							`" . $db->table_library_item . "`.`compilation`='Y'
							" . $common_where_sql . "
						ORDER BY
							`" . $db->table_library_item . "`.`" . $db->field_library_series_ID . "`,
							`" . $db->table_library_item . "`.`series_number`
					";
					$search_heading = "Compilations";
					break;
				default:
					exit_error("Unknown Search", "The special search you have specified is not supported.");
					break;
			}; // end switch
			break;
		case "copies":
			$master_copy_sql = "
				SELECT
					*
				FROM
					`" . $db->table_library_item . "`
				WHERE
					`ID`='" . mysql_escape_string($search_query) . "'
			";
			$master_copy_result = mysql_query($master_copy_sql, $mysql->link);
			$master_copy_record = mysql_fetch_object($master_copy_result);
			
			$sql = "
				SELECT
					" . $common_fields_sql . "
				FROM
					" . $common_tables_sql . "
				WHERE
					`" . $db->table_library_item . "`.`" . $db->field_library_copy_item_ID . "`='" . mysql_escape_string($search_query) . "'
					" . $common_where_sql . "
				ORDER BY
					`" . $db->table_library_item . "`.`copy_number` ASC
			";
			$search_heading = "Copies of " . $master_copy_record->title;
			break;
		case "series":
			$series_sql = "
				SELECT
					*
				FROM
					`" . $db->table_library_series . "`
				WHERE
					`ID`='" . mysql_escape_string($search_query) . "'
			";
			$series_result = mysql_query($series_sql, $mysql->link);
			$series_record = mysql_fetch_object($series_result);
			$like_query = $series_record->title;

			$sql = "
				SELECT
					" . $common_fields_sql . "
				FROM
					" . $common_tables_sql . "
				WHERE
					`" . $db->table_library_item . "`.`" . $db->field_library_series_ID . "`='" . mysql_escape_string($search_query) . "'
					" . $common_where_sql . "
				ORDER BY
					`" . $db->table_library_item . "`.`series_number` ASC
			";
			$search_heading = "Series: " . $series_record->title;
			break;
		case "publisher":
			$publisher_sql = "
				SELECT
					*
				FROM
					`" . $db->table_publisher . "`
				WHERE
					`ID`='" . mysql_escape_string($search_query) . "'
			";
			$publisher_result = mysql_query($publisher_sql, $mysql->link);
			$publisher_record = mysql_fetch_object($publisher_result);
			$like_query = $publisher_record->title;

			$sql = "
				SELECT
					" . $common_fields_sql . "
				FROM
					" . $common_tables_sql . "
				WHERE
					`" . $db->table_library_item . "`.`" . $db->field_publisher_ID . "`='" . mysql_escape_string($search_query) . "'
					" . $common_where_sql . "
			";
			$search_heading = "Publisher: " . $publisher_record->title;
			break;
		case "author":
			$author_sql = "
				SELECT
					*
				FROM
					`" . $db->table_author . "`
				WHERE
					`ID`='" . mysql_escape_string($search_query) . "'
			";
			$author_result = mysql_query($author_sql, $mysql->link);
			$author_record = mysql_fetch_object($author_result);
			$like_query = $author_record->title;

			$sql = "
				SELECT
					`" . $db->table_library_item_link . "`.`record_ID` AS 'author_ID',
					" . $common_fields_sql . "
				FROM
					" . $common_tables_sql . "
				WHERE
					`" . $db->table_library_item_link . "`.`" . $db->field_record_ID . "`='" . mysql_escape_string($search_query) . "'
					" . $common_where_sql . "
			";
			$search_heading = "Author: " . build_author_name($author_record);
			break;
		case "subject":
			$subject_sql = "
				SELECT
					*
				FROM
					`" . $db->table_subject . "`
				WHERE
					`ID`='" . mysql_escape_string($search_query) . "'
			";
			$subject_result = mysql_query($subject_sql, $mysql->link);
			$subject_record = mysql_fetch_object($subject_result);
			$like_query = $subject_record->title;

			$sql = "
				SELECT
					`" . $db->table_library_item_link . "`.`record_ID` AS 'subject_ID',
					" . $common_fields_sql . "
				FROM
					`" . $db->table_library_item_link . "` AS subject_link,
					" . $common_tables_sql . "
				WHERE
					`" . $db->table_library_item . "`.`ID`=`subject_link`.`" . $db->field_library_item_ID . "`
					AND `subject_link`.`record_ID`='" . mysql_escape_string($search_query) . "'
					AND `" . $db->table_library_item_link_type . "`.`table`='subject'
					" . $common_where_sql . "
			";
			echo $sql;
			$search_heading = "Subject: " . $subject_record->title;
			break;
		default:
			$search_words = explode(" ", preg_replace("/\s+/", " ", preg_replace("/(,|\.|!|\?)/", "", $search_query)));
			for($a = 0; $a < count($search_words); $a++)
			{
				$search_words[$a] = mysql_escape_string($search_words[$a]);
			}; // end for

			$fields = array(
				"`" . $db->table_library_item . "`.`title`",
				"`" . $db->table_library_item . "`.`parallel_title`",
				"`" . $db->table_library_item . "`.`summary`",
				"`" . $db->table_library_item . "`.`extra_search_text`",
				"`" . $db->table_library_series . "`.`title`",
				"`" . $db->table_author . "`.`first_name`",
				"`" . $db->table_author . "`.`middle_name`",
				"`" . $db->table_author . "`.`last_name`",
			);
			$where_query_sql = array();
			foreach($search_words as $word)
			{
				$where_query_sql[] = implode(" LIKE '%" . $word . "%' OR ", $fields) . " LIKE '%" . $word . "%'";
			}; // end foreach

			$sql = "
				SELECT
					" . $common_fields_sql . "
				FROM
					" . $common_tables_sql . "
				WHERE
					(" . implode(") AND (", $where_query_sql) . ")
					" . $common_where_sql . "
					" . ($db->is_student_account ? " AND `" . $db->table_location . "`.`visible_to_students`='Y'" : "") . "
				ORDER BY
					`" . $db->table_library_item . "`.`copy_number` ASC
			";
			$like_query = $search_query;
			$search_heading = "Search: " . $search_query;
			break;
	}; // end switch

	if($default_lookup)
	{
		if(! $result = mysql_query($sql, $mysql->link))
		{
			exit_error("MySQL Error", mysql_error($mysql->link));
		}; // end if
		while($record = mysql_fetch_object($result))
		{
			$result_IDs[$record->ID] = levenshtein(strtolower($record->title), strtolower($like_query)) . $record->copy_number;
			$result_records[$record->ID] = $record;
		}; // end while
	}; // end if

	switch($search_type)
	{
		case "special":
		case "series":
			break;
		default:
			asort($result_IDs);
			break;
	}; // end switch

	?>
	<div class="title"><?= $search_heading; ?></div>
	<?

	
	?>
	<table cellspacing="0" cellpadding="0" border="0" width="100%">
		<tr>
			<td width="70%" valign="top">
				<div class="search_results"><?= count($result_IDs); ?> items found</div>
				<?
					if($accounts->account_record->librarian == "Y" || $accounts->account_record->admin == "Y")
					{
						switch($search_type)
						{
							case "author":
								?>
								<div class="method_toolbar">
									<a class="method_tool" href="<?= $db->url_root;?>/author_edit.php/<?= $search_query; ?>" target="_blank">Edit Author</a>
								</div>
								<?
								break;
							case "series":
								?>
								<div class="method_toolbar">
									<a class="method_tool" href="<?= $db->url_root;?>/series_edit.php/<?= $search_query; ?>" target="_blank">Edit Series</a>
								</div>
								<?
								break;
						}; // end switch
					}; // end if
				?>

				<div class="search_listing">
				<?
					if($db->is_librarian_account)
					{
						?>
						<script language="javascript">
							var selected_items = new Array();
						</script>
						<?
					}; // end if

					$item_counter = 0;
					foreach($result_IDs as $record_ID => $val)
					{
						$item_counter++;
						//echo $record_ID . "::" . $val . "::::";
						?>
						<div class="search_item_<?= ($item_counter %2 ? "a" : "b"); ?>">
							<table cellspacing="0" cellpadding="0" border="0" width="100%">
								<tr>
									<td class="search_icon" width="0%" valign="top">
										<a href="<?= $db->url_root; ?>/item_details.php/ID/<?= $record_ID; ?>"><img src="<?= $db->url_root; ?>/images/icons/<?= $result_records[$record_ID]->category_icon; ?>.jpg"></a>
									</td>
									<?
										if($result_records[$record_ID]->{$db->field_image_ID} > 0 || FALSE)
										{
											?>
											<td class="search_image" width="0%" valign="top">
												<a href="<?= $db->url_root; ?>/item_details.php/ID/<?= $record_ID; ?>"><img src="<?= $db->cover_thumbs_image_url; ?>/<?= substr($result_records[$record_ID]->{$db->field_image_ID}, 0, 1); ?>/<?= $result_records[$record_ID]->{$db->field_image_ID}; ?>.jpg"></a>
											</td>
											<?
										}; // end if
									?>
									<td width="80%" valign="top">
										<?
											if($result_records[$record_ID]->{$db->field_library_series_ID} > 0 && $search_type != "series")
											{
												?>
												<div class="search_series"><a href="<?= $db->url_root; ?>/search.php/series/<?= $result_records[$record_ID]->series_ID; ?>"><?= highlight_search_query($result_records[$record_ID]->series_title, $search_words); ?></a><?
													if($result_records[$record_ID]->series_number > 0 && $title == "")
													{
														?>: #<?= $result_records[$record_ID]->series_number; ?><?
													}; // end if
												?></div>
												<?
											}; // end if
										?>
										<div>
											<?
												switch($search_type)
												{
													case "series":
														if($result_records[$record_ID]->series_number > 0)
														{
															?><a href="<?= $db->url_root; ?>/item_details.php/ID/<?= $record_ID; ?>"><?= ($result_records[$record_ID]->title == "" ? "<b>" : ""); ?>#<?= $result_records[$record_ID]->series_number; ?><?= ($result_records[$record_ID]->title == "" ? "</b>" : ""); ?></a><?= ($result_records[$record_ID]->title == "" ? "" : ":"); ?> <?
														}; // end if
														break;
													default:
														break;
												}; // end switch
												if($result_records[$record_ID]->title != "" || $search_type != "series")
												{
													?>
													<a href="<?= $db->url_root; ?>/item_details.php/ID/<?= $record_ID; ?>"><b><?= ($result_records[$record_ID]->title != ""  ? highlight_search_query($result_records[$record_ID]->title, $search_words) : "#" . $result_records[$record_ID]->series_number); ?></b></a><?= ($result_records[$record_ID]->edition != "" ? " (" . highlight_search_query($result_records[$record_ID]->edition, $search_words) . ")" : ""); ?>
													<?
												}; // end if
												?>
												<span class="search_author"><?= $result_records[$record_ID]->by_text; ?> <a href="<?= $db->url_root; ?>/search.php/author/<?= $result_records[$record_ID]->{$db->field_author_ID}; ?>"><?= highlight_search_query(build_author_name($result_records[$record_ID], FALSE, TRUE), $search_words); ?></a></span>
												<?
												if($search_type . $search_query == "specialmultiplecopies")
												{
													?> - <a href="<?= $db->url_root; ?>/search.php/copies/<?= $result_records[$record_ID]->{$db->field_library_copy_item_ID}; ?>"><?= $result_records[$record_ID]->count; ?> copies</a><?
												}
												else
												{
													if(! $db->is_student_account)
													{
														?><?= ($result_records[$record_ID]->{$db->field_library_copy_item_ID} > 0 ? " - Copy " . $result_records[$record_ID]->copy_number : ""); ?><?
													}; // end if
												}; // end if
											?>
										</div>
										<?
											if($result_records[$record_ID]->parallel_title != "")
											{
												?>
												<div class="search_parallel_title"><?= highlight_search_query($result_records[$record_ID]->parallel_title, $search_words); ?></div>
												<?
											}; // end if
											if($result_records[$record_ID]->compilation == "Y")
											{
												output_compilation_titles_and_authors($result_records[$record_ID], "search", FALSE, $search_words);
											}; // end if
											if($result_records[$record_ID]->summary != "")
											{
												?><div class="small indent"><?= highlight_search_query($result_records[$record_ID]->summary, $search_words); ?></div><?
											}; // end if
										?>
									</td>
									<td width="20%" valign="top" align="right">
										<div class="search_location"><nobr><?= $result_records[$record_ID]->location_title; ?></nobr></div>
										<div class="search_category"><nobr><?= $result_records[$record_ID]->category_title; ?></nobr></div>
										<div class="search_call_number"><nobr><?= implode(" ", explode(",", $result_records[$record_ID]->call_number)); ?></nobr></div>
										<?
											if($result_records[$record_ID]->lost_datetime != $db->blank_datetime)
											{
												?>
												<div class="search_status_lost"><nobr>Item Lost</nobr></div>
												<?
											}
											else
											{
												if($result_records[$record_ID]->{$db->field_checkout_ID})
												{
													?>
													<div class="search_status_checkedout"><nobr>Checked-Out</nobr></div>
													<?
												}
												else
												{
													if($result_records[$record_ID]->barcode > 0)
													{
														if($result_records[$record_ID]->allow_checkout == "Y")
														{
															?>
															<div class="search_status_available"><nobr>Available</nobr></div>
															<?
														}
														else
														{
															?>
															<div class="search_status_restricted"><nobr>Restricted</nobr></div>
															<?
														}; // end if
													}
													else
													{
														?>
														<div class="search_status_restricted"><nobr>Not Yet Processed</nobr></div>
														<?
													}; // end if
												}; // end if
											}; // end if
										?>
									</td>
								</tr>
							</table>
						</div>
						<?

					}; // end foreach
				?>
				</div>
			</td>
			<?
				if(count($search_words) > 0)
				{
					ob_start();
					?>
					<td width="30%" valign="top" style="padding-left:20px;">
						<?
							$fields = array(
								"`" . $db->table_author . "`.`first_name`",
								"`" . $db->table_author . "`.`middle_name`",
								"`" . $db->table_author . "`.`last_name`",
							);
							$where_query_sql = array();
							foreach($search_words as $word)
							{
								$where_query_sql[] = implode(" LIKE '%" . $word . "%' OR ", $fields) . " LIKE '%" . $word . "%'";
							}; // end foreach

							$author_sql = "
								SELECT
									*
								FROM
									`" . $db->table_author . "`
								WHERE
									(" . implode(") AND (", $where_query_sql) . ")
							";
							$author_result = mysql_query($author_sql, $mysql->link);
							$authors_found = mysql_num_rows($author_result);
							if($authors_found > 0)
							{
								if($db->is_librarian_account)
								{
									?>
									<script language="javascript">
										var selected_authors = new Array();
									</script>
									<?
								}; // end if
								?>
								<div class="search_results"><?= $authors_found; ?> author<?= ($authors_found != 1 ? "s" : ""); ?> found</div>
								<div class="search_listing">
								<?
								$item_counter = 0;
								while($author_record = mysql_fetch_object($author_result))
								{
									$item_counter++;
									?>
									<div class="search_item_<?= ($item_counter %2 ? "a" : "b"); ?>" id="author_<?= $author_record->ID; ?>">
										<table cellspacing="0" cellpadding="0" border="0" width="100%">
											<tr>
												<?
													if($db->is_librarian_account)
													{
														?>
														<td class="search_checkbox" valign="top" width="0%">
															<input type="checkbox" onclick="selected_authors[<?= $item_counter; ?>]=(this.checked ? <?= $author_record->ID; ?> : '');update_author_actions(this);">
														</td>
														<?
													}; // end if
												?>
												<td valign="top" width="100%">
													<a href="<?= $db->url_root; ?>/search.php/author/<?= $author_record->ID; ?>"><b><?= highlight_search_query(build_author_name($author_record, TRUE, FALSE), $search_words); ?></b></a>
												</td>
											</tr>
										</table>
									</div>
									<?
								}; // end while
								?>
									<div id="author_tools" class="search_actions"><button onclick="merge_selected_authors();">Merge Selected Authors</button></div>
								</div>
								<?

								if($db->is_librarian_account)
								{
									?>
									<script language="javascript">
										var author_IDs;
										var primary_author = 0;
										var old_primary_author = 0;
										function update_author_actions(current_author)
										{
											author_IDs = new Array();
											var primary_author = 0;
											for(author in selected_authors)
											{
												if(selected_authors[author] > 0)
												{
													author_IDs[author_IDs.length] = selected_authors[author];
												}; // end if
											}; // end foreach
											if(author_IDs.length < 1 && old_primary_author > 0)
											{
												document.getElementById('author_' + old_primary_author).className = 'search_item_a';
											}; // end if

											if(author_IDs.length == 1)
											{
												primary_author = author_IDs[0];
												document.getElementById('author_' + author_IDs[0]).className = 'search_item_h';
												old_primary_author = primary_author;
											}; // end if

											if(author_IDs.length == 1 || author_IDs.length == 2)
											{
											}; // end if

											if(author_IDs.length == 2)
											{
												document.getElementById('author_tools').style.display = 'block';
											}
											else
											{
												document.getElementById('author_tools').style.display = 'none';
											}; // end if
										}; // end function
										function merge_selected_authors()
										{
											if(confirm("The FIRST author you selected will be the PRIMARY author.\n\nWould you like to merge them now?"))
											{
												alert(author_IDs);
											}; // end if
										}; // end function
									</script>
									<?
								}; // end if
							}; // end if

						?>
						<?
							$fields = array(
								"`" . $db->table_library_series . "`.`title`",
							);
							$where_query_sql = array();
							foreach($search_words as $word)
							{
								$where_query_sql[] = implode(" LIKE '%" . $word . "%' OR ", $fields) . " LIKE '%" . $word . "%'";
							}; // end foreach

							$series_sql = "
								SELECT
									*
								FROM
									`" . $db->table_library_series . "`
								WHERE
									(" . implode(") AND (", $where_query_sql) . ")
							";
							$series_result = mysql_query($series_sql, $mysql->link);
							$series_found = mysql_num_rows($series_result);
							if($series_found > 0)
							{
								?>
								<div class="search_results"><?= $series_found; ?> series found</div>
								<div class="search_listing">
								<?
								$item_counter = 0;
								while($series_record = mysql_fetch_object($series_result))
								{
									$item_counter++;
									?>
									<div class="search_item_<?= ($item_counter %2 ? "a" : "b"); ?>">
										<a href="<?= $db->url_root; ?>/search.php/series/<?= $series_record->ID; ?>"><b><?= highlight_search_query($series_record->title, $search_words); ?></b></a>
									</div>
									<?
								}; // end while
								?>
								</div>
								<?
							}; // end if
						?>
						<?
							$fields = array(
								"`" . $db->table_subject . "`.`title`",
							);
							$where_query_sql = array();
							foreach($search_words as $word)
							{
								$where_query_sql[] = implode(" LIKE '%" . $word . "%' OR ", $fields) . " LIKE '%" . $word . "%'";
							}; // end foreach

							/*
							$subject_sql = "
								SELECT
									*
								FROM
									`" . $db->table_subject . "`
								WHERE
									(" . implode(") AND (", $where_query_sql) . ")
							";
							$subject_result = mysql_query($subject_sql, $mysql->link);
							$subjects_found = mysql_num_rows($subject_result);
							*/
							if($subjects_found > 0 && FALSE)
							{
								?>
								<div class="search_results"><?= $subjects_found; ?> subject<?= ($subjects_found != 1 ? "s" : ""); ?> found</div>
								<div class="search_listing">
								<?
								$item_counter = 0;
								while($subject_record = mysql_fetch_object($subject_result))
								{
									$item_counter++;
									?>
									<div class="search_item_<?= ($item_counter %2 ? "a" : "b"); ?>">
										<a href="<?= $db->url_root; ?>/search.php/subject/<?= $subject_record->ID; ?>"><b><?= highlight_search_query($subject_record->title, $search_words); ?></b></a>
									</div>
									<?
								}; // end while
								?>
								</div>
								<?
							}; // end if
						?>
						<?
							$fields = array(
								"`" . $db->table_publisher . "`.`title`",
							);
							$where_query_sql = array();
							foreach($search_words as $word)
							{
								$where_query_sql[] = implode(" LIKE '%" . $word . "%' OR ", $fields) . " LIKE '%" . $word . "%'";
							}; // end foreach

							$publisher_sql = "
								SELECT
									*
								FROM
									`" . $db->table_publisher . "`
								WHERE
									(" . implode(") AND (", $where_query_sql) . ")
							";
							$publisher_result = mysql_query($publisher_sql, $mysql->link);
							$publishers_found = mysql_num_rows($publisher_result);
							if($publishers_found > 0)
							{
								?>
								<div class="search_results"><?= $publishers_found; ?> publisher<?= ($publishers_found != 1 ? "s" : ""); ?> found</div>
								<div class="search_listing">
								<?
								$item_counter = 0;
								while($publisher_record = mysql_fetch_object($publisher_result))
								{
									$item_counter++;
									?>
									<div class="search_item_<?= ($item_counter %2 ? "a" : "b"); ?>">
										<a href="<?= $db->url_root; ?>/search.php/publisher/<?= $publisher_record->ID; ?>"><b><?= highlight_search_query($publisher_record->title, $search_words); ?></b></a>
									</div>
									<?
								}; // end while
								?>
								</div>
								<?
							}; // end if
						?>
					</td>
					<?
					if($authors_found > 0 || $subjects_found > 0 || $series_found > 0 || $publishers_found > 0)
					{
						ob_end_flush();
					}
					else
					{
						ob_end_clean();
					}; // end if
				}; // end if
			?>
		</tr>
	</table>
	<?
}; // end if



include_once(dirname(__FILE__) . "/bottom.php");

?>
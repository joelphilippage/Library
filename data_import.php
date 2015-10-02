<?

exit("THIS IS DANGEROUS CODE - DON'T RUN IT UNLESS YOU WANT TO LOSE A LOT OF WORK");

include_once(dirname(__FILE__) . "/first.php");



$data = file("items.xml");
$line_number = 0;
$all_done = FALSE;


?><pre><?

while(! $all_done)
{
	set_time_limit(30);

	$item_data = array();
	$done = FALSE;

	while(! $done)
	{
		$line_data = trim($data[$line_number]);
		$data_type = trim(strtoupper(preg_replace("/(.*<\/)(.*)(>)/", "\\2", " " . $line_data)));
		$data_value = trim(preg_replace("/\s+/", " ", preg_replace("/(.*>)(.*)(<.*)/", "\\2", " " . $line_data)));

		switch($data_type)
		{
			case "":
			case "<XML>":
			case "<ITEM>":
			case "ITEM":
				break;
			case "ITEMID":
				$data_value = intval($data_value);
			default:
				$item_data[$data_type] = preg_replace("/&amp;/", "&", $data_value);
				break;
		}; // end switch

		if(strtolower($line_data) == "</item>")
		{
			$done = TRUE;
		}; // end if

		if($line_number >= count($data))
		{
			$all_done = TRUE;
			$done = TRUE;
		}; // end if

		$line_number++;

	}; // end while

	if(! $all_done)
	{
		$sql = "
				`" . $db->table_library_item . "`
			SET";
		if($item_data['ITEMID'] > 0)
		{
			$sql .= "
					`" . $db->field_old_item_ID . "`='" . mysql_escape_string($item_data['ITEMID']) . "',";
		}; // end if
		if($item_data['TITLE'] != "")
		{
			$sorting_title = preg_replace("/^(The|A)\s/", "", $item_data['TITLE']);

			$sql .= "
					`title`='" . mysql_escape_string($item_data['TITLE']) . "',
					`sorting_title`='" . mysql_escape_string($sorting_title) . "',";
		}; // end if
		if($item_data['COST'] != "" && $item_data['COST'] != "$")
		{
			$sql .= "
					`cost`='" . mysql_escape_string(trim(trim($item_data['COST'], "$"))) . "',";
		}; // end if
		if($item_data['ENTRYDATE'] != "")
		{
			$sql .= "
					`created_datetime`='" . mysql_escape_string(substr($item_data['ENTRYDATE'], 6, 4) . "-" . substr($item_data['ENTRYDATE'], 0, 2) . "-" . substr($item_data['ENTRYDATE'], 3, 2)) . " 00:00:00',";
		}; // end if
		if($item_data['COPYRIGHT'] != "")
		{
			$sql .= "
					`copyright_year`='" . mysql_escape_string($item_data['COPYRIGHT']) . "',";
		}; // end if
		if($item_data['COMMENT'] != "")
		{
			$sql .= "
					`comment`='" . mysql_escape_string($item_data['COMMENT']) . "',";
		}; // end if
		if($item_data['TECHNICALDESCRIPTION'] != "")
		{
			$sql .= "
					`length`='" . mysql_escape_string(intval($item_data['TECHNICALDESCRIPTION'])) . "',";
		}; // end if
		if($item_data['SOURCE'] != "")
		{
			$sql .= "
					`source`='" . mysql_escape_string($item_data['SOURCE']) . "',";
		}; // end if

		$the_call_numbers = array();
		if($item_data['CALLNUMBERLINE1'] != "")
		{
			$the_call_numbers[] = $item_data['CALLNUMBERLINE1'];
		}; // end if
		if($item_data['CALLNUMBERLINE2'] != "")
		{
			$the_call_numbers[] = $item_data['CALLNUMBERLINE2'];
		}; // end if
		if($item_data['CALLNUMBERLINE3'] != "")
		{
			$the_call_numbers[] = $item_data['CALLNUMBERLINE3'];
		}; // end if
		if($item_data['CALLNUMBERLINE4'] != "")
		{
			$the_call_numbers[] = $item_data['CALLNUMBERLINE4'];
		}; // end if
		if($item_data['CALLNUMBERLINE5'] != "")
		{
			$the_call_numbers[] = $item_data['CALLNUMBERLINE5'];
		}; // end if
		if($item_data['CALLNUMBERLINE6'] != "")
		{
			$the_call_numbers[] = $item_data['CALLNUMBERLINE6'];
		}; // end if

		if(count($the_call_numbers) > 0)
		{
			$sql .= "
					`call_number`='" . mysql_escape_string(implode(",", $the_call_numbers)) . "',";
		}; // end if

		if($item_data['EDITION'] != "")
		{
			$sql .= "
					`edition`='" . mysql_escape_string($item_data['EDITION']) . "',";
		}; // end if

		if($item_data['DIMENSIONS'] != "")
		{
			$sql .= "
					`dimensions`='" . mysql_escape_string($item_data['DIMENSIONS']) . "',";
		}; // end if

		if($item_data['SUMMARY'] != "")
		{
			$sql .= "
					`summary`='" . mysql_escape_string($item_data['SUMMARY']) . "',";
		}; // end if

		if($item_data['ISBN'] != "")
		{
			$sql .= "
					`isbn`='" . mysql_escape_string($item_data['ISBN']) . "',";
		}; // end if

		if($item_data['LCCONTROLNUMBER'] != "")
		{
			$sql .= "
					`lc_control_number`='" . mysql_escape_string($item_data['LCCONTROLNUMBER']) . "',";
		}; // end if

		if($item_data['PURCHASEDATE'] != "")
		{
			$sql .= "
					`purchase_date`='" . mysql_escape_string(substr($item_data['PURCHASEDATE'], 6, 4) . "-" . substr($item_data['PURCHASEDATE'], 0, 2) . "-" . substr($item_data['PURCHASEDATE'], 3, 2)) . "',";
		}; // end if
		if($item_data['PARALLELTITLE'] != "")
		{
			$sql .= "
					`parallel_title`='" . mysql_escape_string($item_data['PARALLELTITLE']) . "',";
		}; // end if
		if($item_data['NOTES'] != "")
		{
			$sql .= "
					`notes`='" . mysql_escape_string($item_data['NOTES']) . "',";
		}; // end if
		if($item_data['STMTOFRESP'] != "")
		{
			$sql .= "
					`title_notes`='" . mysql_escape_string($item_data['STMTOFRESP']) . "',";
		}; // end if
		if($item_data['AUTHORANALYTICS'] != "")
		{
			$sql .= "
					`analytics_author`='" . mysql_escape_string($item_data['AUTHORANALYTICS']) . "',";
		}; // end if
		if($item_data['SUBJECTANALYTICS'] != "")
		{
			$sql .= "
					`analytics_subject`='" . mysql_escape_string($item_data['SUBJECTANALYTICS']) . "',";
		}; // end if
		if($item_data['TITLEANALYTICS'] != "")
		{
			$sql .= "
					`analytics_title`='" . mysql_escape_string($item_data['TITLEANALYTICS']) . "',";
		}; // end if

		switch($item_data['LASTUPDATEBY'])
		{
			case "BJV": // Barbara Valdois
				$sql .= "
					`" . $db->field_updated_account_ID . "`='168',";
				break;
			case "RLT": // Unknown (2 records)
			case "DKN": // Debbie Nininger
				$sql .= "
					`" . $db->field_updated_account_ID . "`='102',";
				break;
			case "AMN": // Andrew Nelson
				$sql .= "
					`" . $db->field_updated_account_ID . "`='1',";
				break;
		}; // end switch

		$item_data['SERIESTITLE'] = trim(trim(preg_replace("/\d*$/", "", $item_data['SERIESTITLE']), "#"));
		$item_data['SERIESTITLE'] = trim(preg_replace("/(volume|vol|vol\.)/", "", $item_data['SERIESTITLE']));
		if($item_data['SERIESTITLE'] != "")
		{
			$lookup_sql = "
				SELECT
					*
				FROM
					`" . $db->table_library_series . "`
				WHERE
					`title`='" . mysql_escape_string($item_data['SERIESTITLE']) . "'
			";
			$lookup_result = mysql_query($lookup_sql, $mysql->link);
			if(mysql_num_rows($lookup_result) > 0)
			{
				$lookup_record = mysql_fetch_object($lookup_result);
				$sql .= "
					`" . $db->field_library_series_ID . "`='" . mysql_escape_string($lookup_record->ID) . "',";
			}
			else
			{
				$insert_sql = "
					INSERT INTO
						`" . $db->table_library_series . "`
					SET
						`title`='" . mysql_escape_string($item_data['SERIESTITLE']) . "'
				";
				//echo $insert_sql . "<br>";
				mysql_query($insert_sql, $mysql->link);
				$record_ID = mysql_insert_id($mysql->link);
				$sql .= "
					`" . $db->field_library_series_ID . "`='" . mysql_escape_string($record_ID) . "',";
			}; // end if
		}; // end if

		if($item_data['REPORTCLASS'] != "")
		{
			$lookup_sql = "
				SELECT
					*
				FROM
					`" . $db->table_category . "`
				WHERE
					`title`='" . mysql_escape_string($item_data['REPORTCLASS']) . "'
			";
			$lookup_result = mysql_query($lookup_sql, $mysql->link);
			if(mysql_num_rows($lookup_result) > 0)
			{
				$lookup_record = mysql_fetch_object($lookup_result);
				$sql .= "
					`" . $db->field_category_ID . "`='" . mysql_escape_string($lookup_record->ID) . "',";
			}
			else
			{
				$insert_sql = "
					INSERT INTO
						`" . $db->table_category . "`
					SET
						`title`='" . mysql_escape_string($item_data['REPORTCLASS']) . "'
				";
				//echo $insert_sql . "<br>";
				mysql_query($insert_sql, $mysql->link);
				$record_ID = mysql_insert_id($mysql->link);
				$sql .= "
					`" . $db->field_category_ID . "`='" . mysql_escape_string($record_ID) . "',";
			}; // end if
		}; // end if

		if($item_data['CIRCULATIONTYPE'] != "")
		{
			$lookup_sql = "
				SELECT
					*
				FROM
					`" . $db->table_circulation . "`
				WHERE
					`title`='" . mysql_escape_string($item_data['CIRCULATIONTYPE']) . "'
			";
			$lookup_result = mysql_query($lookup_sql, $mysql->link);
			if(mysql_num_rows($lookup_result) > 0)
			{
				$lookup_record = mysql_fetch_object($lookup_result);
				$sql .= "
					`" . $db->field_circulation_ID . "`='" . mysql_escape_string($lookup_record->ID) . "',";
			}
			else
			{
				$insert_sql = "
					INSERT INTO
						`" . $db->table_circulation . "`
					SET
						`title`='" . mysql_escape_string($item_data['CIRCULATIONTYPE']) . "'
				";
				//echo $insert_sql . "<br>";
				mysql_query($insert_sql, $mysql->link);
				$record_ID = mysql_insert_id($mysql->link);
				$sql .= "
					`" . $db->field_circulation_ID . "`='" . mysql_escape_string($record_ID) . "',";
			}; // end if
		}; // end if

		if($item_data['MATERIALTYPE'] != "")
		{
			$lookup_sql = "
				SELECT
					*
				FROM
					`" . $db->table_library_type . "`
				WHERE
					`title`='" . mysql_escape_string($item_data['MATERIALTYPE']) . "'
			";
			$lookup_result = mysql_query($lookup_sql, $mysql->link);
			if(mysql_num_rows($lookup_result) > 0)
			{
				$lookup_record = mysql_fetch_object($lookup_result);
				$sql .= "
					`" . $db->field_library_type_ID . "`='" . mysql_escape_string($lookup_record->ID) . "',";
			}
			else
			{
				$insert_sql = "
					INSERT INTO
						`" . $db->table_library_type . "`
					SET
						`title`='" . mysql_escape_string($item_data['MATERIALTYPE']) . "'
				";
				//echo $insert_sql . "<br>";
				mysql_query($insert_sql, $mysql->link);
				$record_ID = mysql_insert_id($mysql->link);
				$sql .= "
					`" . $db->field_library_type_ID . "`='" . mysql_escape_string($record_ID) . "',";
			}; // end if
		}; // end if

		if($item_data['AGEGROUP'] != "")
		{
			$lookup_sql = "
				SELECT
					*
				FROM
					`" . $db->table_age . "`
				WHERE
					`title`='" . mysql_escape_string($item_data['AGEGROUP']) . "'
			";
			$lookup_result = mysql_query($lookup_sql, $mysql->link);
			if(mysql_num_rows($lookup_result) > 0)
			{
				$lookup_record = mysql_fetch_object($lookup_result);
				$sql .= "
					`" . $db->field_age_ID . "`='" . mysql_escape_string($lookup_record->ID) . "',";
			}
			else
			{
				$insert_sql = "
					INSERT INTO
						`" . $db->table_age . "`
					SET
						`title`='" . mysql_escape_string($item_data['AGEGROUP']) . "'
				";
				//echo $insert_sql . "<br>";
				mysql_query($insert_sql, $mysql->link);
				$record_ID = mysql_insert_id($mysql->link);
				$sql .= "
					`" . $db->field_age_ID . "`='" . mysql_escape_string($record_ID) . "',";
			}; // end if
		}; // end if

		if($item_data['LOCATION'] != "")
		{
			$lookup_sql = "
				SELECT
					*
				FROM
					`" . $db->table_location . "`
				WHERE
					`title`='" . mysql_escape_string($item_data['LOCATION']) . "'
			";
			$lookup_result = mysql_query($lookup_sql, $mysql->link);
			if(mysql_num_rows($lookup_result) > 0)
			{
				$lookup_record = mysql_fetch_object($lookup_result);
				$sql .= "
					`" . $db->field_location_ID . "`='" . mysql_escape_string($lookup_record->ID) . "',";
			}
			else
			{
				$insert_sql = "
					INSERT INTO
						`" . $db->table_location . "`
					SET
						`title`='" . mysql_escape_string($item_data['LOCATION']) . "'
				";
				//echo $insert_sql . "<br>";
				mysql_query($insert_sql, $mysql->link);
				$record_ID = mysql_insert_id($mysql->link);
				$sql .= "
					`" . $db->field_location_ID . "`='" . mysql_escape_string($record_ID) . "',";
			}; // end if
		}; // end if

		if($item_data['PUBLISHER'] != "")
		{
			$lookup_sql = "
				SELECT
					*
				FROM
					`" . $db->table_publisher . "`
				WHERE
					`title`='" . mysql_escape_string($item_data['PUBLISHER']) . "'
			";
			$lookup_result = mysql_query($lookup_sql, $mysql->link);
			if(mysql_num_rows($lookup_result) > 0)
			{
				$lookup_record = mysql_fetch_object($lookup_result);
				$sql .= "
					`" . $db->field_publisher_ID . "`='" . mysql_escape_string($lookup_record->ID) . "',";
			}
			else
			{
				$insert_sql = "
					INSERT INTO
						`" . $db->table_publisher . "`
					SET
						`title`='" . mysql_escape_string($item_data['PUBLISHER']) . "',
						`location`='" . mysql_escape_string($item_data['PLACEOFPUBLICATION']) . "'
				";
				//echo $insert_sql . "<br>";
				mysql_query($insert_sql, $mysql->link);
				$record_ID = mysql_insert_id($mysql->link);
				$sql .= "
					`" . $db->field_publisher_ID . "`='" . mysql_escape_string($record_ID) . "',";
			}; // end if
		}; // end if

		$linked_author_ID = 0;
		if($item_data['AUTHORSFIRSTNAME'] . $item_data['AUTHORSLASTNAME']  != "")
		{
			$author_first_names = preg_replace("/\/.*/", "", $item_data['AUTHORSFIRSTNAME']);
			$author_first_names = explode(" ", preg_replace("/\s+/", " ", preg_replace("/(\.|[0-9]|-|,)/", " ", $author_first_names)));
			if(count($author_first_names) > 1)
			{
				$author_first_name = trim($author_first_names[0]);
				$author_middle_name = trim(trim($author_first_names[1]) . " " . trim($author_first_names[2]) . " " . trim($author_first_names[3]));
			}
			else
			{
				$author_first_name = $item_data['AUTHORSFIRSTNAME'];
				$author_middle_name = "";
			}; // end if
			//echo $item_data['AUTHORSLASTNAME'] . "::" . $author_first_name . "::" . $author_middle_name . " ~~~ " . $item_data['AUTHORSFIRSTNAME'] . "<br>";

			$lookup_sql = "
				SELECT
					*
				FROM
					`" . $db->table_author . "`
				WHERE
					`first_name`='" . mysql_escape_string($author_first_name) . "'
					AND `middle_name`='" . mysql_escape_string($author_middle_name) . "'
					AND `last_name`='" . mysql_escape_string($item_data['AUTHORSLASTNAME']) . "'
			";
			$lookup_result = mysql_query($lookup_sql, $mysql->link);
			if(mysql_num_rows($lookup_result) > 0)
			{
				$lookup_record = mysql_fetch_object($lookup_result);
				$linked_author_ID = $lookup_record->ID;
			}
			else
			{
				$insert_sql = "
					INSERT INTO
						`" . $db->table_author . "`
					SET
						`first_name`='" . mysql_escape_string($author_first_name) . "',
						`middle_name`='" . mysql_escape_string($author_middle_name) . "',
						`last_name`='" . mysql_escape_string($item_data['AUTHORSLASTNAME']) . "'
				";
				//echo $insert_sql . "<br>";
				mysql_query($insert_sql, $mysql->link);
				$linked_author_ID = mysql_insert_id($mysql->link);
			}; // end if
		}; // end if

		$sql .= "
					`author_ID`='" . mysql_escape_string($linked_author_ID) . "',";



		$linked_artist_ID = 0;
		if($item_data['ARTIST']  != "")
		{
			$artist_name = explode(",", trim(preg_replace("/(, ill\.|, ill|, illus.|, illus)$/", "", $item_data['ARTIST'])));
			$artist_last_name = $artist_name[0];
			if(count($artist_name) == 1)
			{
				$artist_first_name = "";
				$artist_middle_name = "";
			}
			else
			{
				$artist_first_names = preg_replace("/\/.*/", "", $artist_name[1]);
				$artist_first_names = explode(" ", preg_replace("/\s+/", " ", preg_replace("/(\.|[0-9]|-|,)/", " ", $artist_first_names)));
				if(count($artist_first_names) > 1)
				{
					$artist_first_name = trim($artist_first_names[0]);
					$artist_middle_name = trim(trim($artist_first_names[1]) . " " . trim($artist_first_names[2]) . " " . trim($artist_first_names[3]));
				}
				else
				{
					$artist_first_name = $artist_name[1];
					$artist_middle_name = "";
				}; // end if
				//echo $artist_name[0] . "::" . $artist_first_name . "::" . $artist_middle_name . " ~~~ " . $artist_name[1] . "<br>";

			}; // end if

			$lookup_sql = "
				SELECT
					*
				FROM
					`" . $db->table_author . "`
				WHERE
					`first_name`='" . mysql_escape_string($artist_first_name) . "'
					AND `middle_name`='" . mysql_escape_string($artist_middle_name) . "'
					AND `last_name`='" . mysql_escape_string($artist_last_name) . "'
			";
			$lookup_result = mysql_query($lookup_sql, $mysql->link);
			if(mysql_num_rows($lookup_result) > 0)
			{
				$lookup_record = mysql_fetch_object($lookup_result);
				$linked_artist_ID = $lookup_record->ID;
			}
			else
			{
				$insert_sql = "
					INSERT INTO
						`" . $db->table_author . "`
					SET
						`first_name`='" . mysql_escape_string($artist_first_name) . "',
						`middle_name`='" . mysql_escape_string($artist_middle_name) . "',
						`last_name`='" . mysql_escape_string($artist_last_name) . "'
				";
				//echo $insert_sql . "<br>";
				mysql_query($insert_sql, $mysql->link);
				$linked_artist_ID = mysql_insert_id($mysql->link);
			}; // end if
		}; // end if

		$subject_IDs = array();
		if($item_data['SUBJECTHEADINGS']  != "")
		{
			$subjects = explode(",", preg_replace("(\\/|\\\\)", ",", $item_data['SUBJECTHEADINGS']));
			foreach($subjects as $subject)
			{
				$subject_title = trim($subject);
				
				$lookup_sql = "
					SELECT
						*
					FROM
						`" . $db->table_subject . "`
					WHERE
						`title`='" . mysql_escape_string($subject) . "'
				";
				$lookup_result = mysql_query($lookup_sql, $mysql->link);
				if(mysql_num_rows($lookup_result) > 0)
				{
					$lookup_record = mysql_fetch_object($lookup_result);
					$subject_IDs[] = $lookup_record->ID;
				}
				else
				{
					$insert_sql = "
						INSERT INTO
							`" . $db->table_subject . "`
						SET
							`title`='" . mysql_escape_string($subject) . "'
					";
					//echo $insert_sql . "<br>";
					mysql_query($insert_sql, $mysql->link);
					$subject_IDs[] = mysql_insert_id($mysql->link);
				}; // end if

			}; // end foreach
			//echo count($subjects) . "<br>";
		}; // end if



		$sql .= "
					`enabled`='Y'";

		$lookup_sql = "
			SELECT
				*
			FROM
				`" . $db->table_library_item . "`
			WHERE
				`" . $db->field_old_item_ID . "`='" . mysql_escape_string($item_data['ITEMID']) . "'
		";
		$lookup_result = mysql_query($lookup_sql, $mysql->link);
		if(mysql_num_rows($lookup_result) > 0)
		{
			$lookup_record = mysql_fetch_object($lookup_result);
			$item_ID = $lookup_record->ID;
			$sql = "
				UPDATE
			" . $sql . "
				WHERE
					`ID`='" . $item_ID . "'
			";
			mysql_query($sql, $mysql->link);
		}
		else
		{
			$sql = "
				INSERT INTO
			" . $sql;
			mysql_query($sql, $mysql->link);
			$item_ID = mysql_insert_id($mysql->link);
		}; // end if



		$clear_item_links_sql = "
			DELETE FROM
				`" . $db->table_library_item_link . "`
			WHERE
				`" . $db->field_library_item_ID . "`='" . $item_ID . "'
		";
		mysql_query($clear_item_links_sql, $mysql->link);


		if($linked_author_ID > 0)
		{
			$insert_sql = "
				INSERT INTO
				`" . $db->table_library_item_link . "`
				SET
					`" . $db->field_library_item_ID . "`='" . $item_ID . "',
					`record_ID`='" . $linked_author_ID . "',
					`table`='author',
					`type`='A'
			";
			mysql_query($insert_sql, $mysql->link);
		}; // end if

		if($linked_artist_ID > 0)
		{
			$insert_sql = "
				INSERT INTO
				`" . $db->table_library_item_link . "`
				SET
					`" . $db->field_library_item_ID . "`='" . $item_ID . "',
					`record_ID`='" . $linked_artist_ID . "',
					`table`='author',
					`type`='I'
			";
			mysql_query($insert_sql, $mysql->link);
		}; // end if

		foreach($subject_IDs as $subject_ID)
		{
			$insert_sql = "
				INSERT INTO
				`" . $db->table_library_item_link . "`
				SET
					`" . $db->field_library_item_ID . "`='" . $item_ID . "',
					`record_ID`='" . $subject_ID . "',
					`table`='subject',
					`type`='S'
			";
			mysql_query($insert_sql, $mysql->link);
		}; // end foreach

		//echo $sql . "<br><br>";
		echo $item_ID . ": " . $item_data['TITLE'] . "<br>";

	}; // end if
}; // end while


?></pre><?

?>
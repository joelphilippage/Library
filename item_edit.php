<?

include_once(dirname(__FILE__) . "/first.php");

$db->show_search = FALSE;

$item_ID = $query[0];
$special_instructions = $query[2];
$special_instructions2 = $query[3];

if(! $accounts->account_record->ID)
{
	exit_error("Not Signed In", "You must be signed into perform that action.");
}; // end if

if($accounts->account_record->librarian != "Y" && $accounts->account_record->admin != "Y")
{
	exit_error("Access Denied", "Only a librarian can perform that action.");
}; // end if

$is_item_copy = FALSE;
$duplicate_prefill_ID = 0;
$the_prefill_authors = array();



switch($item_ID)
{
	case "new": ////////////////////////////////////////////////////////////////////////////// NEW ITEM
		$item_ID = "";
		$prefill_ID = urldecode($query[1]);

		if($prefill_ID != "" && ! isset($_POST['action']))
		{
			$fields = explode("|", $prefill_ID);
			foreach($fields as $field)
			{
				$data = explode("=", $field);
				$_POST[$data[0]] = $data[1];
				if($data[0] == "title")
				{
					$_POST['prefill_query'] = $data[1];
				}; // end if
			}; // end foreach
		}; // end if
		break;
	case "newcopy": ////////////////////////////////////////////////////////////////////////////// NEW COPY OF ITEM
		$item_ID = "";
		$duplicate_type = "copy";
		$duplicate_prefill_ID = $query[1];
		$is_item_copy = TRUE;
		break;
	case "replacement": ////////////////////////////////////////////////////////////////////////////// DUPLICATE REPLACEMENT COPY
		$item_ID = "";
		$duplicate_type = "copy";
		$duplicate_prefill_ID = $query[1];
		break;
	case "newvolume": ////////////////////////////////////////////////////////////////////////////// NEW VOLUME IN SERIES
		$item_ID = "";
		$duplicate_type = "series";
		$duplicate_prefill_ID = $query[1];
		break;
	case "newbyauthors": ////////////////////////////////////////////////////////////////////////// NEW BOOK BY SAME AUTHORS
		$item_ID = "";
		$duplicate_type = "newbyauthors";
		$duplicate_prefill_ID = $query[1];
		break;
	case "newedition": ////////////////////////////////////////////////////////////////////////// NEW EDITION
		$item_ID = "";
		$duplicate_type = "newedition";
		$duplicate_prefill_ID = $query[1];
		break;
	case "newlookup": ////////////////////////////////////////////////////////////////////////// NEW EDITION
		$item_ID = "";
		$prefill_ID = $query[1];
		$duplicate_type = "newlookup";
		$duplicate_prefill_ID = "";
		break;
	default:
		break;
}; // end switch

if($item_ID != "")
{
	$submit_caption = "Save Changes";
}
else
{
	$submit_caption = "Add Item";
}; // end if

$the_subjects = array();


if($_POST['action'] == $submit_caption)
{
	$_POST['add_more_copies'] = (isset($_POST['add_more_copies']) == "Y" ? "Y" : "N");
	$_POST['allow_checkout'] = (isset($_POST['allow_checkout']) == "Y" ? "Y" : "N");
	if($_POST['subject_IDs'] != "")
	{
		$the_subjects = explode(",", $_POST['subject_IDs']);
	}; // end if

	if($_POST[$db->field_library_series_ID] < 1 || $_POST['series_number'] < 1)
	{
		if(trim($_POST['title']) == "")
		{
			$errors['title'] = TRUE;
		}; // end if
	}; // end if

	if(count($_POST['author_ID']) < 1)
	{
		$errors['authors'] = TRUE;
	}; // end if

	if(trim($_POST['type_ID']) < 1)
	{
		$errors['type_ID'] = TRUE;
	}; // end if

	if(trim($_POST['style_ID']) < 1)
	{
		$errors['style_ID'] = TRUE;
	}; // end if

	if(trim($_POST['age_ID']) < 1)
	{
		$errors['age_ID'] = TRUE;
	}; // end if

	if(trim($_POST['location_ID']) < 1)
	{
		$errors['location_ID'] = TRUE;
	}; // end if

	if(trim($_POST['category_ID']) < 1)
	{
		$errors['category_ID'] = TRUE;
	}; // end if

	if(trim($_POST['call_number']) == "")
	{
		$errors['call_number'] = TRUE;
	}; // end if

	if(substr(trim($_POST['call_number']), -1) == ",")
	{
		$errors['call_number'] = TRUE;
	}; // end if

	if(substr(trim($_POST['call_number']), 0, 1) == ",")
	{
		$errors['call_number'] = TRUE;
	}; // end if

	if(trim($_POST['barcode']) != "0" && trim($_POST['barcode']) != "")
	{
		if(strlen(trim($_POST['barcode'])) != $db->barcode_length)
		{
			$errors['barcode'] = "Invalid Barcode";
		}; // end if

		if(substr(trim($_POST['barcode']), 0, 1) != $db->barcode_item_prefix)
		{
			$errors['barcode'] = "Invalid Barcode";
		}; // end if

		$barcode_sql = "
			SELECT
				*
			FROM
				`" . $db->table_library_item . "`
			WHERE
				`barcode`='" . mysql_escape_string($_POST['barcode']) . "'
				" . ($item_ID != "" ? "AND `ID`!='" . $item_ID . "'" : "") . "
		";
		$barcode_result = mysql_query($barcode_sql, $mysql->link);
		if(mysql_num_rows($barcode_result) > 0)
		{
			$errors['barcode'] = "Barcode already in use";
		}; // end if
	}; // end if


	if($_POST['call_number'] != "")
	{
		$call_numbers = explode(",", $_POST['call_number']);
		$new_call_numbers = array();
		foreach($call_numbers as $val)
		{
			$new_call_numbers[] = trim($val);
		}; // end foreach
		$_POST['call_number'] = implode(",", $new_call_numbers);
	}; // end if

	$_POST['height'] = ceil($_POST['height']);

	if(count($errors) < 1)
	{
		if($_FILES['upload_cover_art']['type'] == "image/jpeg")
		{
			if($item_ID != "")
			{
				$image_sql = "
					SELECT
						*
					FROM
						`" . $db->table_library_image . "`
					WHERE
						`" . $db->field_library_item_ID . "`='" . mysql_escape_string($item_ID) . "'
						AND `type`='A'
						AND `enabled`='Y'
				";
				$image_result = mysql_query($image_sql, $mysql->link);
				if(mysql_num_rows($image_result) > 0)
				{
					$image_record = mysql_fetch_object($image_result);
					$cover_art_image_ID = $image_record->ID;
					echo $find_art_sql;
				}
				else
				{
					$insert_image_sql = "
						INSERT INTO
							`" . $db->table_library_image . "`
						SET
							`" . $db->field_library_item_ID . "`='" . mysql_escape_string($item_ID) . "',
							`type`='A'
					";
					mysql_query($insert_image_sql, $mysql->link);
					$cover_art_image_ID = mysql_insert_id($mysql->link);
				}; // end if

				reduce($_FILES['upload_cover_art']['tmp_name'], $db->cover_thumbs_image_dir . "/" . substr($cover_art_image_ID, 0, 1) . "/" . $cover_art_image_ID . ".jpg", 200, 200);
				copy($_FILES['upload_cover_art']['tmp_name'], $db->cover_image_dir . "/" . substr($cover_art_image_ID, 0, 1) . "/" . $cover_art_image_ID . ".jpg");
			}; // end if
		}; // end if

		$sorting_title = preg_replace("/^(The|A)\s/i", "", $_POST['title']);

		$update_sql = "
				`title`='" . mysql_escape_string(stripslashes($_POST['title'])) . "',
				`sorting_title`='" . mysql_escape_string(stripslashes($sorting_title)) . "',
				`author_ID`='" . $_POST['author_ID'][0] . "',
				`author_type_ID`='" . $_POST['author_type'][0] . "',
				`edition`='" . mysql_escape_string(stripslashes($_POST['edition'])) . "',
				`compilation`='" . (trim(implode("", $_POST['section_title'])) != "" ? "Y" : "N") . "',
				`extra_search_text`='" . mysql_escape_string(implode(", ", $_POST['section_title']) . ", " . implode(", ", $_POST['section_author'])) . "',
				`parallel_title`='" . mysql_escape_string(stripslashes($_POST['parallel_title'])) . "',
				`" . $db->field_library_series_ID . "`='" . mysql_escape_string($_POST[$db->field_library_series_ID]) . "',
				`series_number`='" . mysql_escape_string(stripslashes($_POST['series_number'])) . "',
				`notes`='" . mysql_escape_string(stripslashes($_POST['notes'])) . "',
				`" . $db->field_library_type_ID . "`='" . mysql_escape_string($_POST[$db->field_library_type_ID]) . "',
				`" . $db->field_style_ID . "`='" . mysql_escape_string($_POST[$db->field_style_ID]) . "',
				`summary`='" . mysql_escape_string(stripslashes($_POST['summary'])) . "',
				`" . $db->field_age_ID . "`='" . mysql_escape_string($_POST[$db->field_age_ID]) . "',
				`" . $db->field_category_ID . "`='" . mysql_escape_string($_POST[$db->field_category_ID]) . "',
				`" . $db->field_publisher_ID . "`='" . mysql_escape_string($_POST[$db->field_publisher_ID]) . "',
				`length`='" . mysql_escape_string(strtoupper(stripslashes($_POST['length']))) . "',
				`height`='" . mysql_escape_string(strtoupper(stripslashes($_POST['height']))) . "',
				`call_number`='" . mysql_escape_string(strtoupper(stripslashes($_POST['call_number']))) . "',
				`isbn`='" . mysql_escape_string(stripslashes($_POST['isbn'])) . "',
				`isbn13`='" . mysql_escape_string(stripslashes($_POST['isbn13'])) . "',
				`lc_control_number`='" . mysql_escape_string(stripslashes($_POST['lc_control_number'])) . "',
				`updated_account_ID`='" . $accounts->account_record->ID . "',
				`allow_checkout`='" . mysql_escape_string(stripslashes($_POST['allow_checkout'])) . "',
		";
		$unique_update_sql = "
				`" . $db->field_location_ID . "`='" . mysql_escape_string($_POST[$db->field_location_ID]) . "',
				`copy_number`='" . mysql_escape_string(stripslashes($_POST['copy_number'])) . "',
				`barcode`='" . mysql_escape_string(stripslashes($_POST['barcode'])) . "',
		";

		if($is_item_copy)
		{
			$update_duplicate_sql = "
				UPDATE
					`" . $db->table_library_item . "`
				SET
					`" . $db->field_library_copy_item_ID . "`='" . mysql_escape_string($duplicate_prefill_ID) . "'
				WHERE
					`ID`='" . mysql_escape_string($duplicate_prefill_ID) . "'
			";
			//echo $update_duplicate_sql . "<br><br>";
			mysql_query($update_duplicate_sql, $mysql->link);
			$update_sql .= "
				`" . $db->field_library_copy_item_ID . "`='" . mysql_escape_string($duplicate_prefill_ID) . "',
			";
		}; // end if

		$new_inserted_item = FALSE;
		if($item_ID == "")
		{
			$sql = "
				INSERT INTO
					`" . $db->table_library_item . "`
				SET
					" . $update_sql . $unique_update_sql . "
					`created_datetime`='" . date("Y-m-d H:i:s") . "'
			";
			//echo $sql . "<br><br>";
			//exit();
			mysql_query($sql, $mysql->link);
			$new_inserted_item = TRUE;
			$item_ID = mysql_insert_id($mysql->link);
		}
		else
		{
			$sql = "
				UPDATE
					`" . $db->table_library_item . "`
				SET
					" . $update_sql . $unique_update_sql . "
					`updated_datetime`='" . date("Y-m-d H:i:s") . "'
				WHERE
					`ID`='" . mysql_escape_string($item_ID) . "'
			";
			mysql_query($sql, $mysql->link);

			if($_POST[$db->field_library_copy_item_ID] > 0)
			{
				$sql = "
					UPDATE
						`" . $db->table_library_item . "`
					SET
						" . $update_sql . "
						`updated_datetime`='" . date("Y-m-d H:i:s") . "'
					WHERE
						`" . $db->field_library_copy_item_ID . "`='" . mysql_escape_string($_POST[$db->field_library_copy_item_ID]) . "'
				";
				mysql_query($sql, $mysql->link);
			}; // end if

		}; // end if
		//echo mysql_error($mysql->link);

		
		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//////// GET LIST OF ITEM COPIES THAT NEED TO BE UPDATED


		if($_POST[$db->field_library_copy_item_ID] > 0)
		{
			$copy_lookup_sql = "
				SELECT
					ID
				FROM
					`" . $db->table_library_item . "`
				WHERE
					`" . $db->field_library_copy_item_ID . "`='" . mysql_escape_string($_POST[$db->field_library_copy_item_ID]) . "'
			";
			$copy_lookup_result = mysql_query($copy_lookup_sql, $mysql->link);
			$copy_IDs = array();
			while($copy_lookup_record = mysql_fetch_object($copy_lookup_result))
			{
				$copy_IDs[] = $copy_lookup_record->ID;
			}; // end while
		}
		else
		{
			$copy_IDs = array($item_ID);
		}; // end if

		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// LOOP THROUGH COPIES

		foreach($copy_IDs as $copy_ID)
		{
			////////////////////////////////////////////////////////
			// DELETE AUTHOR AND SUBJECT LINKS FOR COPY

			$clear_authors_sql = "
				DELETE FROM
					`" . $db->table_library_item_link . "`
				WHERE
					`" . $db->field_library_item_ID . "`='" . mysql_escape_string($copy_ID) . "'
					AND (`" . $db->field_library_item_link_type_ID . "`='" . implode("' OR `" . $db->field_library_item_link_type_ID . "`='", $db->all_type_IDs) . "')
			";
			//echo $clear_authors_sql . "<br><br>";
			mysql_query($clear_authors_sql, $mysql->link);



			////////////////////////////////////////////////////////
			// CREATE AUTHOR LINKS FOR EACH COPY

			$priority = 0;
			for($a = 0; $a < count($_POST['author_ID']); $a++)
			{
				$author_ID = $_POST['author_ID'][$a];
				$author_type_ID = $_POST['author_type_ID'][$a];
				$link_author_sql = "
					INSERT INTO
						`" . $db->table_library_item_link . "`
					SET
						`" . $db->field_library_item_ID . "`='" . mysql_escape_string($copy_ID) . "',
						`record_ID`='" . mysql_escape_string($author_ID) . "',
						`" . $db->field_library_item_link_type_ID . "`='" . $author_type_ID . "',
						`section_title`='" . $_POST['section_title'][$a] . "',
						`section_start`='" . $_POST['section_start'][$a] . "',
						`priority`='" . $priority . "';
				";
				//echo $link_author_sql . "<br><br>";
				mysql_query($link_author_sql, $mysql->link);

				$priority++;
			}; // end for


			////////////////////////////////////////////////////////
			// CREATE SUBJECT LINKS FOR EACH COPY

			$priority = 0;
			foreach($the_subjects as $the_subject)
			{
				$link_subject_sql = "
					INSERT INTO
						`" . $db->table_library_item_link . "`
					SET
						`" . $db->field_library_item_ID . "`='" . mysql_escape_string($copy_ID) . "',
						`record_ID`='" . mysql_escape_string($the_subject) . "',
						`" . $db->field_library_item_link_type_ID . "`='" . $db->ID_item_link_type_subject . "',
						`priority`='" . $priority . "'
				";
				//echo $link_subject_sql . "<br><br>";
				mysql_query($link_subject_sql, $mysql->link);

				if($_POST[$db->field_library_copy_item_ID] > 0)
				{
					//exit("Need to update subjects of copies.");
				}; // end if

				$priority++;
			}; // end foreach

		}; // end foreach

		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// REMEMBER LAST USED VALUE FROM CERTAIN FIELDS

		if(! $is_item_copy && $new_inserted_item)
		{
			$db->settings->library_last_type_ID = $_POST[$db->field_library_type_ID];
			$db->settings->library_last_style_ID = $_POST[$db->field_style_ID];
			$db->settings->library_last_age_ID = $_POST[$db->field_age_ID];
			$db->settings->library_last_location_ID = $_POST[$db->field_location_ID];
			$db->settings->library_last_category_ID = $_POST[$db->field_category_ID];
			save_db_settings();
		}; // end if

		////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		if($_POST['add_more_copies'] == "Y")
		{
			header("location:" . $db->url_root . "/item_edit.php/newcopy/" . $_POST[$db->field_library_copy_item_ID] . "/" . $_POST[$db->field_location_ID] . ($_POST['add_more_copies'] == "Y" ? "/multiple" : ""));
		}
		else
		{
			header("location:" . $db->url_root . "/item_details.php/ID/" . $item_ID);
		}; // end if

		exit();
	}
	else
	{
		////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// LOAD AUTHOR DATA FROM SUBMITTED FORM

		$author_list = array();
		$priority = 0;
		for($a = 0; $a < count($_POST['author_ID']); $a++)
		{
			$author_ID = $_POST['author_ID'][$a];
			$author_sql = "
				SELECT
					*
				FROM
					`" . $db->table_author . "`
				WHERE
					`ID`='" . $author_ID . "'
			";
			$author_result = mysql_query($author_sql, $mysql->link);
			$author_record = mysql_fetch_object($author_result);

			$author_record->{$db->field_library_item_link_type_ID} = $_POST['author_type_ID'][$a];
			$author_record->section_title = $_POST['section_title'][$a];
			$author_record->section_start = $_POST['section_start'][$a];

			$author_list[] = $author_record;
		}; // end for

		////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	}; // end if
}
else
{
	if($item_ID != "")
	{
		$sql = "
			SELECT
				*
			FROM
				`" . $db->table_library_item . "`
			WHERE
				`ID`='" . $item_ID . "'
		";

		$result = mysql_query($sql, $mysql->link);

		if(mysql_num_rows($result) < 1)
		{
			exit_error("Item #" . $item_ID . " not found.", "There is no items on record matching ID #" . $item_ID);
		}; // end if

		$record = mysql_fetch_array($result);

		foreach($record as $key=>$val)
		{
			$_POST[$key] = addslashes($val);
		}; // end foreach

		if($_POST['barcode'] == "0")
		{
			$_POST['barcode'] = "";
		}; // end if

		if($_POST['height'] == "0" || $_POST['height'] == "0.0")
		{
			$_POST['height'] = "";
		}
		else
		{
			$_POST['height'] = floatval($_POST['height']);
		}; // end if
	}
	else
	{
		if($duplicate_prefill_ID > 0)
		{
			$duplicate_sql = "
				SELECT
					*
				FROM
					`" . $db->table_library_item . "`
				WHERE
					`ID`='" . mysql_escape_string($duplicate_prefill_ID) . "'
			";
			$duplicate_result = mysql_query($duplicate_sql, $mysql->link);
			if(mysql_num_rows($duplicate_result) > 0)
			{
				$duplicate_record = mysql_fetch_object($duplicate_result);

				foreach($duplicate_record as $key=>$val)
				{
					$_POST[$key] = $val;
				}; // end foreach

				switch($duplicate_type)
				{
					case "copy":
						$copy_sql = "
							SELECT
								*
							FROM
								`" . $db->table_library_item . "`
							WHERE
								`" . $db->field_library_copy_item_ID . "`='" . mysql_escape_string($duplicate_prefill_ID) . "'
						"; // Selecting all copies of the item even if deleted.
						$copy_result = mysql_query($copy_sql, $mysql->link);
						$_POST['copy_number'] = (mysql_num_rows($copy_result) > 0 ? mysql_num_rows($copy_result) + 1 : 2);
						if(! isset($_POST['add_more_copies']) && $special_instructions2 != "multiple")
						{
							$_POST['add_more_copies'] = "N";
						}
						else
						{
							$_POST['add_more_copies'] = "Y";
						}; // end if
						if($is_item_copy)
						{
							$_POST[$db->field_location_ID] = $special_instructions;
						}; // end if
						break;
					case "series":
						$_POST['title'] = "";
						$_POST['parallel_title'] = "";
						$_POST['notes'] = "";
						$_POST['copy_number'] = "1";
						if($_POST['series_number'] > 0)
						{
							$_POST['series_number']++;
						}; // end if
						$_POST['summary'] = "";
						$_POST['length'] = "";
						$_POST['isbn'] = "";
						$_POST['isbn13'] = "";
						$_POST['lc_control_number'] = "";
						$_POST['height'] = ceil($_POST['height']);
						break;
					case "newbyauthors":
						$_POST['title'] = "";
						$_POST['parallel_title'] = "";
						$_POST['notes'] = "";
						$_POST['copy_number'] = "1";
						$_POST['series_ID'] = "0";
						$_POST['series_number'] = "0";
						$_POST['summary'] = "";
						$_POST['isbn'] = "";
						$_POST['isbn13'] = "";
						$_POST['lc_control_number'] = "";
						break;
					case "newedition":
						$_POST['copy_number'] = "1";
						$_POST['series_ID'] = "0";
						$_POST['series_number'] = "0";
						$_POST['isbn'] = "";
						$_POST['isbn13'] = "";
						$_POST['lc_control_number'] = "";
						break;
				}; // end switch
				$_POST['barcode'] = "";
			}
			else
			{
				exit_error("Error Loading Original Record", "The item you wish to duplicate could not be found.");
			}; // end if
		}
		else
		{
			if($duplicate_type == "newlookup")
			{
				set_time_limit(30);
				$lookup_data = @simplexml_load_file("http://isbndb.com/api/books.xml?access_key=" . $db->isbndb_access_key . "&index1=book_id&results=details,texts,subjects,authors&value1=" . $prefill_ID . "");
				if(! $lookup_data)
				{
					exit_error("Lookup Server Down", "The ISBNDB.com data server is not responding.");
				}; // end if

				if($lookup_data->ErrorMessage != "")
				{
					exit_error("ISBNDB.com Error", $lookup_data->ErrorMessage . '<br><br><a href="javascript:" onclick="document.location.href=document.location.href;">Try again</a> in a few seconds.');
				}; // end if

				$book_data = $lookup_data->BookList->BookData;
				$book_attributes = $book_data->attributes();
				$book_details = $book_data->Details->attributes();
				$subjects = $book_data->Subjects->Subject;

				$dewey_guess = "";
				if($book_details->dewey_decimal_normalized != "")
				{
					$dewey_guess = preg_replace("/\..*/", "", $book_details->dewey_decimal_normalized);
				}
				else
				{
					if($book_details->dewey_decimal_normalized != "")
					{
						$dewey_guess = preg_replace("/\..*/", "", $book_details->dewey_decimal);
					}; // end if
				}; // end if

				$_POST['title'] = format_title($book_data->Title);
				$_POST['parallel_title'] = format_title(preg_replace("/^" . preg_quote($book_data->Title) . "(:\s|:|)/", "", $book_data->TitleLong));
				$_POST['summary'] = $book_data->Summary;
				$_POST['summary'] = preg_replace("/’/", "'", $_POST['summary']);

				$_POST['type_ID'] = $db->ID_type_book;
				$_POST['location_ID'] = $db->ID_location_main;

				$_POST['height'] = trim(preg_replace("/(.*)(\b\d+?\s*)(cm\b)(.*)/", "\\2", $book_details->physical_description_text));
				if(trim(strtolower($_POST['height'])) == trim(strtolower($book_details->physical_description_text)))
				{
					$_POST['height'] = "";
				}; // end if
				$_POST['length'] = trim(preg_replace("/(.*)(\b\d+?\s*)((pages|pgs|pg|p)\b)(.*)/", "\\2", $book_details->physical_description_text));
				if(trim(strtolower($_POST['length'])) == trim(strtolower($book_details->physical_description_text)))
				{
					$_POST['length'] = "";
				}; // end if

				$_POST['isbn'] = $book_attributes->isbn;
				$_POST['isbn13'] = $book_attributes->isbn13;
				$_POST['lc_control_number'] = $book_details->lcc_number;
				$_POST['barcode'] = $special_instructions;


				foreach($book_data->Subjects->Subject as $the_subject)
				{
					$subject_lookup_sql = "
						SELECT
							*
						FROM
							`" . $db->table_subject . "`
						WHERE
							(`title`='" . mysql_escape_string($the_subject[0]) . "')
							AND `enabled`='Y'
						LIMIT 1
					";
					$subject_lookup_result = mysql_query($subject_lookup_sql, $mysql->link);
					if(mysql_num_rows($subject_lookup_result) > 0)
					{
						$subject_lookup_record = mysql_fetch_object($subject_lookup_result);
						$the_subjects[] = $subject_lookup_record->ID;
					}
					else
					{
						$subject_insert_sql = "
							INSERT INTO
								`" . $db->table_subject . "`
							SET
								`title`='" . mysql_escape_string($the_subject[0]) . "',
								`enabled`='Y'
						";
						if(mysql_query($subject_insert_sql, $mysql->link))
						{
							$the_subjects[] = mysql_insert_id();
						}; // end if
					}; // end if
				}; // end foreach

				$first = TRUE;
				foreach($book_data->Authors->Person as $the_author)
				{
					$full_name = $the_author[0];
					$author_attributes = $the_author->attributes();

					set_time_limit(30);
					$author_data = simplexml_load_file("http://isbndb.com/api/authors.xml?access_key=" . $db->isbndb_access_key . "&index1=person_id&results=details&value1=" . $author_attributes->person_id . "");

					if($author_data->ErrorMessage != "")
					{
						exit_error("ISBNDB.com Error (Author)", $author_data->ErrorMessage . '<br><br><a href="javascript:" onclick="document.location.href=document.location.href;">Try again</a> in a few seconds.');
					}; // end if

					$author_info = $author_data->AuthorList->AuthorData->Details->attributes();

					//print_r($author_data);

					$author_lookup_sql = "
						SELECT
							*
						FROM
							`" . $db->table_author . "`
						WHERE
							`last_name`='" . mysql_escape_string($author_info->last_name) . "'
							AND `first_name`='" . mysql_escape_string($author_info->first_name) . "'
							AND `enabled`='Y'
						LIMIT 1
					";
					$author_lookup_result = mysql_query($author_lookup_sql, $mysql->link);
					if(mysql_num_rows($author_lookup_result) > 0)
					{
						$author_lookup_record = mysql_fetch_object($author_lookup_result);
						$the_prefill_authors[] = $author_lookup_record->ID;
						if($first)
						{
							$first_author_call_number_filler = substr($author_lookup_record->last_name, 0, 3);
						}; // end if
					}
					else
					{
						$author_insert_sql = "
							INSERT INTO
								`" . $db->table_author . "`
							SET
								`last_name`='" . mysql_escape_string(stripslashes($author_info->last_name)) . "',
								`first_name`='" . mysql_escape_string(stripslashes($author_info->first_name)) . "',
								`enabled`='Y'
						";
						if($first)
						{
							$first_author_call_number_filler = substr($author_info->last_name, 0, 3);
						}; // end if
						if(mysql_query($author_insert_sql, $mysql->link))
						{
							$the_prefill_authors[] = mysql_insert_id();
						}; // end if
					}; // end if
					$first = FALSE;
				}; // end foreach


				$publisher_attributes = $book_data->PublisherText->attributes();
				set_time_limit(30);
				$publisher_data = simplexml_load_file("http://isbndb.com/api/publishers.xml?access_key=" . $db->isbndb_access_key . "&index1=publisher_id&results=details&value1=" . $publisher_attributes->publisher_id . "");

				$publisher_name = $publisher_data->PublisherList->PublisherData->Name;
				if($publisher_data->PublisherList->PublisherData->Details)
				{
					$publisher_attributes = $publisher_data->PublisherList->PublisherData->Details->attributes();
					$publisher_location = $publisher_attributes->location;


					$publisher_lookup_sql = "
						SELECT
							*
						FROM
							`" . $db->table_publisher . "`
						WHERE
							`title`='" . mysql_escape_string($publisher_name) . "'
							AND `location`='" . mysql_escape_string($publisher_location) . "'
							AND `enabled`='Y'
						LIMIT 1
					";
					$publisher_lookup_result = mysql_query($publisher_lookup_sql, $mysql->link);
					if(mysql_num_rows($publisher_lookup_result) > 0)
					{
						$publisher_lookup_record = mysql_fetch_object($publisher_lookup_result);
						$_POST['publisher_ID'] = $publisher_lookup_record->ID;
					}
					else
					{
						$publisher_insert_sql = "
							INSERT INTO
								`" . $db->table_publisher . "`
							SET
								`title`='" . mysql_escape_string(stripslashes($publisher_name)) . "',
								`location`='" . mysql_escape_string(stripslashes($publisher_location)) . "',
								`enabled`='Y'
						";
						if(mysql_query($publisher_insert_sql, $mysql->link))
						{
							$_POST['publisher_ID'] = mysql_insert_id();
						}; // end if
					}; // end if
				}; // end if

				$_POST['call_number'] = preg_replace("/#/", strtoupper($first_author_call_number_filler), $db->category_defaults[$db->settings->library_last_category_ID]->call_number_prefix);
				if($dewey_guess != "")
				{
					$_POST['call_number'] = preg_replace("/\^/", strtoupper($dewey_guess), $_POST['call_number']);
				}; // end if
			}
			else
			{
				$_POST[$db->field_library_series_ID] = 0;
				$_POST['series_number'] = 0;
				$_POST['call_number'] = $db->category_defaults[$db->settings->library_last_category_ID]->call_number_prefix;
			}; // end if
			$_POST[$db->field_library_type_ID] = $db->settings->library_last_type_ID;
			$_POST[$db->field_style_ID] = $db->settings->library_last_style_ID;
			$_POST[$db->field_age_ID] = $db->settings->library_last_age_ID;
			$_POST[$db->field_location_ID] = $db->settings->library_last_location_ID;
			$_POST[$db->field_category_ID] = $db->settings->library_last_category_ID;
			$_POST['copy_number'] = 1;
			$_POST['allow_checkout'] = "Y";
		}; // end if
	}; // end if

	$author_list = array();
	if($item_ID == "" && $duplicate_prefill_ID == 0)
	{
		$author_counter = 0;
		foreach($the_prefill_authors as $key=>$val)
		{
			$author_counter++;
			$author_sql = "
				SELECT
					*
				FROM
					`" . $db->table_author . "`
				WHERE
					`ID`='" . $val . "'
			";
			$author_result = mysql_query($author_sql, $mysql->link);
			if(mysql_num_rows($author_result) > 0)
			{
				$author_record = mysql_fetch_object($author_result);
				$author_record->section_title = "";
				$author_record->section_start = "";
				$author_record->{$db->field_library_item_link_type_ID} = ($author_counter < 2 ? $db->ID_item_link_type_author : $db->ID_item_link_type_illustrator);
				$author_list[] = $author_record;
			}; // end if
		}; // end foreach
	}
	else
	{
		$author_sql = "
			SELECT
				`" . $db->table_author . "`.`ID` AS 'ID',
				`" . $db->table_author . "`.`last_name` AS 'last_name',
				`" . $db->table_author . "`.`first_name` AS 'first_name',
				`" . $db->table_author . "`.`middle_name` AS 'middle_name',
				`" . $db->table_library_item_link . "`.`" . $db->field_library_item_link_type_ID . "` AS '" . $db->field_library_item_link_type_ID . "',
				`" . $db->table_library_item_link . "`.`section_title` AS 'section_title',
				`" . $db->table_library_item_link . "`.`section_start` AS 'section_start'
			FROM
				`" . $db->table_library_item_link . "`,
				`" . $db->table_library_item_link_type . "`,
				`" . $db->table_author . "`
			WHERE
				`" . $db->table_library_item_link . "`.`record_ID`=`" . $db->table_author . "`.`ID`
				AND `" . $db->table_library_item_link . "`.`" . $db->field_library_item_link_type_ID . "`=`" . $db->table_library_item_link_type . "`.`ID`
				AND `" . $db->table_author . "`.`enabled`='Y'
				AND `" . $db->table_library_item_link . "`.`enabled`='Y'
				AND `" . $db->table_library_item_link_type . "`.`table`='author'
				AND `" . $db->table_library_item_link . "`.`" . $db->field_library_item_ID . "`='" . ($duplicate_prefill_ID > 0 ? $duplicate_prefill_ID : $item_ID) . "'
			ORDER BY
				`priority` ASC
		";
		$author_result = mysql_query($author_sql, $mysql->link);
		if(mysql_num_rows($author_result) > 0)
		{
			while($author_record = mysql_fetch_object($author_result))
			{
				$author_list[] = $author_record;
			}; // end while
		}; // end if
	}; // end if
}; // end if






include_once(dirname(__FILE__) . "/top.php");


if(count($errors) > 0)
{
	foreach($errors as $key=>$val)
	{
		echo $key . "::" . $val . ", ";
	}; // end foreach
}; // end if

?>
<script language="javascript">
	function set_text_field(field, text)
	{
		document.getElementById(field).value = text;
	}; // end function
	function add_item_type(letter)
	{
		event.returnValue = false;
		event.cancelBubble = true;
		var cn = document.getElementById('call_number').value;
		var close = cn.substring(cn.length - 1, cn.length)
		var open = cn.substring(cn.length - 3, cn.length - 2)
		
		if(open == "[" && close == "]")
		{
			ncn = cn.substring(0, cn.length - 4);
		}
		else
		{
			ncn = cn;
		}; // end if

		ncn = "" + ncn + ",[" + letter + "]";

		document.getElementById('call_number').value = ncn;
	}; // end function
</script>
<form enctype="multipart/form-data" method="post" action="" onsubmit="show_wait_message()">
<table class="edit_table" cellspacing="0" cellpadding="0" border="0">
	<?
		if($item_ID == "" && $duplicate_prefill_ID == 0)
		{
			?>
			<tr>
				<td class="edit_caption<?= ($errors['title'] ? " edit_error" : ""); ?>" style="padding:20px 0px 20px 0px;background-color:EEEEEE;"><nobr>Prefill Data:</nobr><div id="lookup_hide_button_div" style="display:none;"><input id="lookup_hide_button" type="button" value="Hide" onclick="lookup_hide();"></div></td>
				<td class="edit_value" style="padding:20px 0px 20px 0px;background-color:EEEEEE;">
					<script language="javascript">
						function lookup_done(found)
						{
							document.getElementById('prefill_message').style.display = 'none';
							document.getElementById('lookup_hide_button_div').style.display = 'block';
							if(! found)
							{
								document.getElementById('lookup_hide_button').focus();
							}; // end if
						}; // end function
						function lookup_hide()
						{
							document.getElementById('lookup_hide_button_div').style.display = 'none';
							document.getElementById('prefill_iframe').style.display = 'none';
							document.getElementById('prefill_message').style.display = 'none';
							document.getElementById('author_lookup_button').focus();
						}; // end function
						function lookup_data()
						{
							document.getElementById('lookup_hide_button_div').style.display = 'none';
							var d = new Date;
							var isbnfound = false;
							if(document.getElementById('prefill_query').value + 1 > 1 || document.getElementById('prefill_query').value.substring(document.getElementById('prefill_query').value.length - 1).toUpperCase() == "X")
							{
								if(document.getElementById('prefill_query').value.length == 8 && document.getElementById('prefill_query').value > 0)
								{
									document.location.href = "<?= $db->url_root; ?>/item_details.php/barcode/" + document.getElementById('prefill_query').value;
									return;
								}; // end if
								if(document.getElementById('prefill_query').value.length == 10)
								{
									isbnfound = true;
									document.getElementById('isbn').value =document.getElementById('prefill_query').value.toUpperCase();
								}; // end if
								if(document.getElementById('prefill_query').value.length == 13)
								{
									isbnfound = true;
									document.getElementById('isbn13').value =document.getElementById('prefill_query').value.toUpperCase();
								}; // end if
							}; // end if

							if(document.getElementById('prefill_type').value == "title" || ! isbnfound)
							{
								document.getElementById('title').value = document.getElementById('prefill_query').value;
							}; // end if

							document.getElementById('prefill_message').style.display = 'block';
							document.getElementById('prefill_iframe').style.display = 'block';
							document.getElementById('prefill_iframe').src = '<?= $db->url_root; ?>/lookup_data.php/' + document.getElementById('prefill_type').value + '/' + document.getElementById('prefill_query').value + "/" + d;
						}; // end function
						function do_isbndb_prefill(book_id)
						{
							show_wait_message();
							document.location.href = "<?= $db->url_root; ?>/item_edit.php/newlookup/" + book_id + "/<?= $_POST['barcode']; ?>";
						}; // end function
					</script>
					<select id="prefill_type">
						<option value="auto"<?= ($db->settings->library_last_lookup_type == "auto" ? " selected" : ""); ?>>Auto</option>
						<option value="isbn"<?= ($db->settings->library_last_lookup_type == "isbn" ? " selected" : ""); ?>>ISBN</option>
						<option value="title"<?= ($db->settings->library_last_lookup_type == "title" ? " selected" : ""); ?>>Title</option>
					</select>
					<input id="prefill_query" name="isbn_query" type="text" value="<?= $_POST['prefill_query']; ?>" size="30" onkeydown="if(event.keyCode==13){lookup_data();return false;};" autocomplete="off">
					<input id="prefill_button" type="button" onclick="lookup_data();" value="Search"><br>
					<div id="prefill_message" class="inline_wait_message">Searching...</div>
					<iframe id="prefill_iframe" style="width:600px;height:350px;margin-right:10px;display:none;border-style:none;" src="about:blank"></iframe>
					<script language="javascript">
						document.getElementById('prefill_query').focus();
						document.getElementById('prefill_query').select();
					</script>
				</td>
			</tr>
			<tr>
				<td colspan="2" style="padding-top:30px;border-style:solid;border-color:999999;border-width:1px 0px 0px 0px;"><td>
			</tr>
			<?
		}; // end if

		if($_POST[$db->field_library_copy_item_ID] > 0)
		{
			?>
			<tr style="<?= ($is_item_copy ? "display:none;" : ""); ?>">
				<td class="edit_caption"></td>
				<td class="edit_message">
					Editing the data below will effect all copies except for fields highlighted in yellow.
				</td>
			</tr>
			<?
		}; // end if
	?>
	<script language="javascript">
		function change_type()
		{
			switch(document.getElementById('<?= $db->field_library_type_ID; ?>').value)
			{
				case "1": // book
					break;
				case "2": // video
					break;
				case "3": // periodical
					break;
				case "4": // audio
					break;
				case "5": // software
					break;
				case "7": // map
					document.getElementById('<?= $db->field_style_ID; ?>').value = 11; // Style: Misc
					document.getElementById('style_field').style.display = "none";
					document.getElementById('style_title').style.display = "none";

					document.getElementById('<?= $db->field_age_ID; ?>').value = 2; // Age: Adult
					document.getElementById('age_field').style.display = "none";
					document.getElementById('age_title').style.display = "none";
					
					set_series(0, "");
					document.getElementById('series_row').style.display = "none";

					set_publisher(0, "");
					document.getElementById('publisher_row').style.display = "none";

					document.getElementById('parallel_title').value = "";
					document.getElementById('parallel_title_row').style.display = "none";

					document.getElementById('subjects_row').style.display = "none";

					document.getElementById('<?= $db->field_category_ID; ?>').value = 50; // Category: Maps

					document.getElementById('length').value = "";
					document.getElementById('height').value = "";
					document.getElementById('physical_description_row').style.display = "none";

					document.getElementById('isbn_field').style.display = "none";
					document.getElementById('isbn_title').style.display = "none";
					document.getElementById('isbn').value = "";

					document.getElementById('isbn13_field').style.display = "none";
					document.getElementById('isbn13_title').style.display = "none";
					document.getElementById('isbn13').value = "";
					
					document.getElementById('lc_control_number_field').style.display = "none";
					document.getElementById('lc_control_number_title').style.display = "none";
					document.getElementById('lc_control_number').value = "";

					document.getElementById('allow_checkout').checked = false;

					document.getElementById('edition_field').style.display = "none";
					document.getElementById('edition_title').style.display = "none";
					document.getElementById('copy_field').style.display = "none";
					document.getElementById('copy_title').style.display = "none";
					document.getElementById('title_title').innerHTML = "Continent, Country/Region, State/Province";
					break;
			}; // end switch
		}; // end function
	
	</script>
	<tr style="<?= ($is_item_copy ? "display:none;" : ""); ?>">
		<td class="edit_caption"><nobr>Type:</nobr></td>
		<td class="edit_value">
			<table cellspacing="0" cellpadding="0" border="0">
				<tr>
					<td>
						<?
							if($type_records = lookup_designation($db->table_library_type))
							{
								?>
								<select id="<?= $db->field_library_type_ID; ?>" name="<?= $db->field_library_type_ID; ?>" onchange="change_type();">
									<option value=""></option>
									<?
										foreach($type_records as $type_record)
										{
											?><option value="<?= $type_record->ID; ?>"<?= ($_POST[$db->field_library_type_ID] == $type_record->ID ? " selected" : ""); ?>><?= $type_record->title; ?></option><?
										}; // end foreach
									?>
								</select>
								<?
							}; // end if
						?>
					</td>
					<td id="style_field">
						<?
							if($style_records = lookup_designation($db->table_style))
							{
								?>
								<select id="<?= $db->field_style_ID; ?>" name="<?= $db->field_style_ID; ?>">
									<option value=""></option>
									<?
										foreach($style_records as $style_record)
										{
											if($_POST[$db->field_style_ID] == $style_record->ID)
											{
												$first_author_type = $style_record->default_item_link_type_ID;
											}; // end if
											?><option value="<?= $style_record->ID; ?>"<?= ($_POST[$db->field_style_ID] == $style_record->ID ? " selected" : ""); ?>><?= $style_record->title; ?></option><?
										}; // end foreach
									?>
								</select>
								<?
							}; // end if
						?>
					</td>
					<td id="age_field">
						<?
							if($age_records = lookup_designation($db->table_age))
							{
								?>
								<select id="<?= $db->field_age_ID; ?>" name="<?= $db->field_age_ID; ?>">
									<option value=""></option>
									<?
										foreach($age_records as $age_record)
										{
											?><option value="<?= $age_record->ID; ?>"<?= ($_POST[$db->field_age_ID] == $age_record->ID ? " selected" : ""); ?>><?= $age_record->title; ?></option><?
										}; // end foreach
									?>
								</select>
								<?
							}; // end if
						?>
					</td>
				</tr>
				<tr>
					<td class="tiny<?= (isset($errors['type_ID']) ? " edit_error" : ""); ?>">Media</td>
					<td id="style_title" class="tiny<?= (isset($errors['style_ID']) ? " edit_error" : ""); ?>">Style</td>
					<td id="age_title" class="tiny<?= (isset($errors['age_ID']) ? " edit_error" : ""); ?>">Target Age</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr id="series_row" style="<?= ($is_item_copy ? "display:none;" : ""); ?>">
		<td class="edit_caption"><nobr>Series:</nobr></td>
		<td class="edit_value">
			<script language="javascript">
				function set_series(ID, title)
				{
					if(ID == 0)
					{
						document.getElementById('series_number').value = "0";
					}; // end if
					document.getElementById('series_ID').value = ID;
					document.getElementById('series_title').value = title;
					document.getElementById('series_iframe').style.display = "none";
					document.getElementById('series_number').focus();
					document.getElementById('series_number').select();
				}; // end function
				function lookup_series()
				{
					if(document.getElementById('series_iframe').style.display=='inline')
					{
						document.getElementById('series_iframe').style.display = 'none';
						document.getElementById('series_lookup_button').focus();
					}
					else
					{
						document.getElementById('series_iframe').style.display = 'inline';
						document.getElementById('series_iframe').contentWindow.document.getElementById('lookup_query_input').focus();
						document.getElementById('series_iframe').contentWindow.document.getElementById('lookup_query_input').select();
					}; // end if
				}; // end function
			</script>
			<?
				if(! $series_record = lookup_designation($db->table_library_series, $_POST['series_ID']))
				{
					$series_record = (object) NULL;
					$series_record->ID = 0;
					$series_record->title = "";
				}; // end if
			?>
			<input id="series_ID" name="series_ID" type="hidden" value="<?= $series_record->ID; ?>" autocomplete="off">
			<input id="series_title" class="edit_record_link" type="text" value="<?= htmlspecialchars($series_record->title); ?>" size="22" readonly>
			<input id="series_number" name="series_number" type="text" value="<?= htmlspecialchars($_POST['series_number']); ?>" size="2" style="text-align:center;">
			<?
				$focus_on_series_number = FALSE;
				if($series_record->ID > 0 && $_POST['series_number'] == "0")
				{
					$series_items = lookup_group($series_record->ID, $db->field_library_series_ID);
					foreach($series_items as $series_record)
					{
						if($series_record->series_number > 0)
						{
							$focus_on_series_number = TRUE;
						}; // end if
					}; // end foreach
				}; // end if
				if($focus_on_series_number)
				{
					?>
					<script language="javascript">
						document.getElementById('series_number').focus();
						document.getElementById('series_number').select();
					</script>
					<?
				}; // end if
			?>
			<input id="series_lookup_button" type="button" onclick="lookup_series();" value="Choose...">
			<input type="button" onclick="set_series(0, '');" value="Clear"><br>
			<?
				if(! $is_item_copy)
				{
					?>
					<iframe id="series_iframe" class="iframe_lookup" style="width:600px;height:500px;" src="about:blank"></iframe>
					<?
				}; // end if
			?>
		</td>
	</tr>
	<tr>
		<td class="edit_caption<?= ($errors['title'] ? " edit_error" : ""); ?>"><nobr>Title:</nobr></td>
		<td class="edit_value">
			<table cellspacing="0" cellpadding="0" border="0">
				<tr>
					<td><input id="title" name="title" type="text" value="<?= htmlspecialchars(stripslashes($_POST['title'])); ?>" size="32"<?= ($is_item_copy ? " READONLY" : ""); ?> autocomplete="off"></td>
					<td id="edition_field" style="<?= ($is_item_copy ? "display:none;" : ""); ?>"><input id="edition" name="edition" type="text" value="<?= htmlspecialchars(stripslashes($_POST['edition'])); ?>" size="9" style="text-align:center;"></td>
					<td id="copy_field"><input id="copy_number" <?= ($_POST[$db->field_library_copy_item_ID] > 0 ? ' class="edit_highlight"' : ""); ?> name="copy_number" type="text" value="<?= htmlspecialchars(stripslashes($_POST['copy_number'])); ?>" size="3" style="text-align:center;"><input name="<?= $db->field_library_copy_item_ID; ?>" type="hidden" value="<?= htmlspecialchars(stripslashes(($is_item_copy ? $duplicate_prefill_ID : $_POST[$db->field_library_copy_item_ID]))); ?>"></td>
				</tr>
				<tr>
					<td id="title_title" class="tiny"></td>
					<td id="edition_title" class="tiny" style="text-align:center;<?= ($is_item_copy ? "display:none;" : ""); ?>">Edition</td>
					<td id="copy_title" class="tiny" style="text-align:center;">Copy #</td>
				</tr>
			</table>
			<?
				if(! $is_item_copy && ! $focus_on_series_number && FALSE)
				{
					?>
					<script language="javascript">
						document.getElementById('title').focus();
						document.getElementById('title').select();
					</script>
					<?
				}; // end if
			?>
		</td>
	</tr>
	<tr id="parallel_title_row" style="<?= ($is_item_copy ? "display:none;" : ""); ?>">
		<td class="edit_caption"><nobr>Parallel Title:</nobr></td>
		<td class="edit_value">
			<input id="parallel_title" name="parallel_title" type="text" value="<?= htmlspecialchars(stripslashes($_POST['parallel_title'])); ?>" size="42" autocomplete="off"><input type="button" onclick="document.getElementById('parallel_title').value='';document.getElementById('parallel_title').focus();" value="Clear">
		</td>
	</tr>
	<tr style="<?= ($is_item_copy ? "display:none;" : ""); ?>">
		<td class="edit_caption<?= ($errors['authors'] ? " edit_error" : ""); ?>"><nobr>Contributors:</nobr></td>
		<td class="edit_value">
			<script language="javascript">
				var nofocus = true;
				var add_author_counter = 10000;
				function add_author(ID, name)
				{
					add_author_counter++;
					if(document.getElementById('author_' + ID) == null)
					{
						new_html =
							'<tr id="author_' + add_author_counter + '">' +
								'<td>' +
									'<select id="author_type_'+ add_author_counter +'" name="author_type_ID[]" style="text-align:right;">' +
										<?
											if($item_link_type_records = lookup_designation($db->table_library_item_link_type, -1,"`table`='author'"))
											{
												foreach($item_link_type_records as $item_link_type_record)
												{
													?>'<option value="<?= $item_link_type_record->ID; ?>"<?= ($author_record->{$db->field_library_item_link_type_ID} == $item_link_type_record->ID ? " selected" : ""); ?>><?= $item_link_type_record->title; ?>:</option>'+<?
												}; // end foreach
											}; // end if
										?>
									'</select> '+
									'<input name="author_ID[]" type="hidden" value="' + ID + '" size="4" readonly> '+
								'</td><td>' +
									'<input type="text" name="section_author[]" class="edit_record_link" value="' + name + '" size="23" readonly> ' +
								'</td><td>' +
									'<input type="text" name="section_title[]" value="" size="27" autocomplete="off" onkeyup="section_start_move(' + add_author_counter + ', 2);"> '+
								'</td><td>' +
									'<input type="text" name="section_start[]" value="" size="2" style="text-align:center;" autocomplete="off" onkeyup="section_start_move(' + add_author_counter + ', 3);"> '+
									'<input type="button" onclick="move_author(' + add_author_counter + ', 1);" value="&#0234;" style="font-family:wingdings;">'+
									'<input type="button" onclick="move_author(' + add_author_counter + ', -1);" value="&#0233;" style="font-family:wingdings;">'+
									'<input type="button" onclick="remove_author(' + add_author_counter + ');" value="&#0251;" style="font-family:wingdings;">'+
								'</td>' + 
							'</tr>';

						
						document.getElementById('author_list_table').insertAdjacentHTML('beforeEnd', new_html);

						document.getElementById('author_type_' + add_author_counter).value = document.getElementById('author_type_chooser').value;
					}; // end if

					document.getElementById('author_iframe').style.display = "none";
					
					author_divs = document.getElementById('author_list_table').getElementsByTagName('tr');
					previous_author = author_divs[author_divs.length - 2];

					<?
						if($item_ID == "" && $duplicate_prefill_ID == 0)
						{
							?>
							if(add_author_counter < 10002)
							{
								document.getElementById('author_type_' + add_author_counter).value = <?= $first_author_type; ?>;
								document.getElementById('author_type_chooser').value = <?= $first_author_type; ?>;
							}; // end if
							set_call_number();
							<?
						}; // end if
					?>
					if(previous_author.getElementsByTagName('input').length > 2)
					{
						if(previous_author.getElementsByTagName('input')[2].value != "")
						{
							author_divs[author_divs.length - 1].getElementsByTagName('input')[2].focus();
						}
						else
						{
							document.getElementById('author_lookup_button').focus();
						}; // end if
					}
					else
					{
						document.getElementById('author_lookup_button').focus();
					}; // end if
					add_author_counter++;
				}; // end function


				function reset_author_frame()
				{
					nofocus = true;
					document.getElementById('author_iframe').src = "<?= $db->url_root; ?>/author_lookup.php";
				}; // end function

				function remove_author(ID)
				{
					document.getElementById('author_' + ID).outerHTML = '';
					document.getElementById('author_lookup_button').focus();
				}; // end function
				function lookup_author(force)
				{
					if(document.getElementById('author_iframe').style.display!='inline' || force)
					{
						document.getElementById('author_iframe').style.display = 'inline';
						document.getElementById('author_iframe').contentWindow.document.getElementById('lookup_query_input').focus();
						document.getElementById('author_iframe').contentWindow.document.getElementById('lookup_query_input').select();
					}
					else
					{
						document.getElementById('author_iframe').style.display = 'none';
						document.getElementById('author_lookup_button').focus();
					}; // end if
				}; // end function
				function move_author(ID, direction)
				{
					var author_divs = document.getElementById('author_list_table').getElementsByTagName('tr');
					last_author_ID = "";
					for (a=0; a<author_divs.length; a++)
					{
						this_author_ID = author_divs[a].id;
						if((direction > 0 ? last_author_ID : this_author_ID) == "author_" + ID)
						{
							old_type_ID = document.getElementById(last_author_ID).getElementsByTagName('select')[0].value;
							old_section_title = document.getElementById(last_author_ID).getElementsByTagName('input')[2].value;
							old_section_start = document.getElementById(last_author_ID).getElementsByTagName('input')[3].value;
							temp_div = document.getElementById(last_author_ID).outerHTML;
							document.getElementById(last_author_ID).outerHTML = "";
							document.getElementById(this_author_ID).insertAdjacentHTML('afterEnd', temp_div);
							document.getElementById(last_author_ID).getElementsByTagName('select')[0].value = old_type_ID;
							document.getElementById(last_author_ID).getElementsByTagName('input')[2].value = old_section_title;
							document.getElementById(last_author_ID).getElementsByTagName('input')[3].value = old_section_start;
							break;
						}; // end if
						last_author_ID = this_author_ID;
					}; // end for
				}; // end function
				function section_start_move(ID, field)
				{
					switch(event.keyCode)
					{
						case 38: // up
							direction = -1;
							break;
						case 40: // down
							direction = 1;
							break;
						default:
							return;
							break;
					}; // end switch

					var author_divs = document.getElementById('author_list_table').getElementsByTagName('tr');
					last_author_ID = "";
					moved = false;
					for (a=0; a<author_divs.length; a++)
					{
						this_author_ID = author_divs[a].id;

						if((direction > 0 ? last_author_ID : this_author_ID) == "author_" + ID)
						{
							switch(event.keyCode)
							{
								case 38: // up
									document.getElementById(last_author_ID).getElementsByTagName('input')[field].select();
									document.getElementById(last_author_ID).getElementsByTagName('input')[field].focus();
									moved = true;
									break;
								case 40: // down
									document.getElementById(this_author_ID).getElementsByTagName('input')[field].select();
									document.getElementById(this_author_ID).getElementsByTagName('input')[field].focus();
									moved = true;
									break;
							}; // end switch
							break;
						}; // end if
						last_author_ID = this_author_ID;
					}; // end for
					if(event.keyCode == 40 && ! moved)
					{
						lookup_author(true);
					}; // end if
				}; // end function
			</script>
			<table id="author_list_table" cellspacing="0" cellpadding="0" border="0">
				<tr>
					<td class="tiny">Type</td>
					<td class="tiny">Name</td>
					<td class="tiny">Section Title (optional)</td>
					<td class="tiny">Start (optional)</td>
				</tr>
				<?
				$first = TRUE;

				$author_counter = 0;

				foreach($author_list as $author_record)
				{
					?>
					<tr id="author_<?= $author_counter; ?>">
						<td>
							<?
								if($item_link_type_records = lookup_designation($db->table_library_item_link_type, -1,"`table`='author'"))
								{
									?>
									<select name="author_type_ID[]" style="text-align:right;">
										<?
											foreach($item_link_type_records as $item_link_type_record)
											{
												?><option value="<?= $item_link_type_record->ID; ?>"<?= ($author_record->{$db->field_library_item_link_type_ID} == $item_link_type_record->ID ? ' selected="true"' : ""); ?>><?= $item_link_type_record->title; ?>:</option><?
											}; // end foreach
										?>
									</select>
									<?
								}; // end if
							?>
							<input name="author_ID[]" type="hidden" value="<?= $author_record->ID; ?>" size="4" readonly>
						</td><td>
							<input type="text" name="section_author[]" class="edit_record_link" value="<?= htmlspecialchars(build_author_name($author_record)); ?>" size="23" readonly>
						</td><td>
							<input type="text" name="section_title[]" value="<?= htmlspecialchars($author_record->section_title); ?>" size="27" autocomplete="off" onkeyup="section_start_move(<?= $author_counter; ?>, 2);">
						</td><td>
							<input type="text" name="section_start[]" value="<?= htmlspecialchars(($author_record->section_start > 0 ? $author_record->section_start: "")); ?>" size="2" style="text-align:center;" autocomplete="off" onkeyup="section_start_move(<?= $author_counter; ?>, 3);">
							<input type="button" onclick="move_author(<?= $author_counter; ?>, 1);" value="&#0234;" style="font-family:wingdings;"><?
							?><input type="button" onclick="move_author(<?= $author_counter; ?>, -1);" value="&#0233;" style="font-family:wingdings;"><?
							?><input type="button" onclick="remove_author(<?= $author_counter; ?>);" value="&#0251;" style="font-family:wingdings;">
						</td>
					</tr>
					<?
					$first = FALSE;
					$author_counter++;
				}; // end foreach
			?></table>
			<div>
				<?
					if($item_link_type_records = lookup_designation($db->table_library_item_link_type, -1,"`table`='author'"))
					{
						?>
						<select id="author_type_chooser" style="text-align:right;">
							<?
								foreach($item_link_type_records as $item_link_type_record)
								{
									?><option value="<?= $item_link_type_record->ID; ?>"<?= ($item_link_type_record->ID == $db->ID_item_link_type_author ? " selected" : ""); ?>><?= $item_link_type_record->title; ?>:</option><?
								}; // end foreach
							?>
						</select>
						<?
					}; // end if
				?>
				<button id="author_lookup_button" type="button" onclick="lookup_author(false);">Add...</button>
			</div>
			<?
				if(! $is_item_copy)
				{
					?>
					<iframe id="author_iframe" class="iframe_lookup" style="width:600px;height:500px;" src="about:blank"></iframe>
					<?
				}; // end if
			?>
		</td>
	</tr>
	<tr id="subjects_row" style="<?= ($is_item_copy ? "display:none;" : ""); ?>">
		<td class="edit_caption"><nobr>Subject(s):</nobr></td>
		<td class="edit_value">
			<?
				if($item_ID == "" && $duplicate_prefill_ID == 0)
				{
					$subject_ID_list = array();
					foreach($the_subjects as $key=>$val)
					{
						$subject_sql = "
							SELECT
								*
							FROM
								`" . $db->table_subject . "`
							WHERE
								`ID`='" . $val . "'
						";
						$subject_result = mysql_query($subject_sql, $mysql->link);
						if(mysql_num_rows($subject_result) > 0)
						{
							$subject_record = mysql_fetch_object($subject_result);
							$subject_ID_list[$subject_record->ID] = $subject_record->title;
						}; // end if
					}; // end foreach
				}
				else
				{
					$subject_ID_list = array();
					$subject_sql = "
						SELECT
							`" . $db->table_subject . "`.`ID` AS 'ID',
							`" . $db->table_subject . "`.`title` AS 'title'
						FROM
							`" . $db->table_library_item_link . "`,
							`" . $db->table_library_item_link_type . "`,
							`" . $db->table_subject . "`
						WHERE
							`" . $db->table_library_item_link . "`.`record_ID`=`" . $db->table_subject . "`.`ID`
							AND `" . $db->table_library_item_link . "`.`" . $db->field_library_item_link_type_ID . "`=`" . $db->table_library_item_link_type . "`.`ID`
							AND `" . $db->table_subject . "`.`enabled`='Y'
							AND `" . $db->table_library_item_link . "`.`enabled`='Y'
							AND `" . $db->table_library_item_link_type . "`.`table`='subject'
							AND `" . $db->table_library_item_link . "`.`" . $db->field_library_item_ID . "`='" . ($duplicate_prefill_ID > 0 ? $duplicate_prefill_ID : $item_ID) . "'
						ORDER BY
							`priority` ASC
					";
					$subject_result = mysql_query($subject_sql, $mysql->link);
					if(mysql_num_rows($subject_result) > 0)
					{
						while($subject_record = mysql_fetch_object($subject_result))
						{
							$subject_ID_list[$subject_record->ID] = $subject_record->title;
						}; // end while
					}; // end if
				}; // end if
			?>
			<script language="javascript">
				var subject_IDs = new Object();
				<?

					foreach($subject_ID_list as $key=>$val)
					{
						?>
						subject_IDs.a<?= $key; ?> = <?= $key; ?>;
						<?
					}; // end foreach

					$_POST['subject_IDs'] = implode(",", array_keys($subject_ID_list));
				?>

				function reset_subject_IDs()
				{
					subject_ID_list = "";
					for(subject_ID in subject_IDs)
					{
						eval("aID=subject_IDs." + subject_ID + ";");
						if(aID != null)
						{
							subject_ID_list += (subject_ID_list == "" ? "" : ",") + aID;
						}; // end if
					}; // end for
					document.getElementById('subject_IDs').value = subject_ID_list;
				}; // end function
				function add_subject(ID, name)
				{
					if(document.getElementById('subject_' + ID) == null)
					{
						eval("subject_IDs.a" + ID + "=" + ID + ";");
						document.getElementById('subject_names').innerHTML = document.getElementById('subject_names').innerHTML + '<div id="subject_' + ID + '">' + name + ' [<a href="javascript:" onclick="remove_subject(' + ID + ');">remove</a>]</div>';
					}; // end if
					document.getElementById('subject_iframe').style.display = "none";
					reset_subject_IDs();
					document.getElementById('subject_lookup_button').focus();
				}; // end function
				function remove_subject(ID)
				{
					eval("subject_IDs.a" + ID + "=null;");
					document.getElementById('subject_' + ID).outerHTML = '';
					reset_subject_IDs();
					document.getElementById('subject_lookup_button').focus();
				}; // end function
				function lookup_subject()
				{
					if(document.getElementById('subject_iframe').style.display=='inline')
					{
						document.getElementById('subject_iframe').style.display = 'none';
						document.getElementById('subject_lookup_button').focus();
					}
					else
					{
						document.getElementById('subject_iframe').style.display = 'inline';
						document.getElementById('subject_iframe').contentWindow.document.getElementById('lookup_query_input').focus();
						document.getElementById('subject_iframe').contentWindow.document.getElementById('lookup_query_input').select();
					}; // end if
				}; // end function
			</script>
			<input id="subject_IDs" name="subject_IDs" value="<?= $_POST['subject_IDs']; ?>" type="hidden">
			<div id="subject_names" class="edit_static_data"><?
				foreach($subject_ID_list as $key=>$val)
				{
					?>
					<div id="subject_<?= $key; ?>"><?= $val; ?> [<a href="javascript:" onclick="remove_subject(<?= $key; ?>);">remove</a>]</div>
					<?
				}; // end foreach
			?></div>
			<input id="subject_lookup_button" type="button" onclick="lookup_subject();" value="Choose..."><br>
			<?
				if(! $is_item_copy)
				{
					?>
					<iframe id="subject_iframe" class="iframe_lookup" style="width:600px;height:500px;" src="about:blank"></iframe>
					<?
				}; // end if
			?>
		</td>
	</tr>
	<tr style="<?= ($is_item_copy ? "display:none;" : ""); ?>">
		<td class="edit_caption"><nobr>Summary:</nobr></td>
		<td class="edit_value">
			<textarea id="summary" name="summary" rows="4" cols="50"><?= htmlspecialchars(stripslashes($_POST['summary'])); ?></textarea>
		</td>
	</tr>
	<tr>
		<td class="edit_caption">
			<nobr>
				<div>Location:</div>
				<div style="padding-top:30px;">Publisher:</div>
			</nobr>
		</td>
		<td class="edit_value">
			<table cellspacing="0" cellpadding="0" border="0">
				<tr>
					<td>
						<?
							if($location_records = lookup_designation($db->table_location))
							{
								?>
								<select name="<?= $db->field_location_ID; ?>"<?= ($_POST[$db->field_library_copy_item_ID] > 0 ? ' class="edit_highlight"' : ""); ?><?
										if($is_item_copy)
										{
											?> onchange="document.getElementById('barcode').focus();"<?
										}; // end if
									?>>
									<option value=""></option>
									<?
										foreach($location_records as $location_record)
										{
											?><option value="<?= $location_record->ID; ?>"<?= ($_POST[$db->field_location_ID] == $location_record->ID ? " selected" : ""); ?>><?= $location_record->title; ?></option><?
										}; // end foreach
									?>
								</select>
								<?
							}; // end if
						?>
					</td>
					<td style="<?= ($is_item_copy ? "display:none;" : ""); ?>">
						<?
							if($category_records = lookup_designation($db->table_category))
							{
								?>
								<select id="<?= $db->field_category_ID; ?>" name="<?= $db->field_category_ID; ?>">
									<option value=""></option>
									<?
										foreach($category_records as $category_record)
										{
											?><option value="<?= $category_record->ID; ?>"<?= ($_POST[$db->field_category_ID] == $category_record->ID ? " selected" : ""); ?>><?= $category_record->title; ?></option><?
										}; // end foreach
									?>
								</select>
								<script language="javascript">
									var call_number_prefixes = new Array;
									<?
										foreach($category_records as $category_record)
										{
											?>
											call_number_prefixes[<?= $category_record->ID; ?>] = "<?= $category_record->call_number_prefix; ?>";
											<?
										}; // end foreach
									?>
								</script>
								<?
							}; // end if
						?>
					</td>
					<td style="<?= ($is_item_copy ? "display:none;" : ""); ?>">
						<input id="call_number" name="call_number" type="text" value="<?= htmlspecialchars(stripslashes($_POST['call_number'])); ?>" size="14" autocomplete="off">
					</td>
				</tr>
				<tr>
					<td class="tiny<?= (isset($errors['location_ID']) ? " edit_error" : ""); ?>" valign="top">Room</td>
					<td class="tiny<?= (isset($errors['category_ID']) ? " edit_error" : ""); ?>" style="<?= ($is_item_copy ? "display:none;" : ""); ?>" valign="top">Category</td>
					<td class="tiny<?= ($errors['call_number'] ? " edit_error" : ""); ?>" style="<?= ($is_item_copy ? "display:none;" : ""); ?>" valign="top">
						Call Number (<a href="javascript:" onclick="set_call_number();">Auto</a>)<br>
					</td>
				</tr>
				<tr style="<?= ($is_item_copy ? "display:none;" : ""); ?>">
					<td colspan="2" valign="top" style="padding-top:6px;" id="publisher_row" style="<?= ($is_item_copy ? "display:none;" : ""); ?>">
						<script language="javascript">
							function set_publisher(ID, title)
							{
								document.getElementById('publisher_ID').value = ID;
								document.getElementById('publisher_title').value = title;
								document.getElementById('publisher_iframe').style.display = "none";
								document.getElementById('publisher_lookup_button').focus();
							}; // end function
							function lookup_publisher()
							{
								if(document.getElementById('publisher_iframe').style.display=='inline')
								{
									document.getElementById('publisher_iframe').style.display = 'none';
									document.getElementById('publisher_lookup_button').focus();
								}
								else
								{
									document.getElementById('publisher_iframe').style.display = 'inline';
									document.getElementById('publisher_iframe').contentWindow.document.getElementById('lookup_query_input').focus();
									document.getElementById('publisher_iframe').contentWindow.document.getElementById('lookup_query_input').select();
								}; // end if
							}; // end function
						</script>
						<?
							if(! $publisher_record = lookup_designation($db->table_publisher, $_POST['publisher_ID']))
							{
								$publisher_record = (object) NULL;
								$publisher_record->ID = 0;
								$publisher_record->title = "";
								$publisher_record->location = "";
							}; // end if
						?>
						<input id="publisher_ID" name="publisher_ID" type="hidden" value="<?= $publisher_record->ID; ?>">
						<input id="publisher_title" class="edit_record_link" type="text" value="<?= htmlspecialchars($publisher_record->title . ($publisher_record->location != "" ? " - " . $publisher_record->location : "")); ?>" size="28" readonly autocomplete="off">
						<input id="publisher_lookup_button" type="button" onclick="lookup_publisher();" value="Choose..."> <input type="button" onclick="set_publisher(0, '');" value="Clear"><br>
						<?
							if(! $is_item_copy)
							{
								?>
								<iframe id="publisher_iframe" class="iframe_lookup" style="width:600px;height:500px;" src="about:blank"></iframe>
								<?
							}; // end if
						?>
					</td>
					<td class="tiny">
						<a href="javascript:" onclick="add_item_type('S');">Special Collection</a><br>
						<a href="javascript:" onclick="add_item_type('O');">Oversize Edition</a><br>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr id="physical_description_row" style="<?= ($is_item_copy ? "display:none;" : ""); ?>">
		<td class="edit_caption"><nobr>Physical Description:</nobr></td>
		<td class="edit_value">
			<table cellspacing="0" cellpadding="0" border="0">
				<tr>
					<td><input id="length" name="length" type="text" value="<?= htmlspecialchars(stripslashes($_POST['length'])); ?>" size="11" autocomplete="off"></td>
					<td><input id="height" name="height" type="text" value="<?= htmlspecialchars(stripslashes($_POST['height'])); ?>" size="11" title="Round Up" autocomplete="off"></td>
				</tr>
				<tr>
					<td class="tiny<?= (isset($errors['length']) ? " edit_error" : ""); ?>">Pages/Minutes</td>
					<td class="tiny<?= (isset($errors['height']) ? " edit_error" : ""); ?>">Height (cm)</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td class="edit_caption"><nobr>ID Numbers:</nobr></td>
		<td class="edit_value">
			<table cellspacing="0" cellpadding="0" border="0">
				<tr>
					<td id="isbn_field" style="<?= ($is_item_copy ? "display:none;" : ""); ?>">
						<input id="isbn" name="isbn" type="text" value="<?= htmlspecialchars(stripslashes($_POST['isbn'])); ?>" size="15" autocomplete="off">
					</td>
					<td id="isbn13_field" style="<?= ($is_item_copy ? "display:none;" : ""); ?>">
						<input id="isbn13" name="isbn13" type="text" value="<?= htmlspecialchars(stripslashes($_POST['isbn13'])); ?>" size="20" autocomplete="off">
					</td>
					<td id="lc_control_number_field" style="<?= ($is_item_copy ? "display:none;" : ""); ?>">
						<input id="lc_control_number" name="lc_control_number" type="text" value="<?= htmlspecialchars(stripslashes($_POST['lc_control_number'])); ?>" size="10" autocomplete="off">
					</td>
					<td>
						<input id="barcode" name="barcode"<?= ($_POST[$db->field_library_copy_item_ID] > 0 ? ' class="edit_highlight"' : ""); ?> type="text" value="<?= htmlspecialchars(stripslashes($_POST['barcode'])); ?>" size="8" onmouseup="this.select();" autocomplete="off"><br>
						<?
							if($is_item_copy)
							{
								?>
								<script language="javascript">
									document.getElementById('barcode').focus();
									document.getElementById('barcode').select();
								</script>
								<?
							}; // end if
						?>
					</td>
				</tr>
				<tr>
					<td id="isbn_title" class="tiny" style="<?= ($is_item_copy ? "display:none;" : ""); ?>">ISBN</td>
					<td id="isbn13_title" class="tiny" style="<?= ($is_item_copy ? "display:none;" : ""); ?>">ISBN13</td>
					<td id="lc_control_number_title" class="tiny" title="Library of Congress Control Number" style="<?= ($is_item_copy ? "display:none;" : ""); ?>">LC Control #</td>
					<td class="tiny<?= (isset($errors['barcode']) ? " edit_error" : ""); ?>">Bar Code</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr style="<?= ($is_item_copy ? "display:none;" : ""); ?>">
		<td class="edit_caption"><nobr>Images:</nobr></td>
		<td class="small">
			<?
				$image_sql = "
					SELECT
						*
					FROM
						`" . $db->table_library_image . "`
					WHERE
						`" . $db->field_library_item_ID . "`='" . mysql_escape_string($item_ID) . "'
						AND `type`='A'
						AND `enabled`='Y'
				";
				$image_result = mysql_query($image_sql, $mysql->link);
				if(mysql_num_rows($image_result) > 0)
				{
					$image_record = mysql_fetch_object($image_result);
					?><img id="thumb_image" src="<?= $db->cover_thumbs_image_url; ?>/<?= substr($image_record->ID, 0, 1); ?>/<?= $image_record->ID; ?>.jpg"><?
				}; // end if
			?>
			<input id="upload_cover_art" name="upload_cover_art" type="file">
		</td>
	</tr>
	<tr style="<?= ($is_item_copy ? "display:none;" : ""); ?>">
		<td class="edit_caption"><nobr>Options:</nobr></td>
		<td class="small">
			<label for="allow_checkout"><input id="allow_checkout" name="allow_checkout" type="checkbox" value="Y"<?= ($_POST['allow_checkout'] == "Y" ? " checked" : ""); ?>> Allow Checkout</label>
		</td>
	</tr>
	<tr style="<?= ($is_item_copy ? "display:none;" : ""); ?>">
		<td class="edit_caption"><nobr>Librarian Notes:</nobr></td>
		<td class="edit_value">
			<textarea id="notes" name="notes" rows="4" cols="50"><?= htmlspecialchars(stripslashes($_POST['notes'])); ?></textarea>
		</td>
	</tr>
	<tr>
		<td></td>
		<td class="edit_value">
			<input type="submit" name="action" value="<?= $submit_caption; ?>">
		</td>
	</tr>
	<tr style="<?= ($is_item_copy ? "" : "display:none;"); ?>">
		<td></td>
		<td class="small">
			<label for="add_more_copies"><input id="add_more_copies" name="add_more_copies" type="checkbox" value="Y"<?= ($_POST['add_more_copies'] == "Y" ? " checked" : ""); ?> onclick="document.getElementById('barcode').focus();"> Add More Copies</label>
		</td>
	</tr>
</table>
</form>

<script language="javascript">
	var iframes_loaded = 0;
	function load_iframes()
	{
		document.getElementById('series_iframe').src = "<?= $db->url_root; ?>/series_lookup.php";
		document.getElementById('author_iframe').src = "<?= $db->url_root; ?>/author_lookup.php";
		document.getElementById('subject_iframe').src = "<?= $db->url_root; ?>/subject_lookup.php";
		document.getElementById('publisher_iframe').src = "<?= $db->url_root; ?>/publisher_lookup.php";
		setTimeout("page_loaded()", 100);
	}; // end function

	function page_loaded()
	{
		if(iframes_loaded > 3)
		{
			nofocus = false;
		}
		else
		{
			setTimeout("page_loaded()", 100);
		}; // end if
	}; // end function

	function set_call_number()
	{
		if(document.getElementById('author_list_table').getElementsByTagName('input')[1])
		{
			document.getElementById('call_number').value = call_number_prefixes[document.getElementById('<?= $db->field_category_ID; ?>').value] + document.getElementById('author_list_table').getElementsByTagName('input')[1].value.substring(0, 3).toUpperCase();
			tl = call_number_prefixes[document.getElementById('<?= $db->field_category_ID; ?>').value].length;
			ip = call_number_prefixes[document.getElementById('<?= $db->field_category_ID; ?>').value].indexOf('#');
			document.getElementById('call_number').value = call_number_prefixes[document.getElementById('<?= $db->field_category_ID; ?>').value].substring(0, ip) + document.getElementById('author_list_table').getElementsByTagName('input')[1].value.substring(0, 3).toUpperCase() + call_number_prefixes[document.getElementById('<?= $db->field_category_ID; ?>').value].substring(ip + 1, tl);

			oc = document.getElementById('call_number').value;
			tl = oc.length;
			dp = oc.indexOf('^');
			if("<?= strtoupper($dewey_guess); ?>" != "")
			{
				if(dp > -1)
				{
					document.getElementById('call_number').value = oc.substring(0, dp) + "<?= strtoupper($dewey_guess); ?>" + oc.substring(dp + 1, tl);
				}; // end if
			}; // end if
		}
		else
		{
			document.getElementById('call_number').value = call_number_prefixes[document.getElementById('<?= $db->field_category_ID; ?>').value];
		}; // end if
	}; // end function

	setTimeout("load_iframes()", 1);
</script>

<?

include_once(dirname(__FILE__) . "/bottom.php");

?>
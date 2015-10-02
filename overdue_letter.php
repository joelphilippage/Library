<?

include_once(dirname(__FILE__) . "/first.php");


$ID = urldecode($query[0]);
$overdue_days = urldecode($query[1]);

if(! $accounts->account_record->ID)
{
	exit_error("Not Signed In", "You must be signed into perform that action.");
}; // end if

if($accounts->account_record->librarian != "Y" && $accounts->account_record->admin != "Y")
{
	exit_error("Access Denied", "Only a librarian can perform that action.");
}; // end if

$overdue_item_count = 0;
$lost_item_count = 0;

$first_page = TRUE;

if($ID == "")
{
	$account_sql = "
		SELECT
			*
		FROM
			`" . $db->table_account . "`
		WHERE
			`enabled`='Y'
	";
	$account_result = mysql_query($account_sql, $mysql->link);
	if(mysql_num_rows($account_result) > 0)
	{
		$first_page = TRUE;
		while($account_record = mysql_fetch_object($account_result))
		{
			if(output_letter($account_record->ID))
			{
				$first_page = FALSE;
			}; // end if
		}; // end while
	}; // end if


	if(! $first_page)
	{
		$pm->stream();
	}
	else
	{
		exit_error("Good News", "There are no items overdue for anyone.");
	}; // end if
}
else
{
	$group_sql = "
		SELECT
			`" . $db->table_group_member . "`.`account_ID` AS 'account_ID'
		FROM
			`" . $db->table_group_member . "`,
			`" . $db->table_account . "`
		WHERE
			`" . $db->table_group_member . "`.`" . $db->field_account_ID . "`=`" . $db->table_account . "`.`ID`
			AND `" . $db->table_group_member . "`.`" . $db->field_group_ID . "`='" . $ID . "'
			AND `" . $db->table_group_member . "`.`enabled`='Y'
	";
	$group_result = mysql_query($group_sql, $mysql->link);
	if(mysql_num_rows($group_result) > 0)
	{
		$first_page = TRUE;
		while($group_record = mysql_fetch_object($group_result))
		{
			if(output_letter($group_record->{$db->field_account_ID}))
			{
				$first_page = FALSE;
			}; // end if
		}; // end while
	}; // end if

	if(! $first_page)
	{
		$pm->stream();
	}
	else
	{
		exit_error("Good News", "There are no items overdue for this group.");
	}; // end if

}; // end if



function output_letter($account_ID)
{
	global $db, $mysql, $pm, $accounts, $first_page;

	$account_sql = "
		SELECT
			*
		FROM
			`" . $db->table_account . "`
		WHERE
			`ID`='" . mysql_escape_string($account_ID) . "'
	";
	$account_result = mysql_query($account_sql, $mysql->link);
	$account_record = mysql_fetch_object($account_result);

	$checkout_records = array();
	$lost_records = array();

	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////// LOOKUP OVERDUE RECORDS
	$checkout_sql = "
		SELECT
			*
		FROM
			`" . $db->table_library_checkout . "`
		WHERE
			`" . $db->field_account_ID . "`='" . mysql_escape_string($account_ID) . "'
			AND `out_datetime`!='" . $db->blank_datetime . "'
			AND `in_datetime`='" . $db->blank_datetime . "'
			AND `lost_datetime`='" . $db->blank_datetime . "'
	";
	$checkout_result = mysql_query($checkout_sql, $mysql->link);
	if(mysql_num_rows($checkout_result) > 0)
	{
		while($checkout_record = mysql_fetch_object($checkout_result))
		{
			if($checkout_record->due_datetime < date("Y-m-d H:i:s"))
			{
				$checkout_records[] = $checkout_record;
				$overdue_item_count++;
			}; // end if
		}; // end while
	}; // end if

	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////// LOOKUP LOST RECORDS
	$lost_sql = "
		SELECT
			*
		FROM
			`" . $db->table_library_checkout . "`
		WHERE
			`" . $db->field_account_ID . "`='" . mysql_escape_string($account_ID) . "'
			AND `out_datetime`!='" . $db->blank_datetime . "'
			AND `lost_datetime`!='" . $db->blank_datetime . "'
			AND `in_datetime`='" . $db->blank_datetime . "'
			AND `paid_datetime`='" . $db->blank_datetime . "'
	";
	$lost_result = mysql_query($lost_sql, $mysql->link);
	if(mysql_num_rows($lost_result) > 0)
	{
		while($lost_record = mysql_fetch_object($lost_result))
		{
			$lost_records[] = $lost_record;
			$lost_item_count++;
		}; // end while
	}; // end if


	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////// CREATE LETTER

	if($overdue_item_count + $lost_item_count > 0)
	{
		if(! $first_page)
		{
			$pm->new_page();
		}; // end if
		$first_page = FALSE;


		$current_number = $starting_number;

		require_once($db->include_dir . "/fonts/times.php");
		include_once($db->include_dir . "/pdf_maker.php");

		$xo = 1;
		$yo = 1;
		$lw = 6.5;
		$lh = 9;

		$pm->set_font("times");


		$text = "Immanuel Mission School
" . date("F jS, Y") . "


" . ($account_record->library_notices_to == "P" ? "Dear Parent/Guardian" : $account_record->name) . ",

";

		////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		///////////////////////////////////////////////////////////////////////////// OVERDUE ITEM LIST

		if($overdue_item_count > 0)
		{
			$text .= "We want you to know that " . ($account_record->library_notices_to == "P" ? $account_record->name . " has" : "you have") . " " . $overdue_item_count . " item" . ($overdue_item_count != 1 ? "s" : "") . " checked-out from our library that need" . ($overdue_item_count != 1 ? "" : "s") . " to be returned.  The " . ($overdue_item_count != 1 ? "information about the items are" : "item information is") . " listed below.

";

			foreach($checkout_records as $checkout_record)
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

				$text .= "     ";

				if($item_record->{$db->field_library_series_ID} != 0)
				{
					$series_sql = "
						SELECT
							*
						FROM
							`" . $db->table_series . "`
						WHERE
							`ID`='" . $item_record->{$db->field_library_series_ID} . "'
					";
					$series_result = mysql_query($series_sql, $mysql->link);
					$series_record = mysql_fetch_object($series_result);
					$text .= $series_record->title;
					if($item_record->series_number > 0)
					{
						$text .= " #" . $item_record->series_number;
					}; // end if
					$text .= ": ";
				}; // end if

				$text .= $item_record->title;

				if($item_record->{$db->field_library_type_ID} != $db->ID_type_book)
				{
					$type_sql = "
						SELECT
							*
						FROM
							`" . $db->table_library_type . "`
						WHERE
							`ID`='" . $item_record->{$db->field_library_type_ID} . "'
					";
					$type_result = mysql_query($type_sql, $mysql->link);
					$type_record = mysql_fetch_object($type_result);
					$text .= " (" . $type_record->title . ")";
				}; // end if
				
				$text .= "  (out since " . sql_date_format("F jS, Y", $checkout_record->out_datetime) . ")

";

			}; // end foreach
			if($lost_item_count < 1)
			{
				$text .= "Please find and return " . ($overdue_item_count != 1 ? "these items" : "this item") . " as soon as possible.

";
			}; // end if
		}; // end if


		////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		///////////////////////////////////////////////////////////////////////////// LOST ITEM LIST

		if($lost_item_count > 0)
		{
			$total_due = 0;

			if($overdue_item_count > 0)
			{
				$text .= "You also should know that there " . ($lost_item_count != 1 ? "are" : "is") . " " . $lost_item_count . " item" . ($lost_item_count != 1 ? "s" : "") . " that " . ($lost_item_count != 1 ? "have" : "has") . " been lost and need" . ($lost_item_count != 1 ? "" : "s") . " to be paid for.";
			}
			else
			{
				$text .= "We want you to know that " . ($account_record->library_notices_to == "P" ? $account_record->name . " has" : "you have") . " " . $lost_item_count . " item" . ($lost_item_count != 1 ? "s" : "") . " that " . ($lost_item_count != 1 ? "have" : "has") . " been lost and need" . ($lost_item_count != 1 ? "" : "s") . " to be paid for.";
			}; // end if

				$text .= "  The " . ($lost_item_count != 1 ? "information about the items are" : "item information is") . " listed below.

";

			foreach($lost_records as $lost_record)
			{
				$item_sql = "
					SELECT
						*
					FROM
						`" . $db->table_library_item . "`
					WHERE
						`ID`='" . $lost_record->item_ID . "'
				";
				$item_result = mysql_query($item_sql, $mysql->link);
				$item_record = mysql_fetch_object($item_result);

				$text .= "     " . $item_record->title;

				if($item_record->{$db->field_library_type_ID} != $db->ID_type_book)
				{
					$type_sql = "
						SELECT
							*
						FROM
							`" . $db->table_library_type . "`
						WHERE
							`ID`='" . $item_record->{$db->field_library_type_ID} . "'
					";
					$type_result = mysql_query($type_sql, $mysql->link);
					$type_record = mysql_fetch_object($type_result);
					$text .= " (" . $type_record->title . ")";
				}; // end if
				
				$text .= "  (reported lost on " . sql_date_format("F jS, Y", $lost_record->lost_datetime) . ")
          Replacement fee: $" . $lost_record->lost_fee . "

";
			$total_due += $lost_record->lost_fee;

			}; // end foreach

			if($overdue_item_count < 1)
			{
				$text .= "Please pay for " . ($lost_item_count != 1 ? "these items" : "this item") . " as soon as possible.";
			}
			else
			{
				$text .= "Please find and return the overdue item" . ($overdue_item_count != 1 ? "s" : "") . " and pay for the lost item" . ($lost_item_count != 1 ? "s" : "") . " as soon as possible.";
			}; // end if

			if($lost_item_count > 1)
			{
				$text .= "  The total amount due for the lost items is: $" . $total_due . "

";
			}
			else
			{
				$text .= "

";
			}; // end if
		}; // end if


	$text .= "
Thank you,
" . $accounts->account_record->name . "
";

		$pm->text_wrap($xo, $yo, 0.21, $text, $lw, $align = "left");
	}; // end if

}; // end function


?>
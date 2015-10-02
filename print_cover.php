<?

include_once(dirname(__FILE__) . "/first.php");

$item_ID = urldecode($query[0]);
$type = urldecode($query[1]);  /// NOT USED YET... only does dvd slim....... options will be: dvdslim, dvdstandard, cdslim

$item_sql = "
	SELECT
		*
	FROM
		`" . $db->table_library_item . "`
	WHERE
		`ID`='" . mysql_escape_string($item_ID) . "'
";

$item_result = mysql_query($item_sql, $mysql->link);
$item_record = mysql_fetch_object($item_result);

$style_sql = "
	SELECT
		*
	FROM
		`" . $db->table_library_style . "`
	WHERE
		`ID`='" . mysql_escape_string($item_record->{$db->field_library_style_ID}) . "'
";

$style_result = mysql_query($style_sql, $mysql->link);
$style_record = mysql_fetch_object($style_result);

require_once($db->common_include_dir . "/fonts/times.php");
include_once($db->common_include_dir . "/pdf_maker.php");


switch($type)
{
	case "dvdslim":

		$xo = 0;
		$yo = -10.5;
		$txo = 0;
		$tyo = -19;
		$bw = 2.25;
		$bh = 0.403;
		$lw = 2.781;
		$lh = 1;

		$boy = 0.299;
		$box = 0.094;
		$tw = 5.1875 - 1;

		$barcode_type = "AB";
		$barcode_code = $item_record->barcode;
		$barcode_height = "60";
		$barcode_command = "save";
		include($db->common_image_dir . "/barcode.php");

		$color_lightgray = imagecolorallocate($image_barcode, 128, 128, 128);

		$pm->set_font("helvetica");

		$pm->text(5.15625, 7, 0.1875, "<b>" . preg_replace("/,/", " ", $item_record->call_number) . "</b>", "right");

		$pm->set_font("times");

		$pm->SetX(0);
		$pm->SetY(0);
		$pm->complete_rotate(90);

		//$pm->rect($xo + 0, $yo + 0, 10.5, 7.25);
		//$pm->rect($xo + 0, $yo + 0, 5.1875, 7.25);
		//$pm->rect($xo + 0, $yo + 0, 5.3125, 7.25);
		//$pm->rect($xo + .25, $yo + .25, 10, 6.75);
		//$pm->rect($xo + 5.5, $yo + 0, 5.1875, 1.75);

		$pm->image_jpeg($barcode_filename, $xo + 1.299 - 0.125, $yo + 5.3125 + 0.5 + (($lw - $bw) / 2), $bw, $bh);
		unlink($barcode_filename);

		$pm->text($txo + 1.049 - 0.125, $tyo + 5.3125 + 0.5 + ($lw / 2), 0.15, "Immanuel Mission Library", "center");
		$pm->text($txo + 1.319 + $bh - 0.125, $tyo + 5.3125 + 0.5 + ($lw / 2), 0.15, "" . trim(preg_replace("/(.)/", "\\1     ", $barcode_code)) . "", "center");

		$title_xo = $txo + 2.1;
		$title_yo = $tyo + 5.3125 + 0.5;

		$pm->set_font("helvetica");
		$spine_title = array();

		if($series_record = lookup_designation($db->table_library_series, $item_record->{$db->field_library_series_ID}))
		{
			$series_title = $series_record->title . ($item_record->series_number > 0 ? " - #" . $item_record->series_number : "");
			$title_xo += $pm->text_wrap($title_xo, $title_yo, 0.3, $series_title, $tw) + 0.2;
			$spine_title[] = $series_title;
		}; // end if

		$title_xo += $pm->text_wrap($title_xo, $title_yo, 0.4, $item_record->title, $tw) + 0.2;
		$spine_title[] = $item_record->title;
		if($item_record->parallel_title != "")
		{
			$title_xo += $pm->text_wrap($title_xo, $title_yo, 0.3, $item_record->parallel_title, $tw);
			$spine_title[] = $item_record->parallel_title;
		}; // end if


		$pm->set_text_color(255, 255, 255);
		if($item_record->summary != "")
		{
			$summary_size = 0.2;

			while($pm->text_wrap($txo + 0.5, $tyo + 0.5, $summary_size, $item_record->summary, $tw) > 4.5)
			{
				$summary_size -= 0.005;
			}; // end while
			$summary_size += 0.005;
			$pm->set_text_color(0, 0, 0);
			$pm->text_wrap($txo + 0.5, $tyo + 0.5, $summary_size, $item_record->summary, $tw);
		}; // end if


		if($item_record->length > 0)
		{
			$title_xo += $pm->text($xo + 6.75 - 0.15, $tyo + 0.5, 0.15, $item_record->length . " minutes");
		}; // end if

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
			$image_filename = $db->cover_image_dir . "/" . substr($image_record->ID, 0, 1) . "/" . $image_record->ID . ".jpg";
			$image_dimensions = getimagesize($image_filename);
			$ratio = $image_dimensions[0] / $image_dimensions[1];

			$auto_size_amount = 4.25 - $title_xo;

			if($auto_size_amount < 0)
			{
				$image_h = 3 + $auto_size_amount;
			}
			else
			{
				$image_h = 3;
			}; // end if

			if($image_h * $ratio > $tw)
			{
				$iw = $tw;
				$ih = $tw / $ratio;
			}
			else
			{
				$iw = $image_h * $ratio;
				$ih = $image_h;
			}; // end if

			$pm->image_jpeg($image_filename, $xo + 6.75 - $ih, $yo + 5.3125 + 0.5, $iw, $ih);
		}; // end if


		$pm->set_draw_color(200, 200, 200);
		$pm->rect($xo + 1 - 0.125, $yo + 5.3125 + 0.5, $lw, $lh, FALSE, 0.1);

		$pm->complete_rotate(0);
		$pm->SetX(0);
		$pm->SetY(0);

		$spine_title_text = implode(" - ", $spine_title);

		$spine_suffix = "";
		while($pm->text_width(0.1875, "<b>" . $spine_title_text . "</b>") > 5.8)
		{
			$spine_title_text = substr($spine_title_text, 0, strlen($spine_title_text) - 1);
			$spine_suffix = "...";
		}; // end while
		$pm->text(5.15625, 0.25, 0.1875, "<b>" . $spine_title_text . $spine_suffix . "</b>", "left");


		$pm->text_wrap(10.6, 2, 0.15, ">>>>>>>>>  Crop page to 7.25\" wide by 10.5\" tall  <<<<<<<<<", "left", 1.4);


		$pm->stream(date("YmdHis") . ".pdf", TRUE);

		break;
	case "disc":

		$xo = 0.625;
		$yo = 0.5;
		$txo = 0;
		$tyo = -19;

		$bw = 2.25;
		$bh = 0.403;
		$lw = 2.781;
		$lh = 1;
		$dlw = 4.625;
		$dlh = 4.625;


		$lox = $xo + ($dlh / 2) + ($dlh / 7);
		$loy = $yo + ($dlw / 2) - ($lw / 2);

		$box = $lox + ($lh / 2) - ($bh / 2);
		$boy = $loy + (($lw - $bw) / 2);

		$tw = $dlw * 0.6;

		$barcode_type = "AB";
		$barcode_code = $item_record->barcode;
		$barcode_height = "60";
		$barcode_command = "save";
		include($db->common_image_dir . "/barcode.php");

		$color_lightgray = imagecolorallocate($image_barcode, 128, 128, 128);

		$pm->set_font("helvetica");

		$pm->set_font("times");

		$pm->SetX(0);
		$pm->SetY(0);

		//$pm->rect($xo + 0, $yo + 0, 10.5, 7.25);

		$pm->image_jpeg($barcode_filename, $box, $boy, $bw, $bh);
		unlink($barcode_filename);

		//$pm->rect($xo, $yo, $dlw, $dlh, FALSE, 2.3125);
		//$pm->rect($xo + 2, $yo + 2, 0.6875, 0.6875, FALSE, 0.34375);
		$pm->set_draw_color(200, 200, 200);
		$pm->rect($lox, $loy, $lw, $lh, FALSE, 0.1);

		$pm->text($box - 0.25, $boy + ($bw / 2), 0.15, "Immanuel Mission Library", "center");
		$pm->text($box + $bh + 0.02, $boy + ($bw/2), 0.15, "" . trim(preg_replace("/(.)/", "\\1     ", $barcode_code)) . "", "center");

		$title_xo = $xo + $dlh / 7;
		$title_yo = $yo + (($dlw - $tw) / 2);

		$pm->set_font("helvetica");
		$spine_title = array();

		if($series_record = lookup_designation($db->table_library_series, $item_record->{$db->field_library_series_ID}))
		{
			$series_title = $series_record->title . ($item_record->series_number > 0 ? " - #" . $item_record->series_number : "");
			$title_xo += $pm->text_wrap($title_xo, $title_yo, 0.2, $series_title, $tw, "center") + 0.06;
			$spine_title[] = $series_title;
			$tw *= 1.35;
			$title_yo = $yo + (($dlw - $tw) / 2);
		}; // end if

		$title_xo += $pm->text_wrap($title_xo, $title_yo, 0.3, $item_record->title, $tw, "center") + 0.06;


		if($item_record->parallel_title != "")
		{
			$tw *= 1.1;
			$title_yo = $yo + (($dlw - $tw) / 2);
			$title_xo += $pm->text_wrap($title_xo, $title_yo, 0.2, $item_record->parallel_title, $tw, "center");
		}; // end if


		$pm->text($xo + ($dlh / 2) - (0.15 / 2), $yo + ($dlw / 2) - 0.59375, 0.15, $item_record->length . " minutes", "right");

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
			$image_filename = $db->cover_image_dir . "/" . substr($image_record->ID, 0, 1) . "/" . $image_record->ID . ".jpg";
			$image_dimensions = getimagesize($image_filename);
			$ratio = $image_dimensions[0] / $image_dimensions[1];

			$image_h = 1;
			$iw = $image_h * $ratio;
			$ih = $image_h;
			$pm->image_jpeg($image_filename, $xo + ($dlh / 2) - ($ih / 2), $yo + ($dlw / 2) + 0.59375, $iw, $ih);
		}; // end if

		if($style_record->image != "")
		{
			switch($style_record->image)
			{
				case "dvd":
					$image_filename = $db->image_dir . "/dvd.jpg";
					break;
				case "cd":
					$image_filename = $db->image_dir . "/cd.jpg";
					break;
			}; // end switch

			$image_dimensions = getimagesize($image_filename);
			$ratio = $image_dimensions[1] / $image_dimensions[0];
			$image_w = 0.5;
			$ih = $image_w * $ratio;
			$iw = $image_w;
			$pm->image_jpeg($image_filename, $xo + $dlh - 0.2 - $ih, $yo + ($dlw / 2) - ($iw / 2), $iw, $ih);
		}; // end if


		$pm->stream(date("YmdHis") . ".pdf", TRUE);

		break;
	case "map":

		$xo = 1;
		$yo = 0.5;
		$txo = 0;
		$tyo = -19;

		$bw = 2.25;
		$bh = 0.403;
		$lw = 2.781;
		$lh = 1;
		$dlw = 4.625;
		$dlh = 4.625;


		$lox = $xo;
		$loy = $yo;

		$box = $lox + ($lh / 2) - ($bh / 2);
		$boy = $loy + (($lw - $bw) / 2);

		$tw = 7.5;

		$barcode_type = "AB";
		$barcode_code = $item_record->barcode;
		$barcode_height = "60";
		$barcode_command = "save";
		include($db->common_image_dir . "/barcode.php");

		$color_lightgray = imagecolorallocate($image_barcode, 128, 128, 128);

		$pm->set_font("helvetica");

		$pm->set_font("times");

		$pm->SetX(0);
		$pm->SetY(0);

		//$pm->rect($xo + 0, $yo + 0, 10.5, 7.25);

		$pm->image_jpeg($barcode_filename, $box, $boy, $bw, $bh);
		unlink($barcode_filename);

		//$pm->rect($xo, $yo, $dlw, $dlh, FALSE, 2.3125);
		//$pm->rect($xo + 2, $yo + 2, 0.6875, 0.6875, FALSE, 0.34375);
		$pm->set_draw_color(200, 200, 200);
		$pm->rect($lox, $loy, $lw, $lh, FALSE, 0.1);

		$pm->text($box - 0.25, $boy + ($bw / 2), 0.15, "Immanuel Mission Library", "center");
		$pm->text($box + $bh + 0.02, $boy + ($bw/2), 0.15, "" . trim(preg_replace("/(.)/", "\\1     ", $barcode_code)) . "", "center");

		$title_xo = $xo + 1.3;
		$title_yo = $yo;

		$pm->set_font("helvetica");
		$spine_title = array();

		$pm->text_wrap($xo, $loy + $lw + 0.5, 0.2, "IMPORTANT: To checkout remove map from sleave.  Do not remove this page or plastic sleeve.", 7.5 - $loy - $lw - 0.75, "left");

		$call_number_parts = explode(",", $item_record->call_number);

		$cnx = 0.25;

		foreach($call_number_parts as $key=>$call_number_line)
		{
			$pm->text($cnx, 7.5, 0.1875, "<b>" . $call_number_line . "</b>", "left");
			$cnx+=0.2;
		}; // end foreach


		if($series_record = lookup_designation($db->table_library_series, $item_record->{$db->field_library_series_ID}))
		{
			$series_title = $series_record->title . ($item_record->series_number > 0 ? " - #" . $item_record->series_number : "");
			$title_xo += $pm->text_wrap($title_xo, $title_yo, 0.22, $series_title, $tw, "left") + 0.06;
			$spine_title[] = $series_title;
		}; // end if

		$title_xo += $pm->text_wrap($title_xo, $title_yo, 0.36, $item_record->title, $tw, "left") + 0.06;


		if($item_record->parallel_title != "")
		{
			$title_xo += $pm->text_wrap($title_xo, $title_yo, 0.22, $item_record->parallel_title, $tw, "left");
		}; // end if

		if($item_record->summary != "")
		{
			$title_xo += 0.2;
			$title_xo += $pm->text_wrap($title_xo, $title_yo, 0.2, $item_record->summary, $tw, "left");
		}; // end if

		$pm->stream(date("YmdHis") . ".pdf", TRUE);
		//$pm->stream();

		break;
}; // end switch


?>
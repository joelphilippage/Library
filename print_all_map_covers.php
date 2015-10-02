<?

include_once(dirname(__FILE__) . "/first.php");

$item_ID = urldecode($query[0]);

require_once($db->common_include_dir . "/fonts/times.php");
include_once($db->common_include_dir . "/pdf_maker.php");


$item_sql = "
	SELECT
		*
	FROM
		`" . $db->table_library_item . "`
	WHERE
		`category_ID`='50'
	ORDER BY
		`barcode` ASC
";

$item_result = mysql_query($item_sql, $mysql->link);

$first = TRUE;

while($item_record = mysql_fetch_object($item_result))
{
	if(! $first)
	{
		$pm->new_page();
	}; // end if

	output_cover($item_record);
	
	$first = FALSE;
}; // end while

$pm->stream(date("YmdHis") . ".pdf", TRUE);



function output_cover($item_record)
{
	global $db, $mysql, $pm;

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


}; // end function




?>
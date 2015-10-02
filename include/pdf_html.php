<?

// KBC DB: Version 5.x
// Simple.  Original.  Elegant.  By flash services.

// The tools in this file have the same usage as the same ones in the pdf_maker.php.
// The different is it will make an HTML page to look just like it would a PDF file.

class pdf_dhtml
{
	function pdf_dhtml($xo, $yo, $f)
	{
		$this->xo = $xo;
		$this->yo = $yo;
		$this->f = $f;
	} // end constructor

	function text($x, $y, $s, $t, $a = "left", $js = "")
	{
		switch($a)
		{
			case "left":
				?><div style="width:0%;position:absolute;top:<?= $x + $this->xo; ?>in;left:<?= $y + $this->yo; ?>in;font-family:<?= $this->f; ?>;font-size:<?= $s; ?>in;" <?= $js; ?>><nobr><?= $t; ?></nobr></div><?
				break;
			case "center":
				?><div style="width:11in;text-align:center;position:absolute;top:<?= $x + $this->xo; ?>in;left:<?= $y + $this->yo - 5.5; ?>in;font-family:<?= $this->f; ?>;font-size:<?= $s; ?>in;" <?= $js; ?>><?= $t; ?></div><?
				break;
			case "right":
				?><div style="width:11in;text-align:right;position:absolute;top:<?= $x + $this->xo; ?>in;left:<?= $y + $this->yo - 11; ?>in;font-family:<?= $this->f; ?>;font-size:<?= $s; ?>in;" <?= $js; ?>><?= $t; ?></div><?
				break;
		}; // end switch
	} // end method

	function input($x, $y, $s, $t, $a = "left", $w = 2, $n, $js = "", $l = 20, $i = 0)
	{
		?><input <?= $js; ?> tabindex="<?= $i; ?>" name="<?= $n; ?>" style="z-index:1000;padding:0px 1px 0px 0px;text-align:<?= $a; ?>;position:absolute;width:<?= $w; ?>in;height:<?= $s + 0.1; ?>in;border-width:0px;top:<?= $x + $this->xo; ?>in;left:<?= $y + $this->yo; ?>in;font-family:<?= $this->f; ?>;font-size:<?= $s; ?>in;background-color:transparent;" value="<?= htmlspecialchars($t); ?>" maxlength="<?= $l; ?>" onfocus="this.select();"><?
	} // end method

	function listbox($x, $y, $s, $t, $a = "left", $w = 2, $n = "", $js = "", $l = 20, $options = array(), $i = 0)
	{
		?>
		<select tabindex="<?= $i; ?>" name="<?= $n; ?>" style="padding:0px;text-align:<?= $a; ?>;position:absolute;width:<?= $w; ?>in;height:<?= $s + 0.1; ?>in;border:0px;top:<?= $x + $this->xo; ?>in;left:<?= $y + $this->yo; ?>in;font-family:<?= $this->f; ?>;font-size:<?= $s; ?>in;background-color:transparent;" value="<?= htmlspecialchars($t); ?>" <?= $js; ?> size="<?= $l; ?>">
			<?
				foreach($options as $value=>$caption)
				{
					?>
					<option value="<?= $value; ?>"<?= ($value == $t ? " selected" : ""); ?>><?= $caption; ?></option>
					<?
				}; // end if
			?>
		</select>
		<?
	} // end method

	function text_wrap($x, $y, $s, $t, $w, $a = "left")
	{
		switch($a)
		{
			case "left";
			case "center";
			case "right";
				$align = $a;
				break;
			case "full";
				$align = "justify";
				break;
		}; // end switch

		?><div style="position:absolute;line-height:100%;width:<?= $w; ?>in;top:<?= $x + $this->xo; ?>in;left:<?= $y + $this->yo; ?>in;"><p align="<?= $align; ?>" style="font-family:<?= $this->f; ?>;font-size:<?= $s; ?>in;"><?= $t; ?></p></div><?
	} // end method

	function image_jpeg($u, $x, $y, $w, $h)
	{
		?><img style="position:absolute;top:<?= $x + $this->xo; ?>in;left:<?= $y + $this->yo; ?>in;width:<?= $w; ?>in;height:<?= $h; ?>in;" src="<?= $u; ?>"><?
	} // end method

	function rect($x, $y, $w, $h, $f = 0, $id="", $js = "")
	{
		global $image_url;
		?><div <?= ($id != "" ? ' id="' . $id . '" ' : ''); ?> <?= $js; ?> style="position:absolute;top:<?= $x + $this->xo; ?>in;left:<?= $y + $this->yo; ?>in;width:<?= $w; ?>in;height:<?= $h; ?>in;<?= ($f === 1 ? "background-color:000000;" : ""); ?>border-color:000000;border-style:solid;border-width:<?= ($f === -1 ? "0" : "1"); ?>px;"><img src="<?= $image_url; ?>/void.gif" style="width:1px;height:1px;"></div><?
	} // end method

	function line($x1, $y1, $x2, $y2)
	{
		?><div style="position:absolute;top:<?= $x1 + $this->xo; ?>in;left:<?= $y1 + $this->yo; ?>in;width:<?= $y2 - $y1; ?>in;height:<?= $x2 - $x1; ?>in;border-color:000000;border-style:solid;border-width:1px 0px 0px 0px;"></div><?
	} // end method

}; // end class


?>
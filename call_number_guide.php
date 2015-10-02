<?

include_once(dirname(__FILE__) . "/first.php");

//$db->current_sub_application = "librarian";

if(! $accounts->account_record->ID)
{
	exit_error("Not Signed In", "You must be signed into perform that action.");
}; // end if

if($accounts->account_record->librarian != "Y")
{
	exit_error("Access Denied", "Only a librarian can perform that action.");
}; // end if

include_once(dirname(__FILE__) . "/top.php");

?><div class="title">Call Number Guide</div><?

?>
&nbsp; <b>?</b> = letter &nbsp; <b>#</b> = number<br><br>

<table cellspacing="0" cellpadding="10" border="1" width="100%">
	<tr>
		<td valign="top" width="25%">
			<B>Call Number Template</B>
		</td>
		<td valign="top">
			<B>Explanation</B>
		</td>
	</tr>
	<tr>
		<td valign="top">
			<B>E</B><br>
			?
		</td>
		<td valign="top">
			Children's Easy<br>
			Sorted by first letter of Author's last name
		</td>
	</tr>
	<tr>
		<td valign="top">
			<B>J</B><br>
			<B>FIC</B><br>
			???
		</td>
		<td valign="top">
			Juvenile Fiction<br>
			Sorted by Author's last name
		</td>
	</tr>
	<tr>
		<td valign="top">
			<B>J</B><br>
			<B>FIC</B><br>
			???<br>
			<B>[S]</B>
		</td>
		<td valign="top">
			Juvenile Fiction [Special Collections]<br>
			Sorted by Author's last name
		</td>
	</tr>
	<tr>
		<td valign="top">
			<B>J</B><br>
			<B>FIC</B><br>
			???<br>
			<B>[O]</B>
		</td>
		<td valign="top">
			Juvenile Fiction [Oversize Editions]<br>
			Sorted by Author's last name
		</td>
	</tr>
	<tr>
		<td valign="top">
			<B>J</B><br>
			###<br>
			???
		</td>
		<td valign="top">
			Juvenile Non-Fiction<br>
			Sorted by Dewey Decimal System then Author's last name
		</td>
	</tr>
	<tr>
		<td valign="top">
			<B>J</B><br>
			###<br>
			???<br>
			<B>[S]</B>
		</td>
		<td valign="top">
			Juvenile Non-Fiction [Special Collections]<br>
			Sorted by Dewey Decimal System then Author's last name
		</td>
	</tr>
	<tr>
		<td valign="top">
			<B>J</B><br>
			<B>BIO</B><br>
			???
		</td>
		<td valign="top">
			Juvenile Biographies<br>
			Sorted by last name of who it is a biography of
		</td>
	</tr>
	<tr>
		<td valign="top">
			<B>FIC</B><br>
			???
		</td>
		<td valign="top">
			Adult Fiction<br>
			Sorted by Author's last name
		</td>
	</tr>
	<tr>
		<td valign="top">
			<B>FIC</B><br>
			???<br>
			<B>[S]</B>
		</td>
		<td valign="top">
			Adult Fiction [Special Collections]<br>
			Sorted by Author's last name
		</td>
	</tr>
	<tr>
		<td valign="top">
			###<br>
			???
		</td>
		<td valign="top">
			Adult Non-Fiction<br>
			Sorted by Dewey Decimal System then Author's last name
		</td>
	</tr>
	<tr>
		<td valign="top">
			###<br>
			???<br>
			<B>[S]</B>
		</td>
		<td valign="top">
			Adult Non-Fiction [Special Collections]<br>
			Sorted by Dewey Decimal System then Author's last name
		</td>
	</tr>
	<tr>
		<td valign="top">
			###<br>
			???<br>
			<B>[O]</B>
		</td>
		<td valign="top">
			Adult Non-Fiction [Oversize Editions]<br>
			Sorted by Dewey Decimal System then Author's last name
		</td>
	</tr>
	<tr>
		<td valign="top">
			<B>BIO</B><br>
			???
		</td>
		<td valign="top">
			Adult Biographies<br>
			Sorted by last name of who it is a biography of
		</td>
	</tr>
	<tr>
		<td valign="top">
			<B>REF</B><br>
			???
		</td>
		<td valign="top">
			Reference (Dictionaries, etc.)<br>
			Sorted by publisher/author.
		</td>
	</tr>
	<tr>
		<td valign="top">
			<B>REF</B><br>
			<B>MAP</B><br>
			???
		</td>
		<td valign="top">
			Reference - Maps/Atlases<br>
			Sorted by height of book
		</td>
	</tr>
	<tr>
		<td valign="top">
			<B>REF</B><br>
			<B>BIB</B><br>
			???
		</td>
		<td valign="top">
			Reference - Bible<br>
			Sorted by Author's last name
		</td>
	</tr>
	<tr>
		<td valign="top">
			<B>P</B><br>
			???<br>
			####<br>
			???
		</td>
		<td valign="top">
			Periodicals (Magazines)<br>
			Sorted by publication, then year, then month/season
		</td>
	</tr>
	<tr>
		<td valign="top">
			<B>A</B><br>
			???
		</td>
		<td valign="top">
			Audio Music<br>
			Sorted by Artist's last name
		</td>
	</tr>
	<tr>
		<td valign="top">
			<B>V</B><br>
			<B>CHI</B><br>
			???
		</td>
		<td valign="top">
			Video - Children<br>
			Sorted by production company
		</td>
	</tr>
	<tr>
		<td valign="top">
			<B>V</B><br>
			<B>JUV</B><br>
			???
		</td>
		<td valign="top">
			Video - Juvenile<br>
			Sorted by production company
		</td>
	</tr>
	<tr>
		<td valign="top">
			<B>V</B><br>
			<B>FAM</B><br>
			???
		</td>
		<td valign="top">
			Video - Family<br>
			Sorted by production company
		</td>
	</tr>
	<tr>
		<td valign="top">
			<B>V</B><br>
			<B>EDU</B><br>
			???
		</td>
		<td valign="top">
			Video - Educational<br>
			Sorted by production company
		</td>
	</tr>
	<tr>
		<td valign="top">
			<B>M</B><br>
			???
		</td>
		<td valign="top">
			Map<br>
			Sorted by continent, country, state/province, etc.
		</td>
	</tr>
	<tr>
		<td valign="top">
			<B>R</B><br>
			???
		</td>
		<td valign="top">
			Record<br>
			Not sorted
		</td>
	</tr>
</table>


<?


include_once(dirname(__FILE__) . "/bottom.php");

?>
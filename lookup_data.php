<?

include_once(dirname(__FILE__) . "/first.php");

$lookup_type = strtolower($query[0]);
$lookup_value = $query[1];


$db->settings->library_last_lookup_type = $lookup_type;

if($lookup_type == "auto")
{
	if(is_valid_isbn_number($lookup_value))
	{
		$lookup_type = "isbn";
	}
	else
	{
		$lookup_type = "title";
	}; // end if
}; // end if

switch($lookup_type)
{
	case "isbn":
		save_db_settings();
		//echo "isbn lookup<br><br>";
		$lookup_url = "http://isbndb.com/api/books.xml?access_key=" . $db->isbndb_access_key . "&index1=isbn&results=details,texts,subjects,authors&value1=" . $lookup_value . "";
		if(! $data = @simplexml_load_file($lookup_url))
		{
			exit("ERROR: Lookup server is down<br><br>" . $lookup_url);
		}; // end if
		break;
	case "title":
		save_db_settings();
		//echo "title lookup<br><br>";
		if(! $data = @simplexml_load_file("http://isbndb.com/api/books.xml?access_key=" . $db->isbndb_access_key . "&index1=title&results=details,texts,subjects,authors&value1=" . $lookup_value . ""))
		{
			exit("ERROR: Lookup server is down");
		}; // end if
		break;
	default:
		?>
		<script language="javascript">
			alert("Prefill Method Not Supported: <?= $lookup_type; ?>");
		</script>
		<?
		break;
}; // end switch

$results_info = $data->BookList->attributes();
$results_data = $data->BookList->BookData;

$db->lookup_iframe = TRUE;

include_once(dirname(__FILE__) . "/top.php");

//echo "<pre>";
//print_r($data);
//echo "</pre>";

if(isset($data->BookList) && $results_info->total_results > 0)
{
	foreach($results_data as $book)
	{
		$book_info = $book->attributes();
		?>
		<div><a href="javascript:" onclick="parent.do_isbndb_prefill('<?= $book_info->book_id; ?>');"><?= $book->Title; ?></a> <?= $book->AuthorsText; ?></div>
		<div class="small"><?= $book->PublisherText; ?></div>
		<div class="tiny indent"><?= $book->Summary; ?></div>
		<br><br><?
		
	}; // end foreach
	?>
	<script language="javascript">
		parent.lookup_done(true);
	</script>
	<?
}
else
{
	?>None Found<Br><br><span class="small" style="color:999999;">(<?= time(); ?>)</span><?
	?>
	<script language="javascript">
		parent.lookup_done(false);
	</script>
	<?
}; // end if



//? ><pre>< ?
//print_r($data);
//? ></pre>< ?

include_once(dirname(__FILE__) . "/bottom.php");

exit();


?>
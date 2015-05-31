<?php
/****************
PVA PUBLIC SITE SCRAPER
BY: HACKERMAN
*****************/

ini_set('max_execution_time', 0);
require("simple_html_dom.php");
require("dbconnect.php");
$db = mysqli_connect($host, $user, $pass);
mysqli_query($db,"use $database");


//$table = the table in your database, $rows = the number of rows, $counter = which row to start on
$table = 'newparceldata';
$rows = 289403;
$counter =1;
define(SCRAPEURL, "https://jeffersonpva.ky.gov/property-search/property-details/{LRSN}/?StrtNum={STREETNUM}&Single=1");



while($counter <= $rows) {
	//pull current row from the database
    $query = "SELECT LRSN,remf_HOUSE FROM $table WHERE id = $counter";
	$response = mysqli_query($db, $query);
	while ($row = mysqli_fetch_assoc($response)) {
    	$lrsn = $row['LRSN'];
    	$stnum = $row['remf_HOUSE'];
	}
	
	//FIX: if street num does not exist, use a 1... sometimes it works
	if ($stnum == ''){
		$stnum = "1";
	}

	//scrape the values off the page and put them back into the db
	$data = getPage($lrsn, $stnum);
	echo $counter . " - [Price: " . $data[2] . " Acres: " . $data[3] . "]" . PHP_EOL;
	$query = "UPDATE $table SET pvaprice='" . $data[2] . "',acres = '". $data[3] . "' WHERE id = $counter";
	$results = mysqli_query($db, $query);

	$counter++;
} 



//Heres where the scrape of the page happens, look through the code for <dd> tags. 2 and 3 are the prices and acreage
//For a full list of whats available on that page, open console and run $('dd') to see whats available
function getPage($lrsn, $stnum) {
	$fullurl = str_replace("{LRSN}", $lrsn, SCRAPEURL);
	$fullurl = str_replace("{STREETNUM}", $stnum, $fullurl);
	$html = file_get_html($fullurl);
	$elements = array();
	
	foreach($html->find('dd') as $element){
		array_push($elements, $element->innertext);
	}
	return $elements;
}

?>
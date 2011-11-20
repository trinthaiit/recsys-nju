<?php
require('extract_keywords.php');
require('dbconfig.php');

function str_not_empty($str){
    $trimed = trim($str);

    return $trimed != '';
}

function is_stopword($str){
	@$fp = fopen("stopwords.txt",'rb');
	while(!feof($fp)){
		$stopword = trim(fgets($fp,999));
		if($str == $stopword)
			return false;
	}
	return true;
}

$con = mysql_connect($db_host , $db_user, $db_pass);
if(!$con){
    die(mysql_error());
}

mysql_select_db('bagsok');
//is DISTINCT needed here?
$result = mysql_query("select keyword from browse where keyword is not null");

$all = 0;
$kcount = 0;
$number = 0;
if(!$result){
    die('no result available');
}else{
    $keyword_count = array();
    while($row = mysql_fetch_array($result)){
        $all++; //increment all counter

        $keyword_str = $row[0];
        //$keyword_str = extract_keywords($search_url);
        $keywords = explode(' ', $keyword_str);
        $keywords = array_filter($keywords, "is_stopword");

        if(!count($keywords))
            continue;

        $number += count($keywords);
        $kcount++; //inrement keyword counter
        foreach($keywords as $keyword){
            if(isset($keyword_count[$keyword]))
                $keyword_count[$keyword]++;
            else
                $keyword_count[$keyword] = 1;
        }
    }

    //sort keyword - count array
    arsort($keyword_count);

    //print results
    echo '<table border="1px"><tr><th>keyword</th><th>occurrence</th></tr>';
    foreach($keyword_count as $key => $count){
        echo "<tr><td>$key</td><td>$count</td></tr>";
         mysql_query("insert into keyword_word values
         ('".$key."','".$count."')");
         $result1 = mysql_query("SELECT * FROM browse WHERE keyword LIKE '%".$key."%'");
         if(!$result1){
   			 //die('no result available');
		 }
         //$num_results1 = $result1->num_rows;
         else{
	         while($row1 = mysql_fetch_array($result1)){
	         	//$row1 = $result1->fetch_assoc();
	         	$uri = stripcslashes($row1['uri']);
	         	//echo "$uri <br />";
	         	mysql_query("insert into keyword_product values
	         	('".$key."','".$uri."')");
	         }
         }
    }
    echo '</table>';
}
$keyword_num = count($keyword_count);
$ave_num = floatval($number) / $kcount;
echo "all keywords: {$keyword_num}<br />";
echo "all entries: $all, entries with keywords: $kcount<br />";
echo "average keywords per entry: $ave_num<br />";
echo floatval($kcount) / $all;

mysql_close($con);
?>

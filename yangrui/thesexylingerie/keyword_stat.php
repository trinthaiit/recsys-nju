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

mysql_select_db('thesexylingerie');
mysql_query("delete from keyword");

$result = mysql_query("SELECT DISTINCT refer FROM user WHERE refer IS NOT NULL");

$all = 0;
$kcount = 0;
$number = 0;
if(!$result){
    die('no result available');
}else{
    $keyword_count = array();
    while($row = mysql_fetch_array($result)){
        $all++; //increment all counter

        $search_url = $row[0];
        $keyword_str = extract_keywords($search_url);
        
        /* digital and punctuation filter*/
		$keyword_str = preg_replace('/\s/',' ',preg_replace("/[[:punct:]]/",' ',strip_tags(html_entity_decode(str_replace(array('£¿','£¡','£¤','£¨','£©','£º','¡®','¡¯','¡°','¡±','¡¶','¡·','£¬','¡­','¡£','¡¢','nbsp','Â£','-'),'',$keyword_str),ENT_QUOTES,'UTF-8'))));
		$keyword_str = preg_replace("/[0-9]/", "", $keyword_str);
		
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
        mysql_query("insert into keyword(keyword,occur) values('".$key."',".$count.")");
    }
    echo '</table>';
}
$keyword_num = count($keyword_count);
$ave_num = floatval($number) / $kcount;

mysql_close($con);
?>

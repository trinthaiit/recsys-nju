<?php
/**
 * 此文件完成计算历史数据库中单词的关联度 并将达到threshold的相关keyword写入keyword_link表的功能
 * threshold可自由设定
 */
set_time_limit (1000);
$threshold = 0.2;

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

//mysql_select_db('thesexylingerie');
mysql_select_db('thesexylingerie_test');

//mysql_query("delet from keyword_link");
//$result = mysql_query("SELECT DISTINCT refer FROM user WHERE refer IS NOT NULL");
mysql_query("truncate keyword_link");
$result = mysql_query("SELECT DISTINCT keywords FROM preprocessed_user_train");

$all = 0;
$kcount = 0;
$number = 0;
if(!$result){
    die('no result available');
}else{
    $keyword_count = array();
    while($row = mysql_fetch_array($result)){
        $all++; //increment all counter

        //$search_url = $row[0];
        //$keyword_str = extract_keywords($search_url);
        $keyword_str = $row[0];
        
        /* digital and punctuation filter*/
		$keyword_str = preg_replace('/\s/',' ',preg_replace("/[[:punct:]]/",' ',strip_tags(html_entity_decode(str_replace(array('？','！','￥','（','）','：','‘','’','“','”','《','》','，','…','。','、','nbsp','拢','-'),'',$keyword_str),ENT_QUOTES,'UTF-8'))));
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
    echo '<table border="1px"><tr><th>keyword</th><th>count</th><th>keyword_expand</th><th>count</th><th>jaccard</th></tr>';
    foreach($keyword_count as $key => $count){
    	foreach($keyword_count as $key1 => $count1){
    		if($key != $key1 && $key != null && $key1 != null){
	    		//$nAB = mysql_num_rows(mysql_query("select id from user where keyword like '%".$key."%".$key1."%' or keyword like '%".$key1."%".$key."%'"));
	    		$nAB = mysql_num_rows(mysql_query("select id from preprocessed_user_train where keywords like '%".$key."%".$key1."%' or keywords like '%".$key1."%".$key."%'"));
	    		if($count + $count1 - $nAB != 0)
	    			$jaccard = $nAB/($count + $count1 - $nAB);
	    		else
	    			$jaccard = 1;
	    		//if($jaccard > 0.2){
	    		if($jaccard > $threshold){
	       	 		echo "<tr><td>$key</td><td>$count</td><td>$key1</td><td>$count1</td><td>$jaccard</td></tr>";
	       	 		mysql_query("INSERT INTO keyword_link VALUES ('".$key."', '".$count."', '".$key1."','".$count1."','".$jaccard."')");
	    		}
    		}
    	}
    }
    echo '</table>';
}
mysql_close($con);
?>

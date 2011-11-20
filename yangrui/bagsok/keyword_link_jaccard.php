<?php
	require 'keywords_aggregate.php';
	require 'dbconfig.php';
	set_time_limit (1000);
	
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
	
	$query = "select keyword from browse where keyword is not null";
	$result = mysql_query($query);
	
	if(!$result){
	    die('no result available');
	}
	
	$all = 0;
	$kcount = 0;
	$number = 0;
	
	while($row = mysql_fetch_array($result)){
		$all++;
        $keyword_str = $row[0];
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
	
	arsort($keyword_count);
	
	//print results
    echo '<table border="1px"><tr><th>keyword</th><th>keyword1</th><th>occurrence</th><th>occurrence1</th></tr>';
    foreach($keyword_count as $key => $count){
    	foreach($keyword_count as $key1 => $count1){
    		$result_temp = mysql_query("SELECT keyword from browse where keyword like '%".$key."%".$key1."%' and keyword is not null");
    		if(!$result_temp){
    			//die('no result available');
    		}
    		else{
	    		$num_ab = mysql_num_rows($result_temp);
	    		if($key != $key1 && $count+$count1-$num_ab != 0){
	    			$jaccard = $num_ab/($count+$count1-$num_ab);
	    			if($jaccard > 0.2){
	        			echo "<tr><td>$key</td><td>$key1</td><td>$count</td><td>$count1</td><td>$jaccard</td></tr>";
	        			mysql_query("insert into keyword_link_jaccard1 values('".$key."','".$key1."')");
	    			}
	    		}
    		}
    	}
    }
    echo '</table>';

	$keyword_num = count($keyword_count);
	$ave_num = floatval($number) / $kcount;
	echo "all keywords: {$keyword_num}<br />";
	echo "all entries: $all, entries with keywords: $kcount<br />";
	echo "average keywords per entry: $ave_num<br />";
	echo floatval($kcount) / $all;
	
	mysql_close($con);
?>
	
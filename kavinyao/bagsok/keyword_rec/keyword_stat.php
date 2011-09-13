<?php
require('extract_keywords.php');
require('dbconfig.php');

function str_not_empty($str){
    $trimed = trim($str);

    return $trimed != '';
}

$con = mysql_connect($db_host , $db_user, $db_pass);
if(!$con){
    die(mysql_error());
}

mysql_select_db('bagsok');

$result = mysql_query("SELECT DISTINCT refer FROM userinfo WHERE refer IS NOT NULL");

$all = 0;
$kcount = 0;
if(!$result){
    die('no result available');
}else{
    $keyword_count = array();
    while($row = mysql_fetch_array($result)){
        $all++; //increment all counter

        $search_url = $row[0];
        $keyword_str = extract_keywords($search_url);
        $keywords = explode(' ', $keyword_str);
        $keywords = array_filter($keywords, "str_not_empty");

        if(!count($keywords))
            continue;

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
    }
    echo '</table>';
}
$keyword_num = count($keyword_count);
echo "all keywords: {$keyword_num}<br />";
echo "all entries: $all, entries with keywords: $kcount<br />";
echo floatval($kcount) / $all;

mysql_close($con);
?>

<?php
require('dbconfig.php');
require('extract_keywords.php');
require('keywords_aggregate.php');

$con = mysql_connect($db_host , $db_user, $db_pass);
if(!$con){
    die(mysql_error());
}

mysql_select_db('bagsok');

$TOTAL = 2000;

$result = mysql_query("SELECT id, refer FROM userinfo WHERE refer IS NOT NULL LIMIT 2000, $TOTAL");

$count = 0;
if(!$result){
    echo 'no result available';
}else{
    //echo '<table border="1px">';
    $all = array();
    $all_keywords_arr = array();
    while($row = mysql_fetch_array($result)){
        $keywords_str = extract_keywords($row['refer']);
        if($keywords_str){
            $count++;

            $keywords_arr = keywords_array($keywords_str);
            $all_keywords_arr[] = $keywords_arr;

            //echo "<tr><td>$keywords_str</td><td><pre>";
            //print_r($keywords_arr);
            $all = array_merge($all, $keywords_arr);
            //echo "</pre></td></tr>";
        }
    }
    //echo '</table>';
    echo "entries with keywords/total entries: $count / $TOTAL";

    $all = array_unique($all);
    //sort($all);
    $keyword_occr = array();
    foreach($all as $keyword){
        $occurrence = occurrence(array($keyword), $all_keywords_arr);
        $keyword_occr[$keyword] = $occurrence;
    }

    arsort($keyword_occr);
    echo '<table border="1px"><tr><th>Keywords of size 2</th><th>occurrence</th><th>ratio(=intersection/union)</th></tr>';
    foreach($all_keywords_arr as $keywords_arr){
        $subsets_2 = generate_next($keywords_arr, expand_dimension($keywords_arr)); 
        foreach($subsets_2 as $subset_2){
            $occur_2 = occurrence($subset_2, $all_keywords_arr);
            //print elements in subset_2
            echo '<tr><td>';
            //$max_elem_occr = 0;
            $union_occur = 0;
            foreach($subset_2 as $elem){
                echo $elem . "({$keyword_occr[$elem]}) ";
                $union_occur += $keyword_occr[$elem];
            }
            $ratio = floatval($occur_2) / ($union_occur - $occur_2);
            //print occurrence and ratio
            echo "</td><td>$occur_2</td><td>$ratio</td></tr>";
        }
        //split sets
        echo '<tr><td> </td><td> </td></tr>';
    }
    echo '</table>';

    echo '<table border="1px"><tr><th>Keywords</th><th>occurrence</th></tr>';
    foreach($keyword_occr as $key => $occur){
        echo "<tr><td>$key</td><td>$occur</td></tr>";
    }
    echo'</table>';
}

mysql_close($con);
?>

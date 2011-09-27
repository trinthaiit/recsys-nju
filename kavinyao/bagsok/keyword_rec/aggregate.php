<?php
$verbose = isset($_GET['verbose']);

require('dbconfig.php');
require('extract_keywords.php');
require('aggregate_utility.inc.php');

define("THRESHOLD", 0.4);

$con = mysql_connect($db_host , $db_user, $db_pass);
if(!$con){
    die(mysql_error());
}

mysql_select_db('bagsok');

$TOTAL = 100;

$result = mysql_query("SELECT id, refer FROM userinfo WHERE refer IS NOT NULL LIMIT 2000, $TOTAL");

$count = 0;
if(!$result){
    echo 'no result available';
}else{
    //STEP1: pre-process
    //1. get all keyword strings
    //2. get all splited keyword array
    $all_splitted_keyword_sets = array();
    while($row = mysql_fetch_array($result)){
        $keyword_string = extract_keywords($row['refer']);
        if($keyword_string){
            $count++;
            $all_splitted_keyword_sets[] = keywords_array($keyword_string);
        }
    }
    echo "<p>entries with keywords/total entries: $count / $TOTAL</p>";
    echo "<p>Threshold = ".THRESHOLD."</p>";

    //STEP2: aggregate
    $kwset_occur_mapping = array();
    foreach($all_splitted_keyword_sets as $splitted_keyword_set){
        echo '<h2>'.kwset_to_string($splitted_keyword_set).'</h2>';

        //deal with keyword sets of size 1 first
        $current_generation = array();
        $size1_set = expand_dimension($splitted_keyword_set);
        foreach($size1_set as $keyword_set){
            if(!array_key_exists(kwset_to_string($keyword_set), $kwset_occur_mapping)){
                $occur = occurrence($keyword_set, $all_splitted_keyword_sets);
                $kwset_occur_mapping[kwset_to_string($keyword_set)] = $occur;
            }
            //to use Jaccard Index, do trick here
            $current_generation[] = array($keyword_set, array($keyword_set, $keyword_set));
        }

        $round_count = 1;
        //now aggregation process begins
        echo '<table border="1px"><tr><th>Keyword Set</th><th>Occurrence</th><th>Jaccard Index</th><th>Support sets</th></tr>';
        while(count($current_generation) > 0){
            $candidates = array();
            foreach($current_generation as $group){
                $keyword_set = $group[0];
                $kwset_string = kwset_to_string($keyword_set);//avoid unnecessary typing
                $support_sets = $group[1];

                //if keyword set already in the support set
                //skip it
                if(array_key_exists($kwset_string, $candidates))
                    continue;

                //in case the occurrence is not calculated
                if(!array_key_exists($kwset_string, $kwset_occur_mapping)){
                    $occur = occurrence($keyword_set, $all_splitted_keyword_sets);
                    $kwset_occur_mapping[$kwset_string] = $occur;
                }

                //calculate the index here, finally!
                $intersection_length = $kwset_occur_mapping[$kwset_string];
                $suppset_occur1 = $kwset_occur_mapping[kwset_to_string($support_sets[0])];
                $suppset_occur2 = $kwset_occur_mapping[kwset_to_string($support_sets[1])];
                $union_length =  $suppset_occur1 + $suppset_occur2 - $intersection_length;
                $jaccard_index = floatval($intersection_length) / $union_length;

                if($verbose){
                    echo 'processing ['.$kwset_string.'] index = '.$jaccard_index.'<br />';
                }

                //check if index is no less than THRESHOLD
                if($jaccard_index >= THRESHOLD){
                    //TODO: add keyword_set, uri to databse
                    $candidates[$kwset_string] = $keyword_set;
                    if($round_count > 1){
                        echo '<tr><td>'.$kwset_string.'</td><td>'.$intersection_length.'</td><td>'.$jaccard_index.'</td><td>'.kwset_to_string($support_sets[0]).'['.$suppset_occur1.']<br />'.kwset_to_string($support_sets[1]).'['.$suppset_occur2.']</td></tr>';
                    }
                }
            }
            
            //string keys to numeric
            $candidates = array_values($candidates);
            //aggregate to size N+1
            $current_generation = keyword_aggregate($candidates);
            $round_count++;
        }
        echo '</table>';
    }
}

mysql_close($con);
?>

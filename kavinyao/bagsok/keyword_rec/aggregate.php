<?php
require('dbconfig.php');
require('extract_keywords.php');
require('aggregate_utility.inc.php');

define("THRESHOLD", 0.4);

$con = mysql_connect($db_host , $db_user, $db_pass);
if(!$con){
    die(mysql_error());
}

mysql_select_db('bagsok');

$TOTAL = 200;

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
    echo "entries with keywords/total entries: $count / $TOTAL";

    //STEP2: aggregate
    $kwset_occur_mapping = array();
    foreach($all_splitted_keyword_sets as $splitted_keyword_set){
        //deal with keyword sets of size 1 first
        $current_generation = array();
        $size1_set = expand_dimension($splitted_keyword_set);
        foreach($size1_set as $keyword_set){
            if(!array_key_exists($keyword_set, $kwset_occur_mapping)){
                $occur = occurrence($keyword_set, $all_splitted_keyword_sets);
                $kwset_occur_mapping[$keyword_set] = $occur;
            }
            //to use Jaccard Index, do trick here
            $current_generation[$keyword_set] = array($keyword_set, $keyword_set);
        }

        //now aggregation process begins
        while(count($current_generation) > 0){
            $support_sets = array();
            foreach($current_generation as $keyword_set => $support_sets){
                //if keyword set already in the support set
                //skip it
                if(array_key_exists($keyword_set, $support_sets))
                    continue;

                //in case the occurrence is not calculated
                if(!array_key_exists($keyword_set, $kwset_occur_mapping)){
                    $occur = occurrence($keyword_set, $all_splitted_keyword_sets);
                    $kwset_occur_mapping[$keyword_set] = $occur;
                }

                //calculate the index here, finally!
                $intersection_length = $kwset_occur_mapping[$keyword_set];
                $union_length = $kwset_occur_mapping[$support_sets[0]] + $kwset_occur_mapping[$support_sets[1]] - $intersection_length;
                $jaccard_index = float($intersection_length) / $union_length;

                //check if index is no less than THRESHOLD
                if($jaccard_index >= THRESHOLD){
                    //TODO: add keyword_set, uri to databse
                    $support_sets[] = $keyword_set;
                }
                
                //aggregate to size N+1
                $current_generation = keyword_aggregate($support_sets);
            }
        }
    }
}

mysql_close($con);
?>

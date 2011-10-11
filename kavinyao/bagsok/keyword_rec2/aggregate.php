<?php
$start_time = microtime(true);

$verbose = isset($_GET['verbose']);
$TOTAL = isset($_GET['total']) ? intval($_GET['total']) : 500;
if($TOTAL == 0)
    $TOTAL = 500;

require('dbconfig.php');
require('extract_keywords.php');
require('aggregate_utility.inc.php');

define("THRESHOLD", 0.4);

$con = mysql_connect($db_host , $db_user, $db_pass);
if(!$con){
    die(mysql_error());
}

mysql_select_db('bagsok');

//create table for keyword_set - product mapping
$query1 = "CREATE TABLE IF NOT EXISTS keywordset_product(id bigint(20) NOT NULL AUTO_INCREMENT, keyword_set varchar(255) DEFAULT NULL, product varchar(255) DEFAULT NULL, PRIMARY KEY(id))";
$query2 = "TRUNCATE TABLE keywordset_product";

mysql_query($query1);
mysql_query($query2);

$result = mysql_query("SELECT id, url, keywords FROM pageflow_keywords WHERE keywords IS NOT NULL AND CHAR_LENGTH(keywords) > 0 ORDER BY keywords LIMIT 2000, $TOTAL");

$count = 0;
if(!$result){
    echo 'no result available';
}else{
    //STEP1: pre-process
    //1. get all keyword strings
    //2. get all splited keyword array

    mysql_query("BEGIN");

    $all_splitted_keyword_sets = array();
    $all_rows = array(); //store rows for later iteration
    while($row = mysql_fetch_array($result)){
        $keyword_string = extract_keywords($row['keywords']);
        if($keyword_string){
            $count++;
            $kw_array = keywords_array($keyword_string);
            $row['keywords'] = $kw_array;
            $all_rows[] = $row;
            $all_splitted_keyword_sets[] = $kw_array;
        }
    }
    echo "<p>entries with keywords/total entries: $count / $TOTAL</p>";
    echo "<p>Threshold = ".THRESHOLD."</p>";

    //STEP2: aggregate
    $kwset_occur_mapping = array();
    $previous_keywords = "zsedcftgbhujmkolp"; //store previously processed results
    $previous_sets = array();                 //to avoid verbose re-processing
    foreach($all_rows as $row){
        $splitted_keyword_set = $row['keywords'];
        $current_keywords = kwset_to_string($splitted_keyword_set);
        echo '<h2>'.$current_keywords.'</h2>';

        //see if we have already aggregated for this set of keywrods
        if($previous_keywords == $current_keywords){
            $product = $row['url'];
            foreach($previous_sets as $keywordset){
                $keywordset_string = kwset_to_string($keywordset);
                $query = "INSERT INTO keywordset_product(keyword_set, product) VALUES('$keywordset_string', '$product')";
                mysql_query($query);
            }

            continue;
        }
        $current_sets = array();

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

            $current_sets[] = $keyword_set; //add size 1 keywordset to results
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
            foreach($current_generation as $group){
                $current_sets[] = $group[0]; //add result set to current sets
            }

            $round_count++;
        }
        echo '</table>';

        //insert results to databse
        $product = $row['url'];
        foreach($previous_sets as $keywordset){
            $keywordset_string = kwset_to_string($keywordset);
            $query = "INSERT INTO keywordset_product(keyword_set, product) VALUES('$keywordset_string', '$product')";
            mysql_query($query);
        }
        
        $previous_keywords = $current_keywords;
        $previous_sets = $current_sets;
    }

    mysql_query("COMMIT");
}

$end_time = microtime(true);
echo 'processed: '.($end_time - $start_time).' ms';

mysql_close($con);
?>

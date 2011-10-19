<?php
/**
  * load the stopword list from the $filePath
  * return the array containing the stopwords
  */
function load_stopwords($filePath){
    $file_handle = fopen($filePath, "r");
    $stopwords = array();
    while(!feof($file_handle)){
        $line = fgets($file_handle);
        $line = rtrim($line, "\r\n");
        $stopwords[] = $line;
    }
    fclose($file_handle);
    return $stopwords;
}

$__whitelist = array();
/**
 * fast check if $word exists in $blacklist
 */
function in_blacklist($word, &$blacklist){
    global $__whitelist;
    if(in_array($word, $__whitelist))
        return false;

    foreach($blacklist as $blackword){
        if($blackword == $word)
            return true;
    }

    $__whitelist[] = $word;
    return false;
}

/**
 * remove words that are in $stopwords array
 * from $words
 */
function remove_stopwords($words, &$stopwords){
    $result = array();
    foreach($words as $word){
        if(!in_blacklist($word, $stopwords))
            $result[] = $word;
    }

    return $result;
}

require_once('dbconfig.php');
require_once('aggregate_utility.inc.php');
require('extract_keywords.php');

set_time_limit(300);
$start_time = microtime(true);

// processing starts here
$con = mysql_connect($db_host , $db_user, $db_pass);
if(!$con){
    die(mysql_error());
}

mysql_select_db('bagsok');
mysql_query('BEGIN'); //begin transaction
$create_keyword_table_stmt = "CREATE TABLE IF NOT EXISTS m2_keyword (keyword VARCHAR(32) PRIMARY KEY, occurrence BIGINT(20) DEFAULT 0)";
$create_keyword_product_table_stmt = "CREATE TABLE IF NOT EXISTS m2_keyword_product (id BIGINT(20) PRIMARY KEY AUTO_INCREMENT, keyword VARCHAR(32) NOT NULL, product VARCHAR(255) NOT NULL)";
$success = mysql_query($create_keyword_table_stmt);
if(!$success){
    die("create keyword table failed.");
}
$success = mysql_query($create_keyword_product_table_stmt);
if(!$success){
    die("create keyword-product table failed.");
}
$truncate_stmt = "TRUNCATE TABLE m2_keyword;TRUNCATE TABLE m2_keyword_product;";
mysql_query($truncate_stmt);

$raw_data_query = "SELECT url, keywords FROM pageflow_keywords WHERE keywords IS NOT NULL AND CHAR_LENGTH(keywords) > 0 ORDER BY keywords";
$result = mysql_query($raw_data_query);
$TOTAL = mysql_num_rows($result);

$count = 0;
if(!$result){
    echo 'no result available';
    die();
}else{
    $stopwords = load_stopwords('stopwords.txt');

    $keyword_occur = array();
    $previous_keyword_string = 'thequickbrownfoxjumpsoverthelazydog';
    $previous_keywords = array();
    while($row = mysql_fetch_array($result)){
        $query_string = $row['keywords'];
        $keyword_string = extract_keywords($query_string);
        $product = $row['url'];

        if($keyword_string != $previous_keyword_string){
            $keywords = keywords_array($keyword_string);
            $keywords = remove_stopwords($keywords, $stopwords);

            foreach($keywords as $keyword){
                if(!isset($keyword_occur[$keyword]))
                    $keyword_occur[$keyword] = 0;
            }

            $previous_keyword_string = $keyword_string;
            $previous_keywords = $keywords;
        }

        if(count($previous_keywords) == 0)
            continue; //avoid unnecessary insertion

        $insert_query = 'INSERT INTO m2_keyword_product (keyword, product) VALUES ';
        foreach($previous_keywords as $keyword){
            $keyword_occur[$keyword]++; //key must exist

            $insert_query .= "('$keyword', '$product'),";
        }
        $insert_query = substr($insert_query, 0, -1); //remove trailing comma to avoid SQL syntax error
        //echo $insert_query . '<br />';
        mysql_query($insert_query);
    }

    foreach($keyword_occur as $keyword => $occur){
        if(strlen($keyword) > 32)
            continue;
        $insert_ko = "INSERT INTO m2_keyword VALUE ('$keyword', $occur)";
        mysql_query($insert_ko);
    }
}
mysql_query('COMMIT'); //end transaction
mysql_close($con);

$end_time = microtime(true);
echo 'processed ' . ($end_time = $start_time) . ' ms';
?>

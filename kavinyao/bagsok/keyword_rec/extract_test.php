<?php
require('re_extract.php');
require('dbconfig.php');

$con = mysql_connect($db_host , $db_user, $db_pass);
if(!$con){
    die(mysql_error());
}

mysql_select_db('bagsok');

$result = mysql_query('SELECT id, keywords FROM pageflow_keywords LIMIT 1000');

if(!$result){
    die('no result available');
}else{
    while($row = mysql_fetch_array($result)){
        $search_url = $row[1];
        $keywords = extract_keywords($search_url);
        if($keywords){
            echo "<pre>id:{$row[0]}<br/>";
            print_r($keywords);
            echo '</pre>';
        }
    }
}

mysql_close($con);
?>

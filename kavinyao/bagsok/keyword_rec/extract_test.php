<?php
require('re_extract.php');
require('dbconfig.php');

$con = mysql_connect($db_host , $db_user, $db_pass);
if(!$con){
    die(mysql_error());
}

mysql_select_db('bagsok');

$TOTAL = 2000;

$result = mysql_query("SELECT DISTINCT refer FROM userinfo LIMIT $TOTAL");

$count = 0;
if(!$result){
    die('no result available');
}else{
    echo '<table border="1px">';
    while($row = mysql_fetch_array($result)){
        $search_url = $row[0];
        echo "<tr><td>$row[0]</td><td>";
        $keywords = extract_keywords($search_url);
        if($keywords){
            $count++;
            echo $keywords;
        }
        echo '</td></tr>';
    }
    echo '</table>';
}
echo floatval($count) / $TOTAL;

mysql_close($con);
?>

<?php
require('dbconfig.php');
require('extract_keywords.php');
require('aggregate_utility.inc.php');

$start_time = microtime(true);

$con = mysql_connect($db_host , $db_user, $db_pass);
if(!$con){
    die(mysql_error());
}

mysql_select_db('glassesshop');

$result = mysql_query("SELECT userid, refer FROM user WHERE refer is not null and refer not like '%null%;'");
$TOTAL = mysql_num_rows($result);

$count = 0;
if(!$result){
    echo 'no result available';
}else{
    mysql_query("BEGIN;");
    $kw_occr = array();
?>
<table border="1px">
<thead>
<tr>
<th>ID</th>
<th>Keyword string</th>
</tr>
</thead>
<tbody>
<?
    while($row = mysql_fetch_array($result)){
        $userid = $row['userid'];
        $refer = $row['refer'];
        $real_refer = urldecode($refer);
        $keyword_string = extract_keywords($real_refer);
        if($keyword_string){
            $count++;
            $kw_set = keywords_array($keyword_string);
            foreach($kw_set as $kw) {
                if(!array_key_exists($kw, $kw_occr)) {
                    $kw_occr[$kw] = 0;
                }
                $kw_occr[$kw] += 1;
            }
            echo "<tr><td>$userid</td><td>$keyword_string</td></tr>";
        }
    }
?>
</tbody>
</table>
<?
    mysql_query("COMMIT");
    echo "query ratio: $count / $TOTAL<br/>";

    arsort($kw_occr);
?>
<table border="1px">
<thead>
<tr>
<th>keyword</th>
<th>occurrence</th>
</tr>
</thead>
<tbody>
<?
    foreach($kw_occr as $kw => $occr) {
        echo "<tr><td>$kw</td><td>$occr</td></tr>";
    }
}
?>
</tbody>
</table>
<?
$end_time = microtime(true);
echo 'processed: '.($end_time - $start_time).' ms';

mysql_close($con);
?>

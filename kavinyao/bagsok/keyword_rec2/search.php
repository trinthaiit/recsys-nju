<form action="search.php" method="GET">
<input type="text" size="50" name="query" /><br/>
<input type="submit" value="Search" /><br/>
<?php
if(!isset($_GET['query']))
    die();
require_once('dbconfig.php');
require_once('aggregate_utility.inc.php');
require_once('search.inc.php');

$query = $_GET['query'];
echo '<h1>query: '.$query.'</h1>';

$keywords = explode(' ', $query);
remove_empty2($keywords);

//global connection, so chilling > <
$con = mysql_connect($db_host , $db_user, $db_pass);
if(!$con){
    die(mysql_error());
}

mysql_select_db('bagsok');

//search part
$searcher = new DirectKeywordSearcher();
$product_weight = $searcher->getResult($keywords);

//draw table
echo '<table border="1px"><tr><th>Product</th><th>Weight</th></tr>';
foreach($product_weight as $pair){
    $product = $pair[0];
    $weight = $pair[1];
    echo "<tr><td><a href=\"$product\">$product</a></td><td>$weight</td></tr>";
}
echo '</table>';
mysql_close($con);
?>

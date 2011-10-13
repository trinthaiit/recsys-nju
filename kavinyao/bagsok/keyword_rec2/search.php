<form action="search.php" method="GET">
<input type="text" size="50" name="query" /><br/>
<input type="submit" value="Search" /><br/>
<?php
if(!isset($_GET['query']))
    die();
echo '<h1>query: '.$_GET['query'].'</h1>';

//============functions===============
function get_product_list_via($keyword){
    $query = "SELECT id, keyword_set, product FROM keywordset_product WHERE keyword_set LIKE '%$keyword%'";
    $result = mysql_query($query);
    $products = array();
    while($row = mysql_fetch_array($result)){
        $temp = array();
        $temp['id'] = $row['id'];
        $temp['keyword'] = $row['keyword_set'];
        $temp['product'] = $row['product'];
        $products[] = $temp;
    }

    return $products;
}

function remove_duplicate(){
    $id_cache = array();
    return function($item) use (&$id_cache){
        $is_in = in_array($item['id'], $id_cache);
        if(!$is_in)
            $id_cache[] = $item['id'];
        return !$is_in;
    };
}

//use keywords size as weight
function simple_weight($keyword_string){
    $keyword_array = explode(' ', $keyword_string);
    remove_empty2($keyword_array);
    $weight = count($keyword_array);
    return $weight;
}
//====================================
require_once('dbconfig.php');
require_once('aggregate_utility.inc.php');

$con = mysql_connect($db_host , $db_user, $db_pass);
if(!$con){
    die(mysql_error());
}

mysql_select_db('bagsok');
$query = $_GET['query'];
$keywords = explode(' ', $query);
remove_empty2($keywords);

$all_result = array();
foreach($keywords as $keyword){
    $result = get_product_list_via($keyword);
    $all_result = array_merge($result, $all_result);
}
$dup_remover = remove_duplicate();
$filtered_result = array_filter($all_result, remove_duplicate());

//combine weight
$product_weight_raw = array();
foreach($filtered_result as $item){
    $weight = simple_weight($item['keyword']);

    if(array_key_exists($item['product'], $product_weight_raw))
        $product_weight_raw[$item['product']] += $weight;
    else
        $product_weight_raw[$item['product']] = $weight;
}

//reorganize for sort
$product_weight = array();
foreach($product_weight_raw as $product => $weight)
    $product_weight[] = array($product, $weight);

//sort by weight descendingly
usort($product_weight, function($item1, $item2){
    return $item2[1] - $item1[1];
});

//draw table
echo '<table border="1px"><tr><th>Product</th><th>Weight</th></tr>';
foreach($product_weight as $pair){
    $product = $pair[0];
    $weight = $pair[1];
    echo "<tr><td><a href=\"$product\">$product</a></td><td>$weight</td></tr>";
}
echo '</table>';
?>

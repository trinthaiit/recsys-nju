<?php
define('LMT', 1000);
$query = <<<EOD
SELECT b.uri, b.keyword, p.meta_keywords
FROM browse b, products p
WHERE b.keyword IS NOT NULL AND b.uri = p.uri_name
LIMIT 1000;
EOD;

$con = mysql_connect("localhost", "root", "kavinyao");
if(!$con){
    die("shit" . mysql_error());
}

mysql_select_db("bagsok");

$result = mysql_query($query);
?>

<html>
    <body>
<?php
if($result){
    echo '<table style="font-size: .8em;" border="1px">';

    define('THRESHOLD', 0.5);
    $good_count = 0;
    
    while($row = mysql_fetch_array($result)){
        $keywords = strtolower($row['keyword']);
        $meta_keywords = strtolower($row['meta_keywords']);

        $kwarr = explode(' ', $keywords);
        $count = 0;
        foreach($kwarr as $keyword){
            if(!$keyword || $keyword == 'bag' || $keyword == 'bags')
                continue;

            if(false !== strpos($meta_keywords, $keyword))
                $count++;
        }
        $percentage = floatval($count) / count($kwarr);
        if($percentage >= THRESHOLD)
            $good_count++;

        echo "<tr><td>$keywords</td><td>$meta_keywords</td><td>$percentage</td></tr>";
    }
    echo '</table>';
    $good_percentage = floatval($good_count) / LMT;
    echo "Percentage no lower than " . THRESHOLD . ": $good_percentage";
}else{
    echo 'damn';
}
?>
        <form action="process.php" method="GET">
            keyword: <input type="text" name="keyword" />
            <input type="submit" value="search" />
        </form>
    </body>
</html>
<?php
mysql_close($con);
?>

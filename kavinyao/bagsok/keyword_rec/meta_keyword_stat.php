<?php
define('LMT', 1000);
$query = <<<EOD
SELECT b.uri, b.keyword, p.meta_keywords
FROM browse b, products p
WHERE b.keyword IS NOT NULL AND b.uri = p.uri_name
LIMIT 1000;
EOD;

$con = mysql_connect("localhost", "root", "xyyy");
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

    $counts = array(0, 0, 0, 0, 0);
    
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
        if($percentage >= 0.6)
            $counts[4]++;
        if($percentage >= 0.5)
            $counts[3]++;
        if($percentage >= 0.4)
            $counts[2]++;
        if($percentage >= 0.3)
            $counts[1]++;
        if($percentage >= 0.2)
            $counts[0]++;

        echo "<tr><td>$keywords</td><td>$meta_keywords</td><td>$percentage</td></tr>";
    }
    echo '</table>';
    $good_percentage = array(0, 0, 0, 0, 0);
    $good_percentage[0] = floatval($counts[0]) / LMT;
    $good_percentage[1] = floatval($counts[1]) / LMT;
    $good_percentage[2] = floatval($counts[2]) / LMT;
    $good_percentage[3] = floatval($counts[3]) / LMT;
    $good_percentage[4] = floatval($counts[4]) / LMT;
    echo "Percentage no lower than 0.2: $good_percentage[0]<br/>";
    echo "Percentage no lower than 0.3: $good_percentage[1]<br/>";
    echo "Percentage no lower than 0.4: $good_percentage[2]<br/>";
    echo "Percentage no lower than 0.5: $good_percentage[3]<br/>";
    echo "Percentage no lower than 0.6: $good_percentage[4]<br/>";
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

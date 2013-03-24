<?php
	include_once "../database/glass-database-manager.php";
    $dm = GlassDatabaseManager::getInstance();
    $result = $dm->query('Select COUNT(*) from query_train;');
    $row = mysql_fetch_array($result);
    echo $row[0];
    echo 1/2;
    echo 'aaaa' . 12.2;
?>

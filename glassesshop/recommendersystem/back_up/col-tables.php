<?php
// This code assumes $itemID is set to that of 
// the item that was just rated. 
// Get all of the user's rating pairs

ini_set("max_execution_time",0);
$connection = mysql_connect($host = "localhost", $username = "root", $password = "");
mysql_select_db("glassesshop");
mysql_query("delete from dev");
$pair_result = mysql_query("select userID, itemID from rating");
$count = 1;
$count_sum = mysql_num_rows($pair_result);

while($pair_row = mysql_fetch_array($pair_result)){
	preprocess($pair_row["userID"], $pair_row["itemID"]);
	echo $count*100/$count_sum."%<br />";
	$count++;
	ob_flush();
	flush();
}

function preprocess($userID, $itemID){
	$sql = "SELECT DISTINCT r.itemID, r2.ratingValue - r.ratingValue as rating_difference
	            FROM rating r, rating r2
	            WHERE r.userID='".$userID."' AND 
	                    r2.itemID='".$itemID."' AND 
	                    r2.userID='".$userID."'";	
	$db_result = mysql_query($sql);
	$num_rows = mysql_num_rows($db_result);
	//For every one of the user's rating pairs, 
	//update the dev table
	while ($row = mysql_fetch_assoc($db_result)) {
	    $other_itemID = $row["itemID"];
	    $rating_difference = $row["rating_difference"];
	    //if the pair ($itemID, $other_itemID) is already in the dev table
	    //then we want to update 2 rows.
	  
	    if (mysql_num_rows(mysql_query("SELECT itemID1 FROM dev WHERE itemID1='".$itemID."' AND itemID2='".$other_itemID."'")) > 0)  {
	    	//echo "update<br />";
	    	$sql = "UPDATE dev SET count=count+1,sum=sum+".$rating_difference." WHERE itemID1='".$itemID."' AND itemID2='".$other_itemID."'";
	    	mysql_query($sql);
			//We only want to update if the items are different                
	        if ($itemID != $other_itemID) {
	            $sql = "UPDATE dev SET count=count+1,sum=sum-".$rating_difference." WHERE (itemID1='".$other_itemID."' AND itemID2='".$itemID."')";
	            mysql_query($sql);
	        }
	    }
	    else { //we want to insert 2 rows into the dev table
	    	//echo "create<br />";
	        $sql = "INSERT INTO dev VALUES ('".$itemID."','".$other_itemID."', 1, ".$rating_difference.")";
	        mysql_query($sql); 
		//We only want to insert if the items are different       
	        if ($itemID != $other_itemID) {         
	            $sql = "INSERT INTO dev VALUES ('".$other_itemID."', 
		    '".$itemID."', 1, ".-$rating_difference.")";
	            mysql_query($sql);
	        }
	    } 

	}
}


function predict($userID, $itemID) {
    global $connection;    
    $denom = 0.0; //denominator
    $numer = 0.0; //numerator    
    $k = $itemID;    
    $sql = "SELECT r.itemID, r.ratingValue FROM rating r WHERE r.userID='".$userID."' AND r.itemID <> '".$itemID."'";
    $db_result = mysql_query($sql);        
    //for all items the user has rated
    while ($row = mysql_fetch_assoc($db_result))  {
        $j = $row["itemID"];
        $ratingValue = $row["ratingValue"];        
        //get the number of times k and j have both been rated by the same user
        $sql2 = "SELECT d.count, d.sum FROM dev d WHERE itemID1=$k AND itemID2=$j";
        $count_result = mysql_query($sql2, $connection);        
        //skip the calculation if it isn't found
        if(mysql_num_rows($count_result) > 0)  {
            $count = mysql_result($count_result, 0, "count");
            $sum = mysql_result($count_result, 0, "sum");            
            //calculate the average
            $average = $sum / $count;            
            //increment denominator by count
            $denom += $count;            
            //increment the numerator
            $numer += $count * ($average + $ratingValue);
        }        
    }    
    if ($denom == 0)
        return 0;
    else
        return ($numer / $denom);
}


function predict_all($userID ) {
    $sql2 = "SELECT d.itemID1 as 'item', sum(d.count) as 'denom', 
    sum(d.sum + d.count*r.ratingValue) as 'numer' FROM item i, rating r,
    dev d WHERE r.userID=$userID 
    AND d.itemID1<>r.itemID 
    AND d.itemID2=r.itemID GROUP BY d.itemID1";
    return mysql_query($sql2, $connection);
}
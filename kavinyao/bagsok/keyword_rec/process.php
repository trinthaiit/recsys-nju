<html>
    <body>
        <?php
        $keyword = $_GET['keyword'];
        $keywords = explode(' ', $keyword);

        $con = mysql_connect('localhost', 'root', 'kavinyao');
        if(!$con){
            echo mysql_error();
            die('shit');
        }

        foreach($keywords as $key){
            $query = sprintf('SELECT * FROM opococ2mod.featureoptions WHERE name REGEXP \'(^|[a-zA-Z ]*[^a-zA-Z])%s[^a-zA-Z]*[a-zA-Z ]*\'', mysql_real_escape_string($key));
            //echo $query;
            $result = mysql_query($query, $con);
            if(!$result)
                continue;

            $rows = mysql_num_rows($result);
            if($rows){
                echo $key . ' exists in databse [rows number: ' . $rows . ']<br />';
                while($row = mysql_fetch_array($result)){
                    echo sprintf('feature id is %s, feature option is is %s, feature_name is [%s]<br />', $row['feature_id'], $row['id'], $row['name']);

                    $product_query = sprintf('SELECT product_id FROM opococ2mod.product_featureoption_relations WHERE featureoption_id = %s', $row['id']);
                    $result2 = mysql_query($product_query, $con);
                    if($result2 && mysql_num_rows($result2)){
                        echo '&nbsp;&nbsp;&nbsp;&nbsp;product ids: ';
                        while($row2 = mysql_fetch_array($result2)){
                            echo $row2['product_id'] . ', ';
                        }
                        echo '<br />';
                    }
                }
                echo '<br />';
            }else{
                echo $key . ' does not exist in databse <br />';
            }
        }

        mysql_close($con);
        ?>
    </body>
</html>

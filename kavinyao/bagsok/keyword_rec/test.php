<html>
    <body>
        <?php
        $con = mysql_connect('localhost', 'root', 'kavinyao');
        if(!$con){
            echo 'shit' . mysql_error();
            die();
        }
        
        mysql_select_db('bagsok', $con);
        $views = mysql_query('SELECT COUNT(*) FROM pageflow WHERE url LIKE \'http://www.bagsok.com/product/%\' GROUP BY cookie_id');
        $view_count = array();
        while($row = mysql_fetch_row($views)){
            $index = $row[0];
            if(isset($view_count[$index])){
                $view_count[$index]++;
            }else{
                $view_count[$index] = 1;
            }
        }

        ksort($view_count);
        echo '<table border="1">';
        foreach($view_count as $view => $count)
            echo '<tr><td>' . $view . '</td><td>' . $count . '</td></tr>';
        echo '</table>';
        mysql_close($con);
        ?>
    </body>
</html>

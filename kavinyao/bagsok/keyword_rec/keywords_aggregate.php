<?php
/**
 * Utilities for keyword aggregation.
 * @author: kavinyao@gmail.com
 */
function remove_empty($str_arr){
    $result = array();
    foreach($str_arr as $word){
        if($word)
            $result[] = $word;
    }

    return $result;
}

function remove_empty2(&$str_arr){
    foreach($str_arr as $index => $word){
        if(!$word)
            unset($str_arr[$index]);
    }
}

function keywords_array($kw_str){
    $kw_arr = explode(" ", $kw_str);
    remove_empty2($kw_arr);
    //sort($kw_arr);

    return $kw_arr;
}

//Check if all elements in $arr_b are in $arr_a
function array_contains($arr_a, $arr_b){
    foreach($arr_b as $elem){
        if(!in_array($elem, $arr_a))
            return false;
    }

    return true;
}

function occurrence($keywords_set, $all_keywords_arr){
    $count = 0;
    foreach($all_keywords_arr as $keywords_arr){
        if(array_contains($keywords_arr, $keywords_set))
            $count++;
    }

    return $count;
}

//expand array one more dimension
function expand_dimension($dim1arr){
    $expanded = array();
    foreach($dim1arr as $elem)
        $expanded[] = array($elem);

    return $expanded;
}

function generate_next($keywords_arr, $curr_arr){
    $next = array();
    foreach($curr_arr as $arr){
        $diff = array_diff($keywords_arr, $arr);
        foreach($diff as $diff_elem){
            $temp = array_merge($arr, array($diff_elem));
            sort($temp);
            $next[] = $temp;
        }
    }

    return array_unique($next, SORT_REGULAR);
}
?>

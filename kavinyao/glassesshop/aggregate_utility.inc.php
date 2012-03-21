<?php
/**
 * Utilities for keyword aggregation.
 * @author: kavinyao@gmail.com
 */
/**
 * recursive version of array_unique
 * treats nested array correctly
 */
function super_unique($array)
{
  $result = array_map("unserialize", array_unique(array_map("serialize", $array)));

  foreach ($result as $key => $value)
  {
    if ( is_array($value) )
    {
      $result[$key] = super_unique($value);
    }
  }

  return $result;
}

/**
 * OMG, php only supports integer or string as array key
 * convert keyword set to string
 */
function kwset_to_string($keyword_set){
    return implode(" ", $keyword_set);
}

/**
 * remove empty string from $str_arr
 */
function remove_empty($str_arr){
    $result = array();
    foreach($str_arr as $word){
        if($word)
            $result[] = $word;
    }

    return $result;
}

/**
 * remove empty string from $str_arr
 * using reference
 */
function remove_empty2(&$str_arr){
    foreach($str_arr as $index => $word){
        if(!$word)
            unset($str_arr[$index]);
    }
}

/**
 * split $keyword_string to keywords with delimiter given
 */
function keywords_array($keyword_string, $delimiter=" "){
    $keyword_array = explode($delimiter, $keyword_string);
    remove_empty2($keyword_array);

    return $keyword_array;
}

/**
 * Check if every element in $array_b is in $array_a
 */
function array_contains($array_a, $array_b){
    foreach($array_b as $elem){
        if(!in_array($elem, $array_a))
            return false;
    }

    return true;
}

/**
 * check times of occurrence of $target in $universal_set
 * $target: array of string
 * $universal_set: array of array of string
 */
function occurrence($target, $universal_set){
    $count = 0;
    foreach($universal_set as $keywords_arr){
        if(array_contains($keywords_arr, $target))
            $count++;
    }

    return $count;
}

/**
 * expand 1-dimensional array to 2-dimensional array.
 * $dim1_array: array of dimension 1
 */
function expand_dimension($dim1_array){
    $expanded = array();
    foreach($dim1_array as $elem)
        $expanded[] = array($elem);

    return $expanded;
}

/**
 * generate keyword set of size N+1
 * from $size_n_sets and the $keyword_pool
 * $keyword_pool: pool of candidate keywords
 * $size_n_sets: keyword arrays of size N
 */
function generate_next($keyword_pool, $size_n_sets){
    $next = array();
    foreach($size_n_sets as $size_n_set){
        $difference_set = array_diff($keyword_pool, $size_n_set);
        foreach($difference_set as $diff_elem){
            $temp = array_merge($size_n_set, array($diff_elem));
            sort($temp);
            $next[] = $temp;
        }
    }

    return array_unique($next, SORT_REGULAR);
}

/**
 * aggregate keyword sets of size n to sets of size N+1
 * returns array(
 *          array(set_size_n+1, array(kw_set1, kw_set2)),
 *          ....
 * )
 */
function keyword_aggregate($keyword_sets){
    $result = array();
    $length = count($keyword_sets);
    for($i = 0;$i < $length;$i++){
        for($j = $i+1;$j < $length;$j++){
            //echo 'merging ['.kwset_to_string($keyword_sets[$i]).'] and ['.kwset_to_string($keyword_sets[$j]).']<br />';
            $merged = array_unique(array_merge($keyword_sets[$i], $keyword_sets[$j]));
            //my math's bad...
            if((count($merged)*2-2) == (count($keyword_sets[$i])+count($keyword_sets[$j]))){
                sort($merged);
                $result[] = array($merged, array($keyword_sets[$i], $keyword_sets[$j]));
            }
        }
    }

    return $result;
}
?>

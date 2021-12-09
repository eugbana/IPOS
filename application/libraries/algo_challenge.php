<?php
class Algo_challenge{
    static function findWindowMax(array $arr,$window){
        $maxis = [];
        if(count($arr) < $window){
            return [max($arr)];
        }
        for($i=0;$i< count($arr);$i++){
            if(($i+$window)>=count($arr)){
                $maxis[] = max(array_slice($arr,$i));
                break;
            }
            $maxis[] = max(array_slice($arr,$i,$window,true));
        }
        return $maxis;
    }
    static function return_prime($num){
        if($num == 2){
            return 2;
        }
        $divisors = [2,3];
        $results = [];
        foreach ($divisors as $div){
            $results[] = $num % $div;
        }
        if(count($results) == count($divisors)){
            return  $num;
        }
        $num += 1;
        return self::return_prime($num);
    }
    static function writeOneToHundredWithoutNumbers(){
        $target  = strlen("abcdefghij");
        $target *=$target;
        for ($i=strlen("a");$i <=$target;$i++){
            echo $i."\n";
        }
    }
    static function get_sum_pairs(array $arr,$sum){
        $start_time = microtime(true);
        $arr_length = count($arr);
        $pairs = [];
        if($arr_length <= 0){
            return $pairs;
        }
        $cur_index = 0;
        foreach ($arr as $digit){
            if($cur_index == $arr_length){
                break;
            }
            for($i = $cur_index+1;$i<$arr_length;$i++){
                if(($digit + $arr[$i]) == $sum){
                    $pairs[] = [$digit,$arr[$i]];
                }
            }
            $cur_index++;
        }
        $end_time =  microtime(true);
        echo "finished in ".($end_time-$start_time)." seconds";
        return $pairs;
    }
    static function get_sum_pairs_optimised(array $arr,$sum){
        $start_time =  microtime(true);
        $arr_length = count($arr);
        $pairs = [];
        if($arr_length <= 1){
            return $pairs;
        }
        if($arr_length == 2){
            if(($arr[0]+ $arr[1]) == $sum){
                return  [[$sum[0],$arr[1]]];
            }
            return $pairs;
        }
        $mid_point = intdiv($arr_length,2); //integer division

        for ($i = 0;$i < $mid_point;$i++){ // n/2 iteration
            $inner_counter = $arr_length;
            $temp = 0;
            for($j = $i+1; $j<$arr_length; $j++){
                $inner = $inner_counter-1;
//            echo 'i: '.$i.' j:'.$j."<br>";
                if(($arr[$i] + $arr[$j]) == $sum){
                    $pairs[] = [$arr[$i],$arr[$j]];
                }
                if(($inner_counter-2) > $j){
//                echo 'i: '.$i." j: $inner<br>";
                    if(($arr[$i] + $arr[$inner_counter-1]) == $sum){
                        $pairs[] = [$arr[$i],$arr[$inner_counter-1]];
                    }
                }else{
                    if($inner > $j){
//                    echo 'i: '.$i.' j:'.$inner."<br>";
                        if(($arr[$i] + $arr[$inner]) == $sum){
                            $pairs[] = [$arr[$i],$arr[$inner]];
                        }
                    }
                    break;
                }
                if(($i+$mid_point+1+$temp)< ($inner_counter)){
//                echo 'u_i: '.$upper_i.' u_j:'.$upper_j."<br>";
                    if(($arr[$i+$mid_point] + $arr[$i+$mid_point+1+$temp]) == $sum){
                        $pairs[] = [$arr[$i+$mid_point] , $arr[$i+$mid_point+1+$temp]];
                    }
                }
                if(($inner_counter-1)>($i+$mid_point+1+$temp)){
//                echo 'u_i: '.$upper_i." u_j: $inner<br>";
                    if(($arr[$i+$mid_point] + $arr[$inner_counter-1]) == $sum){
                        $pairs[] = [$arr[$i+$mid_point] , $arr[$inner_counter-1]];
                    }
                }
                $inner_counter--;
                $temp++;
            }
        }
        $end_time =  microtime(true);
        echo "<br> finished in ".($end_time-$start_time)." seconds <br>";
        return $pairs;
    }

}
//$arr = [1,2,3,4,5,6,7,8,9];
//$sum = 10;
//
//print_r(Algo_challenge::get_sum_pairs($arr, $sum));
//
//print_r(Algo_challenge::get_sum_pairs_optimised($arr,$sum));

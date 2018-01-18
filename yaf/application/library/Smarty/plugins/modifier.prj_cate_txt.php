<?php

function smarty_modifier_prj_cate_txt($string)
{
    if(!$string){
        return '';
    }
    $str=$string.'';
    $returnVal = '';
    switch ($str) {
        case '1':
            $returnVal="小微商贷";
            break;
        case '2':
            $returnVal='浮盈收益';
            break;
        case '4':
            $returnVal='企业融资';
            break;
        case '5':
            $returnVal='供应链融';
            break;
        case '6':
            $returnVal='房产易贷';
            break;
        case '7':
            $returnVal='车辆抵押';
            break;
        default:
            $returnVal='';
            break;
    }
    return $returnVal;
} 
?>
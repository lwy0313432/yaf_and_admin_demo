<?php

function smarty_modifier_prj_cate_sm_title($string)
{
    if(!$string){
        return '';
    }
    $str=$string.'';
    $returnVal = '';
    switch ($str) {
        case '3':
            $returnVal="为该直投项目提供担保";
            break;
        case '4':
            $returnVal='为该债权提供担保';
            break;
        case '6':
            $returnVal='为该项目提供风险保障金';
            break;
        case '7':
            $returnVal='为该项目提供担保';
            break;
        case '5':
            $returnVal='为该债权提供担保';
            break;
        case '8':
            $returnVal='为该债权提供担保';
            break;
        case '9':
            $returnVal='为该债权提供担保';
            break;
        case '2':
            $returnVal='为该直投项目提供担保';
            break;
        case '1':
            $returnVal='为该债权提供担保';
            break;
        case '10':
            $returnVal='为该直投项目提供担保';
            break;
        case '11':
            $returnVal='为该债权提供担保';
            break;
        default:
            $returnVal='';
            break;
    }
    return $returnVal;
} 

?>
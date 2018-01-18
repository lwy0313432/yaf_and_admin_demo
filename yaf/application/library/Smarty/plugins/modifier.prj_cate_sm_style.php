<?php

function smarty_modifier_prj_cate_sm_style($string)
{
    if(!$string){
        return '';
    }
    $str=$string.'';
    $returnVal = '';
    switch ($str) {
        case '1':
            $returnVal="i-prj-cate-1";
            break;
        case '2':
            $returnVal='i-prj-cate-2';
            break;
        case '3':
            $returnVal='i-prj-cate-3';
            break;
        case '4':
            $returnVal='i-prj-cate-4';
            break;
        case '5':
            $returnVal='i-prj-cate-5';
            break;
        case '6':
            $returnVal='i-prj-cate-6';
            break;
        case '7':
            $returnVal='i-prj-cate-7';
            break;
        case '8':
            $returnVal='i-prj-cate-8';
            break;
        case '9':
            $returnVal='i-prj-cate-9';
            break;
        case '10':
            $returnVal='i-prj-cate-10';
            break;
        case '11':
            $returnVal='i-prj-cate-10';
            break;
        default:
            $returnVal='';
            break;
    }
    return $returnVal;
} 

?>
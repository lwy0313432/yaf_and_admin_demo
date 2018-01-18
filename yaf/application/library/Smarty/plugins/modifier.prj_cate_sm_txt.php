<?php

function smarty_modifier_prj_cate_sm_txt($string)
{
    if(!$string){
        return '';
    }
    $str=$string.'';
    $returnVal = '';
    switch ($str) {
        case '3':
            $returnVal="直";
            break;
        case '4':
            $returnVal='债';
            break;
        case '6':
            $returnVal='央';
            break;
        case '7':
            $returnVal='链';
            break;
        case '5':
            $returnVal='保';
            break;
        case '8':
            $returnVal='房';
            break;
        case '9':
            $returnVal='车';
            break;
        case '2':
            $returnVal='微';
            break;
        case '1':
            $returnVal='新';
            break;
        case '10':
            $returnVal='优';
            break;
        case '11':
            $returnVal='短';
            break;
        default:
            $returnVal='';
            break;
    }
    return $returnVal;
} 

?>
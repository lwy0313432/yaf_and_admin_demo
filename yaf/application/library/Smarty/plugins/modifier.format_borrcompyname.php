<?php
function smarty_modifier_format_borrcompyname($compyname) {
	$mb_len = mb_strlen($compyname);
	if (!$compyname) {
		return '';
	}
	return mb_substr($compyname, 0, 3 ,"utf-8").'****'.mb_substr($compyname, $mb_len-2, 2 ,"utf-8");
} 
?>
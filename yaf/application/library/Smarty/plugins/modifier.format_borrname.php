<?php
function smarty_modifier_format_borrname($username) {
	if (!$username) {
		return '';
	}
	return mb_strimwidth($username, 0, 2).'**';
} 
?>
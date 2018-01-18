<?php

function smarty_modifier_format_userid($card) {
	if (!$card) {
		return '';
	}
	$length=strlen($card);
	return substr($card, 0, 1).'*** **** **** **'.substr($card, -2);
}
?>
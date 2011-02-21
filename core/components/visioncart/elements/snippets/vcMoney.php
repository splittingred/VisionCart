<?php
/**
 * @package visioncart
 */

$vc =& $modx->visioncart;

if ($scriptProperties['options'] != '') {
	$scriptProperties['options'] = explode(',', $scriptProperties['options']);
	foreach($scriptProperties['options'] as $key => $value) {
		$value = explode('==', $value);
		$scriptProperties[$value[0]] = $value[1];
	}
}

return $vc->money($scriptProperties['input'], $scriptProperties);
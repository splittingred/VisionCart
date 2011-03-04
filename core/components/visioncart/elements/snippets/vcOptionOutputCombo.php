<?php
/**
 * @package visioncart
 */

$option = $modx->getOption('option', $scriptProperties, array());
$values = $modx->getOption('values', $scriptProperties, array());
$tpl = $modx->getOption('tpl', $scriptProperties, 'vcOptionOutputCombo');
$rowTpl = $modx->getOption('rowTpl', $scriptProperties, 'vcOptionOutputComboRow');
$selectedValue = $modx->getOption('selectedValue', $scriptProperties, '0');

$innerChunk = '';
foreach($values as $value) {
	if ($value['id'] == $selectedValue) {
		$value['selected'] = 'selected="selected"';	
	} else {
		$value['selected'] = '';	
	}
	$innerChunk .= $modx->getChunk($rowTpl, $value);
}

return $modx->getChunk($tpl, array_merge($option, array('innerChunk' => $innerChunk)));
<?php

if (!isset($modx->visioncart) || $modx->visioncart == null) {
	$modx->addPackage('visioncart', $modx->getOption('core_path').'components/visioncart/model/');
	$modx->visioncart = $modx->getService('visioncart', 'VisionCart', $modx->getOption('core_path').'components/visioncart/model/visioncart/', array(
		'method' => 'processor',
		'initialize' => 'processor',
		'context' => (string) $modx->context->get('key'),
		'event' => (string) $modx->event->name
	));
}

$vc =& $modx->visioncart;

$selectedOptions = json_decode($scriptProperties['selectedOptions'], true);

// Prepare array for snippet
$selectedArray = array();
foreach($selectedOptions as $option) {
	if (trim($option['value']) != '') {
		$selectedArray[$option['optionId']] = $option['value'];	
	}
}

// Get options
$returnValue = $modx->runSnippet('vcProductOptions', array(
	'id' => $modx->visioncart->router['product']['id'],
	'selectedValues' => $selectedArray,
	'return' => 'json'
));

return $returnValue;
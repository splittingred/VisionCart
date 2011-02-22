<?php

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
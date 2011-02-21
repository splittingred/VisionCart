<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

$query = $modx->getOption('query', $scriptProperties, '');

$options = $modx->getCollection('vcOption', array(
	'shopid:=' => (int) $_REQUEST['shopId'],
	'name:LIKE' => '%'.$query.'%'
));
        
$list = array();
foreach ($options as $option) {
	// Check if option is already assigned to product
	$value = $modx->getObject('vcProductOption', array(
		'productid' => (int) $_REQUEST['prodId'],
		'optionid' => $option->get('id')
	));
	
	if ($value != null) {
		continue;	
	}
	
	$optionArray = $option->toArray();
	
    $list[] = $optionArray;
}

return $this->outputArray($list, sizeof($list));
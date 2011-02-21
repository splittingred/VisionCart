<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

if (isset($_REQUEST['type'])) {
	switch($_REQUEST['type']) {
		case 'input':
			$type = 'Input';
			break;
		case 'output':
			$type = 'Output';
			break;
		default:
			$type = 'Output';
			break;	
	}	
}

$query = $modx->getOption('query', $scriptProperties, '');

if ($query == '') {
	$options = $modx->getCollection('modSnippet', array('name:LIKE' => 'vcOption'.$type.'%'));
} else {
	if (strtolower(substr($query, 0, 8)) != 'vcoption') {
		$query = 'vcOption'.$query;
	}
	$options = $modx->getCollection('modSnippet', array('name:LIKE' => $query.'%'));
}
        
$list = array();
foreach ($options as $option) {
	$optionArray = $option->toArray();
    $list[] = $optionArray;
}

return $this->outputArray($list, sizeof($list));
<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

$optionArray = (array) json_decode($_REQUEST['formData']);

if ($optionArray['inputsnippet'] != '') {
	// Get the snippet
	$snippet = $modx->getObject('modSnippet', (int) $optionArray['inputsnippet']);
	if ($snippet != null) {
		$optionArray['inputsnippet'] = $snippet->get('name');
	} else {
		$optionArray['inputsnippet'] = '';
	}	
}

if ($optionArray['outputsnippet'] != '') {
	// Get the snippet
	$snippet = $modx->getObject('modSnippet', (int) $optionArray['outputsnippet']);
	if ($snippet != null) {
		$optionArray['outputsnippet'] = $snippet->get('name');
	} else {
		$optionArray['outputsnippet'] = '';
	}	
}

if (isset($optionArray['id']) && !empty($optionArray['id']) && $optionArray['id'] != 0) {
	$option = $modx->getObject('vcOption', $optionArray['id']);
	$option->fromArray(array(
		'name' => $optionArray['name'],
		'shopid' => (int) $_REQUEST['shopId'],
		'inputsnippet' => $optionArray['inputsnippet'],
		'outputsnippet' => $optionArray['outputsnippet']
	));
} else {
	$option = $modx->newObject('vcOption', array(
		'name' => $optionArray['name'],
		'shopid' => (int) $_REQUEST['shopId'],
		'inputsnippet' => $optionArray['inputsnippet'],
		'outputsnippet' => $optionArray['outputsnippet']
	));
}

// Return values
if ($option->save()) {
	return $modx->error->success('', $option);
} else {
	return $modx->error->failure('');
}
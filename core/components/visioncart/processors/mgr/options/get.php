<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

$optionId = (int) $_REQUEST['id'];

$option = $modx->getObject('vcOption', $optionId);

if ($option == null) {
	return $modx->error->failure('Option not found');
}

$optionArray = $option->toArray();

if ($optionArray['inputsnippet'] != '') {
	// Get the snippet
	$snippet = $modx->getObject('modSnippet', array(
		'name' => $optionArray['inputsnippet']
	));
	if ($snippet != null) {
		$optionArray['inputsnippet'] = $snippet->get('id');
	} else {
		$optionArray['inputsnippet'] = '';
	}	
}

if ($optionArray['outputsnippet'] != '') {
	// Get the snippet
	$snippet = $modx->getObject('modSnippet', array(
		'name' => $optionArray['outputsnippet']
	));
	if ($snippet != null) {
		$optionArray['outputsnippet'] = $snippet->get('id');
	} else {
		$optionArray['outputsnippet'] = '';
	}	
}

return $modx->error->success('', $optionArray);
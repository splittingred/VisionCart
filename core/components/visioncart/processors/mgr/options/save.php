<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

$optionArray = (array) json_decode($_REQUEST['formData']);

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
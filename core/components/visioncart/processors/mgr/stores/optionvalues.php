<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

$query = $modx->getOption('query', $scriptProperties, '');

$optionId = (int) $_REQUEST['optionId'];

$options = $modx->getCollection('vcOptionValue', array(
	'optionid' => $optionId,
	'value:LIKE' => '%'.$query.'%'
));

$list = array();
foreach ($options as $option) {
	$optionArray = $option->toArray();
    $list[] = $optionArray;
}

return $this->outputArray($list, sizeof($list));
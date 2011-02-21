<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

$valueId = (int) $_REQUEST['id'];

$value = $modx->getObject('vcOptionValue', $valueId);

if ($value == null) {
	return $modx->error->failure('Value not found');
}

$value->remove();

return $modx->error->success('');
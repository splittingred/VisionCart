<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

$optionId = (int) $_REQUEST['id'];

$option = $modx->getObject('vcOption', $optionId);

if ($option == null) {
	return $modx->error->failure('Option not found');
}

$option->remove();

return $modx->error->success('');
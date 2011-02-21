<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

$moduleId = (int) $_REQUEST['id'];

$module = $modx->getObject('vcModule', $moduleId);

if ($module == null) {
	return $modx->error->failure('Module not found');
}

$module->remove();

return $modx->error->success('');
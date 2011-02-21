<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

$query = $modx->getOption('query', $scriptProperties, '');
$path = $modx->visioncart->config['assetsBasePath'].'web/themes/';

$list = array();
if (is_dir($path)) {
	$handle = opendir($path);
	while(false !== ($file = readdir($handle))) {
		if (substr($file, 0, 1) == '.') {
			continue;	
		}
		
		$list[] = array(
			'theme' => $file
		);	
	}
}

return $this->outputArray($list, sizeof($list));
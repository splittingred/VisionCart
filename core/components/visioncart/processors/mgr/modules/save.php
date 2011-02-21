<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

$moduleObject = (array) json_decode($_REQUEST['formData']);
$type = $_REQUEST['currentTab'];

if (isset($moduleObject['id']) && !empty($moduleObject['id']) && $moduleObject['id'] != 0) {
	$newModule = $modx->getObject('vcModule', $moduleObject['id']);
	
	// Get the config values from the REQUEST and combine them
	$configValues = array();
	foreach ($moduleObject as $key => $value){
		if (substr($key, 0, 7) == 'config_' ){
			$key = substr($key, 7);
			$configValues[$key] = $value;
		}	
	}
	
	// Set module data
	$newModule->set('name', $moduleObject['name']);
	$newModule->set('description', $moduleObject['description']);
	$newModule->set('controller', $moduleObject['controller']);
	$newModule->set('active', $moduleObject['active']);
	$newModule->set('config', $configValues);
} else {
	$newModule = $modx->newObject('vcModule', array(
		'type' => $type,
		'name' => $moduleObject['name'],
		'description' => $moduleObject['description'],
		'controller' => $moduleObject['controller'],
		'active' => $moduleObject['active']
	));
}

// Return values
if ($newModule->save()) {
	return $modx->error->success('', $newModule);
} else {
	return $modx->error->failure('');
}
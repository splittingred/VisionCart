<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

$categoryId = explode('|', $_REQUEST['categoryId']);
$categoryId = explode(':', $categoryId[0]);
$categoryId = (int) $categoryId[1];	

$category = $modx->getObject('vcCategory', $categoryId);

if ($category == null) {
	return $modx->error->failure('Category not found');
}

$customFields = $category->get('customfields');

if (!is_array($customFields)) {
	$customFields = array();	
}

$mandatory = 1;
if ($_REQUEST['mandatory'] == 'false') {
	$mandatory = 0;	
}

if ($_POST['isUpdate'] == true) {
	if (isset($customFields[$_REQUEST['oldField']])) {
		unset($customFields[$_REQUEST['oldField']]);
	}
}

$values = '';
if (isset($_REQUEST['values'])) {
	$values = $_REQUEST['values'];
}

$customFields[$_REQUEST['name']] = array(
	'type' => $_REQUEST['type'],
	'mandatory' => $mandatory,
	'values' => $values
);

$category->set('customfields', $customFields);
$category->save();

return $modx->error->success('', $category);
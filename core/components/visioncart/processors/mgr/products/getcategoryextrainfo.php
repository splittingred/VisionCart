<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

$prodId = (int) $_REQUEST['prodid'];

$product = $modx->getObject('vcProduct', $prodId);
$currentValues = $product->get('customfields');

$categoryLinks = $product->getMany('ProductCategory');

$outputArray = array();

foreach($categoryLinks as $categoryLink) {
	$category = $categoryLink->getOne('Category');
	$category = $category->toArray();
	if (!empty($category['customfields'])) {
		$outputArray[] = array(
			'type' => 'fieldset',
			'mandatory' => false,
			'fieldLabel' => $category['name']
		);
		
		foreach($category['customfields'] as $customKey => $customField) {
			$defaultValue = '';
			if (isset($currentValues[$category['id']][$customKey])) {
				$defaultValue = $currentValues[$category['id']][$customKey];	
			}
			
			$itemValues = array();
			if ($customField['type'] == 'combobox') {
				if (substr($customField['values'], 0, 8) == '@SNIPPET') {
					$snippetName = substr($customField['values'], 9);
					$customField['values'] = $modx->runSnippet($snippetName, array(
						'product' => $product
					));	
				}
				
				$values = explode("\n", $customField['values']);
				foreach($values as $value) {
					if (trim($value) != '') {
						$itemValues[] = array(
							'option' => $value,
							'value' => $value
						);
					}
				}	
			}
			
			$outputArray[] = array(
				'mandatory' => $customField['mandatory'],
				'key' => $category['id'].'_'.$customKey,
				'fieldLabel' => $customKey,
				'type' => $customField['type'],
				'value' => $defaultValue,
				'values' => $itemValues
			);
		}
	}
}

return $modx->error->success('', $outputArray);
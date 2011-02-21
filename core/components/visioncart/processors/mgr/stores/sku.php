<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

$prodId = (int) $scriptProperties['prodId'];

$query = $modx->newQuery('vcProduct');
$query->where(array('parent' => $prodId));
$query->orCondition(array('id' => $prodId));

$products = $modx->getCollection('vcProduct', $query);

$list = array();
foreach ($products as $product) {
	$options = $product->getMany('ProductOption');
	$details = '<table width="100%">';
	
	if (!empty($options)) {
		$details .= '<tr><td colspan="2"><h3>Attributes</h3></td></tr>';
		
		foreach($options as $option) {
			$value = $option->getOne('OptionValue');
			$attrib = $option->getOne('Option');
			
			if ($value == null || $attrib == null) {
				continue;
			}
			
			$details .= '<tr><td><b>'.$attrib->get('name').'</b></td><td>'.$value->get('value').'</td></tr>';
		}
	}
	
	$product = $product->toArray();
	
	if (!empty($product['customfields'])) {
		$details .= '<tr><td colspan="2"><br /><h3>Extra fields</h3></td></tr>';
		
		foreach($product['customfields'] as $category => $values) {
			foreach($values as $type => $value) {
				$details .= '<tr><td><b>'.$type.'</b></td><td>'.$value.'</td></tr>';
			}
		}
	}
	
	$details .= '</table>';
	
	$product['qtip'] = $details;
    $list[] = $product;
}

return $this->outputArray($list, sizeof($list));
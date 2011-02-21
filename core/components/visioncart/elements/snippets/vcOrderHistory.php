<?php
/**
 * @package visioncart
 */

$vc =& $modx->visioncart;

$scriptProperties['viewOrderResource'] = $modx->getOption('viewOrderResource', $scriptProperties, 0);
$scriptProperties = array_merge($vc->getConfigFile($vc->shop->get('id'), 'orderHistory'), $scriptProperties);

$cache = '';
$output = '';
$orders = $vc->getOrders();

foreach($orders as $order) {
	$rawOrder = $order->toArray('', true);
	$order = $order->toArray();

	$order['ordertime'] = $rawOrder['ordertime'];
	
	$order['display'] = array(
	 	'products' => count($order['basket'])
	);
	
	$order['link'] = $modx->makeUrl($scriptProperties['viewOrderResource']).'?id='.$order['ordernumber']; 
	
	$output .= $vc->parseChunk($scriptProperties['wrapperTpl'], $order);
}

return $output;
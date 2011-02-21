<?php
/**
 * Basket processor for managing the basket via ajax, snippets or post variables
 *
 * The basket processor accepts a couple of methods that control all products within the basket.
 * Please refer to the examples to find out which are possible.
 *
 * @package VisionCart
 * 
 * @method add	 	Add values to the basket from an array (post or snippet) or json (ajax)
 * @method update 	Update and/or add products to the basket
 * @method subtract	Subtract from existing product quantities, ignores unexisting products and removes them if the count drops below 0
 * @method empty	Drops the complete content of the basket, returning an empty array
 * @method remove	Removes products by id from the basket
 * 
 * @config return	Specify the output method (xpdo, json, array)
 * @config outerTpl	Render the basket in a template, functions as a wrapper for the products and counts
 * @config rowTpl	Renders a single product, inserting it into the outerTpl
 * @config products A single dimensioned array or json formatted string
 * @config method	See @method for available methods
 * 
 */

$vc =& $modx->visioncart;
$scriptProperties['config'] = $modx->getOption('config', $scriptProperties, 'default');
$config = $vc->getConfigFile($vc->shop->get('id'), 'getBasket', null, array('config' => $scriptProperties['config']));
$basket = $vc->getBasket(false);
 
// Receive script properties
$scriptProperties = array_merge($config, $scriptProperties);
$scriptProperties['basketAction'] = strtolower($modx->getOption('basketAction', $scriptProperties, ''));
$scriptProperties['return'] = $modx->getOption('return', $scriptProperties, 'tpl');
$scriptProperties['outerTpl'] = $modx->getOption('outerTpl', $scriptProperties, '');
$scriptProperties['rowTpl'] = $modx->getOption('rowTpl', $scriptProperties, '');
$scriptProperties['emptyBasketTpl'] = $modx->getOption('emptyBasketTpl', $scriptProperties, '');
$scriptProperties['products'] = $modx->getOption('products', $scriptProperties, '');
$scriptProperties['product'] = $modx->getOption('product', $scriptProperties, '');

$methods = array('add', 'update', 'subtract', 'empty', 'remove');

if (in_array($scriptProperties['basketAction'], $methods)) {
	// If there wasn't a basket yet, create one for processing
	if ($basket == null) {
		$basket = $vc->getBasket();
	}
	
	if ($scriptProperties['products'] == '' && $scriptProperties['product'] == '') {
		$scriptProperties['products'] = false;
	} elseif ($scriptProperties['products'] == '' && $scriptProperties['product'] != '') {
		$scriptProperties['products'] = json_encode(array((int) $scriptProperties['product'] => 1));
	}
	
	$products = json_decode($scriptProperties['products'], true);
	
	switch($scriptProperties['basketAction']) {
		case 'add':
			$currentBasket = $basket->get('basket');
			foreach($products as $productId => $quantity) {
				if (!isset($currentBasket[$productId])) {
					$product = $vc->getProduct((int) $productId, array(
						'asArray' => true
					));
					
					if ($product == null) {
						continue;	
					}
					
					$product['quantity'] = 0;
					$currentBasket[$productId] = $product;
				}
				
				$currentBasket[$productId]['quantity'] += $quantity;
			}
			
			$basket->set('basket', $currentBasket);
			break;
		case 'update':
			$currentBasket = array();
			foreach($products as $productId => $quantity) {
				$product = $vc->getProduct((int) $productId, array(
					'asArray' => true
				));
				
				if ($product == null) {
					continue;	
				}
				
				$product['quantity'] = 0;
				$currentBasket[$productId] = $product;
				$currentBasket[$productId]['quantity'] = $quantity;
			}
			
			$basket->set('basket', $currentBasket);
			break;
		case 'subtract':
			$currentBasket = $basket->get('basket');
			foreach($products as $productId => $quantity) {
				if (isset($currentBasket[$productId])) {		
					$currentBasket[$productId]['quantity'] -= $quantity;
					
					// If quantity = 0 or below remove from basket
					if ($currentBasket[$productId]['quantity'] <= 0) {
						unset($currentBasket[$productId]);	
					}
				}
			}
			
			$basket->set('basket', $currentBasket);
			break;
		case 'empty':
			$basket->set('basket', array());
			break;
		case 'remove':
			$currentBasket = $basket->get('basket');
			foreach($products as $productId => $quantity) {
				if (isset($currentBasket[$productId])) {		
					unset($currentBasket[$productId]);
				}
			}
			
			$basket->set('basket', $currentBasket);
			break;	
	}
	
	$currentBasket = $basket->get('basket');
	if (isset($currentBasket) && !empty($currentBasket)) {
		foreach($currentBasket as $key => $product) {
			$productPrice = $vc->calculateProductPrice($product, true);
			$currentBasket[$key]['display']['price'] = $productPrice;			
			$currentBasket[$key]['display']['subtotalin'] = number_format(($productPrice['in'] * $currentBasket[$key]['quantity']), 2, '.', '');			
			$currentBasket[$key]['display']['subtotalex'] = number_format(($productPrice['ex'] * $currentBasket[$key]['quantity']), 2, '.', '');			
		}
		$basket->set('basket', $currentBasket);
	}
	
	$basket->save();
} elseif ($scriptProperties['basketAction'] != '') {
	return $modx->error->failure('Method does not exist');	
}

switch($scriptProperties['return']) {
	case 'json':
		if (!is_null($basket)) {
			return json_encode(array(
				'basket' => $basket->get('basket'),
				'items' => sizeof($basket->get('basket'))
			));
		} else {
			return json_encode(array(
				'basket' => array(),
				'items' => 0
			));
		}
		break;
	case 'tpl':
		if (!is_null($basket)) {
			$basketArray = $basket->get('basket');
			$items = sizeof($basket->get('basket'));
		} else {
			$basketArray = array();
			$items = 0;
		}
		
		if (empty($basketArray)) {
			return $vc->parseChunk($scriptProperties['emptyBasketTpl']);
		} else {
			$innerChunk = '';
			foreach($basketArray as $product) {
				$innerChunk .= $vc->parseChunk($scriptProperties['rowTpl'], $product);
			}	
			
			$returnValue = $vc->parseChunk($scriptProperties['outerTpl'], array(
				'innerChunk' => $innerChunk,
				'items' => $items,
				'order' => $basket
			));
			
			echo $returnValue;
		}
		break;	
}
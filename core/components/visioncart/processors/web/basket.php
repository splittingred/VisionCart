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
 * @example The following example updates a basket by adding 2 products with 3 and 10, setting their count (quantity) to 5 and 19.
 *  $modx->executeProcessor(array(
 *		'location' => 'web',
 *		'action' => 'basket',
 *		'add' => array(
 *			3 => 5,
 *			10 => 19
 *		)
 *	 ));
 * 
 * @example The following example adds products to the basket via AJAX (use the engine) Product 3 with a quantity of 9 and so on
 *	this.request({
 *		params: {
 *			action: 'basket',
 *			basketAction: 'add',
 *			products => Ext.encode({
 *				3: 1,
 *				2: 1,
 *				9: 3
 *			})
 *		}	
 *	});
 *
 */
 
if (!isset($modx->visioncart) || $modx->visioncart == null) {
	$modx->addPackage('visioncart', $modx->getOption('core_path').'components/visioncart/model/');
    $modx->visioncart = $modx->getService('visioncart', 'VisionCart', $modx->getOption('core_path').'components/visioncart/model/visioncart/');	
}

$vc =& $modx->visioncart;
$vc->initialize('web', array(
	'requestURL' => $options['requestURL'],
	'method' => 'processor'
));

$methods = array('add', 'update', 'subtract', 'empty', 'remove');
$options = array_merge($options, $scriptProperties);

// Check the for basketAction (Direct POST or GET)
if (isset($_REQUEST['basketAction'])) {
	$options['basketAction'] = $_REQUEST['basketAction'];
}

// Generic action
if (isset($_REQUEST['action'])) {
	$options['action'] = $_REQUEST['action'];
}

// Single product add
if (isset($_REQUEST['product'])) {
	$options['product'] = $_REQUEST['product'];
}

$config = array(
	// Global parameters
	'return' => $modx->getOption('return', $options, 'json'),
	
	// Read specific functions
	'outerTpl' => $modx->getOption('outerTpl', $options, $vc->getConfigFile($vc->shop->get('id'), 'getBasket', 'outerTpl')),
	'rowTpl' => $modx->getOption('rowTpl', $options, $vc->getConfigFile($vc->shop->get('id'), 'getBasket', 'rowTpl')), 
	'emptyBasketTpl' => $modx->getOption('rowTpl', $options, $vc->getConfigFile($vc->shop->get('id'), 'getBasket', 'emptyBasketTpl')),
	
	// Action specific functions
	'products' => $modx->getOption('products', $options, false),
	'basketAction' => $modx->getOption('basketAction', $options, false),
	'product' => $modx->getOption('product', $options, false)
);

if ($config['product'] != false && $config['product'] != '') {
	$config['products'] = json_encode(array($config['product'] => 1));
}

if (in_array(strtolower($config['basketAction']), $methods) && $config['products'] != '') {
	$basket = $vc->getBasket(); // Creates a basket since we need to process it
	
	// Check user ID
	if ($basket->get('userid') == $modx->user->get('id')) {
		$products = array();
		
		// Allows only json encoded strings with the relation id => count
		if (!is_array($config['products']) && $config['products'] != '') {
			$config['products'] = json_decode($config['products'], true);
		}
		
		// Check if the update array contains values, otherwise there is no need to loop and create a new array
		if (is_array($config['products']) && !empty($config['products'])) {
			foreach($basket->get('basket') as $product) {
				if (!isset($product['quantity'])) {
					$product['quantity'] = 1;
				}
				
				$products[$product['id']] = $product;
			}
			
			switch($config['basketAction']) {
				case 'add':
					foreach($config['products'] as $id => $count) {
						if ($id == '' || !is_numeric($id) || $count == 0 || !is_numeric($count)) {
							continue;
						}
						
						$product = $vc->getProduct((int) $id, array(
							'asArray' => true
						));
						
						if ($product == null) {
							continue;
						}
						
						if (!isset($products[$id])) {
							$products[$id] = array_merge($product, array(
								'quantity' => (int) $count
							));
							
							continue;
						}
						
						$products[$id]['quantity'] += (int) $count;
					}
					break;
				case 'subtract':
					foreach($config['products'] as $id => $count) {
						if ($id == '' || !is_numeric($id) || $count == 0 || !is_numeric($count)) {
							continue;
						}
						
						if (isset($products[$id]) && isset($products[$id]['quantity'])) {
							if ((int) $products[$id]['quantity'] > 1) {
								$products[$id]['quantity'] -= (int) $count;
								
								if ($products[$id]['quantity'] <= 0) {
									unset($products[$id]);
								}
								
								continue;
							}
						}
						
						unset($products[$id]);
					}
					break;
				case 'update':
					foreach($config['products'] as $id => $count) {
						if ($id == '' || !is_numeric($id) || !is_numeric($count)) {
							continue;
						}
						
						if (isset($products[$id]) && (int) $count <= 0) {
							unset($products[$id]);
							continue;
						}
						
						if (!isset($products[$id])) {
							$product = $vc->getProduct((int) $id, array(
								'asArray' => true
							));
							
							if ($product == null) {
								continue;
							}
							
							$products[$id] = array_merge($product, array(
								'quantity' => (int) $count
							));
							continue;
						}
						
						$products[$id]['quantity'] = (int) $count;
					}
					break;
				case 'remove':
					foreach(array_values($config['products']) as $id) {
						if ($id == '' || !is_numeric($id)) {
							continue;
						}
						
						if (isset($products[$id])) {
							unset($products[$id]);
						}
					}
					break;
			}
		}

		$basket->set('basket', $products);
		$basket->save();
		
		// Calculate the new order price
		$vc->calculateOrderPrice($basket);
		$modx->sendRedirect($_SERVER['REQUEST_URI']);
	}
} elseif (strtolower($config['basketAction']) == 'empty') {
	$basket = $vc->getBasket(); // Creates a basket since we need to process it
	
	$basket->set('basket', array());
	$basket->save();
}

if ($config['return'] != false && $config['return'] != '') {
	$basket = $vc->getBasket(false);
	
	// Check user ID
	if ($basket != null && $basket->get('userid') != $modx->user->get('id')) {
		$basket = null;
	}
	
	$products = array();
	$response = array(
		'data' => array(),
		'total' => 0
	);

	$cache = '';
	
	if ($basket == null || sizeof($basket->get('basket')) == 0) {
		switch($config['return']) {
			case 'xpdo':
				return null;
				break;
			case 'json':
				return json_encode($response);		
				break;
			case 'tpl':
				if (substr($config['emptyBasketTpl'], 0, 6) == '@CODE:') {
					return $vc->parseChunk(substr($config['emptyBasketTpl'], 6), $product);
				} else {
					return $vc->parseChunk($config['emptyBasketTpl'], $product, array(
						'isChunk' => true
					));
				}
				break;
		}
	} 
	
	if ($config['return'] == 'xpdo') {
		return $basket;
	}
	
	$products = $basket->get('basket');
	
	if ($config['return'] != 0 && empty($products) && $config['return'] != 'tpl') {
		return json_encode($response);
	}
	
	if (!is_array($products)) {
		$products = array();	
	}
	
	foreach($products as $product) {
		$priceData = $vc->calculateProductPrice($product, true);
		$response['data'][$product['id']] = $product;
		$response['total'] += $product['quantity'];
		$product['display']['price'] = $priceData;

		if (isset($config['return']) && $config['return'] == 'tpl') {
			if (substr($config['rowTpl'], 0, 6) == '@CODE:') {
				$cache .= $vc->parseChunk(substr($config['rowTpl'], 6), $product);
			} else {
				$cache .= $vc->parseChunk($config['rowTpl'], $product, array(
					'isChunk' => true
				));
			}
		}
	}
	
	if (isset($config['return']) && $config['return'] == 'tpl') {
		if (substr($config['outerTpl'], 0, 6) == '@CODE:') {
			$response['tpl'] = $vc->parseChunk(substr($config['outerTpl'], 6),  array(
				'vc.content' => $cache,
				'vc.basket.count' => $response['total'],
				'vc.order' => $basket->toArray(),
				'checkOutUrl' => $modx->makeUrl($vc->getShopSetting('orderProcessResource', $basket->get('shopid')))
			));
		} else {
			$response['tpl'] = $vc->parseChunk($config['outerTpl'], array(
				'vc.content' => $cache,
				'vc.basket' => array(
					'count' => $response['total']
				),
				'vc.order' => $basket->toArray(),
				'checkOutUrl' => $modx->makeUrl($vc->getShopSetting('orderProcessResource', $basket->get('shopid')))
			), array(
				'isChunk' => true
			));
		}
	}
	
	unset($basket, $products, $cache);
	
	switch($config['return']) {
		case 'json':
			return json_encode($response);
			break;
		case 'array':
			return $response;
			break;
		case 'tpl':
			return $response['tpl'];
			break;
	}
}

return true;
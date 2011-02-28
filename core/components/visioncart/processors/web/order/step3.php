<?php

$vc =& $modx->visioncart;
$order = $vc->getBasket();
$action = $modx->makeUrl($modx->resource->get('id'), '', 'step=3');
$previousStep = $modx->makeUrl($modx->resource->get('id'), '', 'step=2');
$content = '';
$shop = $vc->shop;

// Check for authentication and a full basket ;-)
if (!$modx->user->isAuthenticated()) {
	$modx->sendUnauthorizedPage();	
	exit();
} elseif ($order->get('basket') == '' || !is_array($order->get('basket')) || sizeof($order->get('basket')) == 0 || $order->get('status') > 0) {
	$modx->sendRedirect($modx->makeUrl($modx->resource->get('id'), '', 'step=1'));
	exit();
}

// Check for minimum order amount
if ($vc->getShopSetting('enableMinimumOrderAmount') == 1) {
	if ($order->get('totalorderamountin') <= $vc->getShopSetting('minimumOrderAmount')) {
		$modx->sendRedirect($modx->makeUrl($modx->resource->get('id'), '', 'step=1'));
		exit();
	} 
}

// Update order step
if (!isset($_SESSION['vc-order-step'])) {
	$_SESSION['vc-order-step'] = 3;
} elseif ($_SESSION['vc-order-step'] < 3) {
	$_SESSION['vc-order-step'] = 3;
}

$vc->fireEvent('vcEventOrderStep3', '', array(
	'order' => $order
));

// Get theme configuration
$scriptProperties['config'] = $modx->getOption('config', $scriptProperties, 'default');
$config = $vc->getConfigFile($order->get('shopid'), 'orderStep3', null, array('config' => $scriptProperties['config']));
$config = array_merge($config, $scriptProperties);

$chunkArray = array(
	'vcShippingRow' => '', 
	'vcShippingWrapper' => '', 
	'vcOrderStep3' => ''
); 

foreach($chunkArray as $key => $value) {
	if (isset($config[$key])) {
		$chunkArray[$key] = $config[$key];	
	} else {
		$chunkArray[$key] = $key;	
	}
}

// Fetch all shipping modules
$shippingModules = $modx->getCollection('vcModule', array(
	'type' => 'shipping',
	'active' => 1
));

// Get highest tax
$highestTax = $vc->getOrderHighestTax($order);

// Fetch shop shipping modules
$activeModules = $vc->getShopSetting('shippingModules', $order->get('shopid'));

$shippingModuleArray = array();
foreach($shippingModules as $shippingModule) {
	$moduleFound = false;
	foreach($activeModules as $module) {
		if ($module['id'] == $shippingModule->get('id') && $module['active'] == 1) {
			$moduleFound = true;
		}	
	}
	
	if (!$moduleFound) {
		continue;	
	}
	
	$moduleConfig = $shippingModule->get('config');
	
	// Check if order is too light
	if (isset($moduleConfig['shippingMinimumWeight']) && $moduleConfig['shippingMinimumWeight'] != 0) {
		if ($order->get('totalweight') < $moduleConfig['shippingMinimumWeight']) {
			continue;	
		}
	}
	
	// Check if order is too heavy
	if (isset($moduleConfig['shippingMaximumWeight']) && $moduleConfig['shippingMaximumWeight'] != 0) {
		if ($order->get('totalweight') > $moduleConfig['shippingMaximumWeight']) {
			continue;	
		}
	}
	
	// Include the controller to give it a chance to do some stuff as well
	if ($shippingModule->get('controller') != '') {
		$controller = $vc->config['corePath'].'modules/shipping/'.$shippingModule->get('controller');
		if (is_file($controller)) {	
			$vcAction = 'getParams';
			
			$returnValue = include($controller);	
			
			if (!is_array($returnValue)) {
				$returnValue = array();	
			}
			
			if (isset($returnValue['enabled']) && $returnValue['enabled'] == false) {
				continue;
			}
			
			if (isset($module)) {
				unset($module);	
			}
		}
	}
	
	$shippingModuleArray[] = $shippingModule->get('id');
	$shippingModule = $shippingModule->toArray();
	$shippingModule = array_merge($shippingModule, $returnValue);

	$shippingModule['selected'] = '';
	if ($order->get('shippingid') == $shippingModule['id'] && $order->get('shippingid') != '') {
		$shippingModule['selected'] = '1';	
	} 
	
	// Get the shipping row
	$content .= $vc->parseChunk($chunkArray['vcShippingRow'], $shippingModule, array('isChunk' => true));

	unset($returnValue, $module);
}

// Check if a shipping method is chosen, and if so redirect to the next page
if (isset($_REQUEST['vc_shipping_method'])) {
	$shippingMethod = (int) $_REQUEST['vc_shipping_method'];
	
	// Check if the module exists
	if ($shippingMethod != 0 && in_array($shippingMethod, $shippingModuleArray)) {
		// Update order step
		if ($_SESSION['vc-order-step'] <= 3) {
			$_SESSION['vc-order-step'] = 4;
		}
		
		// Update the order and continue
		$order->set('shippingid', $shippingMethod);
		$order->save();
		
		$vc->calculateOrderPrice($order);
		$vc->fireEvent('vcEventChooseShipping', '', array(
			'order' => $order
		));
		
		$modx->sendRedirect($modx->makeUrl($modx->resource->get('id'), '', 'step=4'));
		exit();
	}
}

$content = $vc->parseChunk($chunkArray['vcShippingWrapper'], array(
	'action' => $action,
	'previousStep' => $previousStep,
	'content' => $content
), array('isChunk' => true));

return $vc->parseChunk($chunkArray['vcOrderStep3'], array(
	'action' => $action,
	'previousStep' => $previousStep,
	'content' => $content
), array('isChunk' => true)); 
<?php

$vc =& $modx->visioncart;
$options = array_merge($options, $scriptProperties);
$order = $vc->getBasket(false);

if ($order == null) {
	$order = $modx->getObject('vcOrder', array(
		'ordernumber' => $_REQUEST['order']
	));
}

// Check for authentication and a full basket ;-)
if (!$modx->user->isAuthenticated()) {
	$modx->sendUnauthorizedPage();	
	exit();
} elseif ($order->get('basket') == '' || !is_array($order->get('basket')) || sizeof($order->get('basket')) == 0) {
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

// Check if user tried to get to an order not belonging to him/her
if ($order->get('userid') != $modx->user->id) {
	$modx->sendErrorPage();
	exit();
}

// Update order step
if (!isset($_SESSION['vc-order-step']) || $_SESSION['vc-order-step'] < 6) {
	$modx->sendRedirect($modx->makeUrl($modx->resource->get('id'), '', 'step=5'));
	exit();
}

$vc->fireEvent('vcEventOrderStep6', '', array(
	'order' => $order
));

// Verify payment
$paymentModule = $order->getOne('PaymentModule');
if ($paymentModule != null) {
	if ($paymentModule->get('controller') != '') {
		$controller = $vc->config['corePath'].'modules/payment/'.$paymentModule->get('controller');
		if (is_file($controller)) {	
			$vcAction = 'verifyPayment';
			$parameters['returnUrl'] = $modx->makeUrl($modx->resource->get('id'), '', 'step=6&order='.$orderNumber, 'full');
			
			$returnValue = include($controller);	
			
			if (!is_array($returnValue)) {
				$returnValue = array();	
			}
		}
	}
	
	$paymentData = $order->get('paymentdata');
	$paymentData = array_merge($paymentData, $returnValue);
	$order->set('paymentdata', $paymentData);
	$order->set('basketid', '');
	$order->save();
	
	// Because the order is done we will clear the cart and the cookie as well, this will unlink the basket cookie from the order
	// so the user can start creating a new one if they wish
	$vc->clearBasket();
	unset($_SESSION['vc-order-step']);
	
	if (isset($returnValue['returnUrl'])) {
		$modx->sendRedirect($returnValue['returnUrl']);	
	}
} else {
	$modx->sendRedirect($modx->makeUrl($vc->getShopSetting('orderHistoryResource', $order->get('shopid'))));
}

return '';
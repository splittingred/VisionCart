<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

$visionCart = $modx->visioncart;
$id = (int) $scriptProperties['id'];
$status = (int) $scriptProperties['status'];
$sendMail = (int) $scriptProperties['sendMail'];
$shopId = (int) $scriptProperties['shopId'];

if (isset($id) && !empty($id) && $id != 0) {
	$order = $modx->getObject('vcOrder', $id);
		
	// Set status
	$order->set('status', $status);
}

// Return values
if ($order->save()) {
	if ($sendMail == 1){
		// get user email
		if ($order->get('status') == 1) {
			$pdf = true;	
		} else {
			$pdf = false;	
		}
		$visionCart->sendStatusEmail($order, $pdf);
	}
	return $modx->error->success('', $order);
} else {
	return $modx->error->failure('');
}
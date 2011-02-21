<?php

switch ($options[xPDOTransport::PACKAGE_ACTION]) {  
	case xPDOTransport::ACTION_INSTALL:
	case xPDOTransport::ACTION_UPGRADE:
		// Create a reference to MODx since this resolver is executed from WITHIN a modCategory
		$modx =& $object->xpdo; 
		
		if (!isset($modx->visioncart) || $modx->visioncart == null) {
			$modx->addPackage('visioncart', $modx->getOption('core_path').'components/visioncart/model/');
		    $modx->visioncart = $modx->getService('visioncart', 'VisionCart', $modx->getOption('core_path').'components/visioncart/model/visioncart/');	
		}
		
		$mgr = $modx->getManager();
		$mgr->createObjectContainer('vcModule');
		
		// Create example modules
		$module = $modx->newObject('vcModule');
		$module->fromArray(array(
			'id' => 1,
			'type' => 'payment',
			'name' => 'example',
			'description' => 'Example payment module',
			'controller' => 'example/index.php',
			'config' => array(
				'paymentCountry' => 'all',
				'paymentMaximimumAmount' => 0,
				'paymentPercentage' => 0,
				'paymentCosts' => 5
			),
			'active' => 1
		), '', true, true);
		$module->save();
		
		$module = $modx->newObject('vcModule');
		$module->fromArray(array(
			'id' => 2,
			'type' => 'shipping',
			'name' => 'example',
			'description' => 'Example shipping module',
			'controller' => 'example/index.php',
			'config' => array(
				'shippingCountry' => 'all',
				'shippingMinimumWeight' => 0,
				'shippingMaximumWeight' => 0,
				'shippingPercentage' => 0,
				'shippingCosts' => 5
			),
			'active' => 1
		), '', true, true);
		$module->save();
		
		if ($options['send_email'] == '1') {
			$message = 'VisionCart 0.1 Beta-2 was installed on '.date('d-m-Y H:i')."\n\n";
			$message .= 'Domain: '.$_SERVER['HTTP_HOST'];
			mail('beta@visioncart.net', 'VisionCart 0.1 Beta-2 installed', $message);	
		}
		break;
}
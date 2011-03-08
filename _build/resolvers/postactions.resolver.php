<?php

if ($options[xPDOTransport::PACKAGE_ACTION] == xPDOTransport::ACTION_UPGRADE) {
	$action = 'upgrade';	
} elseif ($options[xPDOTransport::PACKAGE_ACTION] == xPDOTransport::ACTION_INSTALL) {
	$action = 'install';	
}

$success = false;
switch ($action) {  
	case 'upgrade':
	case 'install':
		// Create a reference to MODx since this resolver is executed from WITHIN a modCategory
		$modx =& $object->xpdo; 
		
		if (!isset($modx->visioncart) || $modx->visioncart == null) {
			$modx->addPackage('visioncart', $modx->getOption('core_path').'components/visioncart/model/');
		    $modx->visioncart = $modx->getService('visioncart', 'VisionCart', $modx->getOption('core_path').'components/visioncart/model/visioncart/');	
		}
		
		$mgr = $modx->getManager();
		$mgr->createObjectContainer('vcModule');
		
		if (isset($options['send_email']) && $options['send_email'] == '1') {
			$message = 'VisionCart 0.5.1 RC-1 was installed on '.date('d-m-Y H:i')."\n\n";
			$message .= 'Domain: '.$_SERVER['HTTP_HOST'];
			mail('beta@visioncart.net', 'VisionCart 0.5.1 RC-1 installed', $message);	
		}
		
		if ($action == 'install') {
			// Create example modules
			$module = $modx->getObject('vcModule', array(
				'controller' => 'example/index.php',
				'type' => 'payment'
			));
			if ($module == null) {
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
			}
		}
		
		$success = true;
		break;
}
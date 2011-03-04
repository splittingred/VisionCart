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
		
		$modx->exec("ALTER TABLE {$modx->getTableName('vcProduct')} ADD `tierprice` TEXT NOT NULL default ''");
		$modx->exec("ALTER TABLE {$modx->getTableName('vcCategory')} ADD `tierprice` TEXT NOT NULL default ''");
		
		$success = true;
		break;
}
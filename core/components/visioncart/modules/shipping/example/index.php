<?php

switch($vcAction) {
	case 'calculateOrderAmount':
		/*  
			This is the case where the payment method should return the costs of using this specific method
			Variables available here include: 
				$order (xPDO object: order)
				$paymentModule (xPDO object: module)
				$highestTax (xPDO object: containing the highest found tax in the order (vcCategory))
				$shop (xPDO object: The shop this order belongs to)
			And the array $amounts containing: totalProductsPriceIn/Ex, totalShippingPriceIn/Ex
			Here you can manipulate the order directly and set the order costs with the xPDO object and save it!
			You can return an array which will be merged with the paymentdata column from this order. This array
			will overwrite any colliding values with the current paymentdata array!
			
			Personally I like working with the $module array so the script will not collide with any variables
			defined in the main class ;-)
			
			The $module variable will also be unset after this module is loaded to spare memory
		*/
		
		$vc =& $modx->visioncart;
		$module['config'] = $shippingModule->get('config');
		$module['calculateShippingTaxes'] = (int) $vc->getShopSetting('calculateShippingTaxes', $shop->get('id'));

		// Set fixed shipping costs
		$module['shippingCostsEx'] = $module['config']['shippingCosts'] + $amounts['totalShippingPriceEx'];
		
		// Add percentage (over the products)
		if ((int) $module['config']['shippingPercentage'] > 0) {
			$module['shippingCostsEx'] += ($amounts['totalProductsPriceEx'] / 100) * $module['config']['shippingPercentage'];	
		}
		
		// Check if we need to calculate taxes
		if ($module['calculateShippingTaxes'] == 1) {
			$module['shippingCostsIn'] = (($module['shippingCostsEx'] / 100) * $highestTax->get('pricechange')) + $module['shippingCostsEx'];
		} else {
			$module['shippingCostsIn'] = $module['shippingCostsEx'];
		}
		
		// Change the order xPDO object and save it (this way it gets taken into account when the user pays the order)
		$order->set('shippingcostsex', number_format($module['shippingCostsEx'], 2, '.', ''));
		$order->set('shippingcostsin', number_format($module['shippingCostsIn'], 2, '.', ''));
		$order->save();
		
		// This array gets saved in the order shipping_data column
		$module['shippingData'] = array(
			'time' => time()
		);
		
		return $module['shippingData'];
		break;
	case 'getParams':
		/*
			You can return the payment module's data here (in case of multiple languages for instance you can make
			your custom code here. If you don't need anything fancy/custom don't do anything here. The shipping costs
			will be calculated when this module is chosen. If you want to show something like *live* shipping costs
			you can choose to set custom variables here and they will be present in your chunk. You should ALWAYS
			return an array. It get's merged with the module itself (so you can even overwrite the module name with
			lexicon values here...
			
			You can also do live calculation's here...and decide not to include this module as a possible shipping option.
			Just send 'enabled' as false in the return array and it won't show up.
		*/
		
		if ($highestTax == null) {
			$highestTax = $vc->getOrderHighestTax($order);	
		}
		
		$module['calculateShippingTaxes'] = (int) $vc->getShopSetting('calculateShippingTaxes', $shop->get('id'));
		$module['config'] = $shippingModule->get('config');
		
		if ((int) $module['config']['shippingPercentage'] > 0) {
			$percentageAmount = ($order->get('totalproductamountex') / 100) * (int) $module['config']['shippingPercentage'];
		} else {
			$percentageAmount = 0;	
		}
		
		$module['shippingCosts'] = $module['config']['shippingCosts'] + $percentageAmount;

		// Check if we need to calculate taxes
		if ($module['calculateShippingTaxes'] == 1) {
			$module['shippingCosts'] = (($module['shippingCosts'] / 100) * $highestTax->get('pricechange')) + $module['shippingCosts'];
		}
		
		return array(
			'enabled' => true,
			'costs' => $vc->money($module['shippingCosts']),
			'name' => 'Example shipping module'
		);
		break;
}
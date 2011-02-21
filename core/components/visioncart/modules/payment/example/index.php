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
		$module['config'] = $paymentModule->get('config');
		$module['calculatePaymentTaxes'] = (int) $vc->getShopSetting('calculatePaymentTaxes', $shop->get('id'));

		// Set fixed shipping costs
		$module['paymentCostsEx'] = $module['config']['paymentCosts'];
		
		// Add percentage (over the products)
		if ((int) $module['config']['paymentPercentage'] > 0) {
			$module['paymentCostsEx'] += ($amounts['totalProductsPriceEx'] / 100) * $module['config']['paymentPercentage'];	
		}
		
		// Check if we need to calculate taxes
		if ($module['calculatePaymentTaxes'] == 1) {
			$module['paymentCostsIn'] = (($module['paymentCostsEx'] / 100) * $highestTax->get('pricechange')) + $module['paymentCostsEx'];
		} else {
			$module['paymentCostsIn'] = $module['paymentCostsEx'];
		}
		
		// Change the order xPDO object and save it (this way it gets taken into account when the user pays the order)
		$order->set('paymentcostsex', number_format($module['paymentCostsEx'], 2, '.', ''));
		$order->set('paymentcostsin', number_format($module['paymentCostsIn'], 2, '.', ''));
		$order->save();
		
		$module['paymentData'] = array(
			'calculation' => false
		);
		
		return $module['paymentData'];
		break;
	case 'getParams':
		/*
			You can return the payment module's data here (in case of multiple languages for instance you can make
			your custom code here). If you don't need anything fancy/custom just return an empty array
			and that should suffice.
		*/
		
		if ($highestTax == null) {
			$highestTax = $vc->getOrderHighestTax($order);	
		}
		
		$module['config'] = $paymentModule->get('config');
		$module['calculatePaymentTaxes'] = (int) $vc->getShopSetting('calculatePaymentTaxes', $shop->get('id'));
		
		if ((int) $module['config']['paymentPercentage'] > 0) {
			$percentageAmount = ($order->get('totalproductamountex') / 100) * (int) $module['config']['paymentPercentage'];
		} else {
			$percentageAmount = 0;	
		}
		
		$module['paymentCosts'] = $module['config']['paymentCosts'] + $percentageAmount;
		
		// Check if we need to calculate taxes
		if ($module['calculatePaymentTaxes'] == 1) {
			$module['paymentCosts'] = (($module['paymentCosts'] / 100) * $highestTax->get('pricechange')) + $module['paymentCosts'];
		}
		
		return array(
			'enabled' => true,
			'costs' => $vc->money($module['paymentCosts']),
			'name' => 'Example payment module'
		);
		break;
	case 'doPayment':
		/*
			This is where your module should do it's payment. This could mean sending a redirect to a different site...or maybe even
			creating some XML request, sending it away and then redirect to a different site, or even set the payment to "completed".
			Variables available here:
			$order (xPDO object: order)
			$paymentModule (xPDO object: payment module)
			$parameters (array: containing the keys/values)
				"returnUrl", the URL to which your payment module must return to get to the "verifyPayment" case)
		*/
		
		$parameters['ordernumber'] = $order->get('ordernumber');
		$parameters['amount'] = $order->get('totalorderamountin');
		
		// Instead of doing actual payment, in this example we will just set the order to paid ;)
		$order->set('paidamount', $parameters['amount']);
		$order->save();
		
		// Let's verify (normally your payment provider will return to this URL)
		// You MUST redirect to somewhere here...else your users will be left with a white page ;-)
		$modx->sendRedirect($parameters['returnUrl']);
		break;
	case 'verifyPayment':
		/*
			This is where your module should update the `paidamount` column in the order database so VisionCart knows IF the order is
			paid and how much is paid (if it was enough).
			Variables available here:
			$order (xPDO object: order)
			$paymentModule (xPDO object: payment module)
			
			Set your order to a new status here
			1 = new
			2 = confirmed
			3 = paid
			4 = shipped/handled
			
			Be sure to return an array that AT LEAST contains a returnUrl (where the user will go to after payment has either succeeded
			or failed). This complete array will be stored in the 'paymentdata' column with the order. (It will be merged with previous
			values and this case overwrite previous cases)
		*/
		
		// Set order to paid (normally you would want to verify some XML here)
		if ($order->get('paidamount') >= $order->get('totalorderamountin')) {
			$order->set('status', 3);
			$order->save();
			
			// Because the order is paid, we're gonna send an email
			// We have just updated the status so the function knows what chunk to get
			// 2nd parameter false, because we don't want ANOTHER pdf to be created...
			// They already received it in their mailbox ;)
			$vc->sendStatusEmail($order, false);
		}
		
		
		
		return array(
			'returnUrl' => $modx->makeUrl($vc->getShopSetting('orderHistoryResource', $order->get('shopid'))),
			'paymentVerified' => 1,
			'paymentDate' => time()
		);
		break;
}
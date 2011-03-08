<?php
if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
	if (isset($_REQUEST['basketAction']) && $_REQUEST['basketAction'] == 'add') {
		if (isset($_REQUEST['product'])) {
			foreach($_REQUEST as $key => $value) {
				if (substr($key, 0, 10) == 'vc_option_') {
					$order = $modx->visioncart->getBasket();
					$basket = $order->get('basket');
					
					if (is_array($basket) && !empty($basket)) {
						foreach($basket as $basketKey => $product) {
							if ($product['id'] == $_REQUEST['product']) {
								$basket[$basketKey]['customfields'][substr($key, 10)] = $value;
								
								$order->set('basket', $basket);
								$order->save();
							}	
						}	
					}
				}		
			}
		}
	}
}
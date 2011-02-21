<?php

// See wether the options are passed on by the hook or the scriptProperties (processor)
if (!isset($options['hook']) || !is_object($options['hook'])) {
	$values = $scriptProperties;
} else {
	$hook =& $options['hook'];
	$values = $hook->getValues();
}

$profile = $modx->user->getOne('Profile');
$extended = $profile->get('extended');

// Set the default VisionCart key if extended is empty
if (empty($extended) || !is_array($extended)) {
	$extended = array(
		'VisionCart' => array()
	);
}

// Set the default VisionCart key if extended is in use and hasn't been set
if (!isset($extended['VisionCart'])) {
	$extended['VisionCart'] = array();
}

$extended['VisionCart']['shippingaddress'] = array(
	'fullname' => $values['shipping_fullname'],
	'address' => $values['shipping_address'],
	'zip' => $values['shipping_zip'],
	'city' => $values['shipping_city'],
	'state' => $values['shipping_state'],
	'country' => $values['shipping_country']
);

if (isset($values['billing_as_shipping']) && (int) $values['billing_as_shipping'] == 1) {
	$extended['VisionCart']['billingaddress'] = $extended['VisionCart']['shippingaddress'];
	$extended['VisionCart']['billing_as_shipping'] = 1;
} else {
	$extended['VisionCart']['billing_as_shipping'] = 0;
	$extended['VisionCart']['billingaddress'] = array(
		'fullname' => $values['billing_fullname'],
		'address' => $values['billing_address'],
		'zip' => $values['billing_zip'],
		'city' => $values['billing_city'],
		'state' => $values['billing_state'],
		'country' => $values['billing_country']
	);
}

$profile->fromArray(array(
	'fullname' => $values['shipping_fullname'],
	'address' => $values['shipping_address'],
	'zip' => $values['shipping_zip'],
	'city' => $values['shipping_city'],
	'state' => $values['shipping_state'],
	'country' => $values['shipping_country'],
	'extended' => $extended	
));

$profile->save();

unset($values, $profile, $extended);
$modx->sendRedirect($modx->makeUrl($modx->resource->get('id'), '', 'step=2'));
exit();
return '';
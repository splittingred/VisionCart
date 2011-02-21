<?php
/**
 * @package visioncart
 */

if (!$modx->user->isAuthenticated()) {
	$modx->sendUnauthorizedPage();
	return '';
}
$profile = $modx->user->getOne('Profile');
$extended = $profile->get('extended');
$fields = array();

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

if (!isset($extended['VisionCart']['shippingaddress']) || $extended['VisionCart']['shippingaddress']['fullname'] == '') {
	$extended['VisionCart']['shippingaddress'] = array(
		'fullname' => $profile->get('fullname'),
		'address' => $profile->get('address'),
		'zip' => $profile->get('zip'),
		'city' => $profile->get('city'),
		'state' => $profile->get('state'),
		'country' => $profile->get('country')
	);
}

if (!isset($extended['VisionCart']['billingaddress']) || $extended['VisionCart']['billingaddress']['fullname'] == '') {
        $extended['VisionCart']['billing_as_shipping'] = 1;
}

if (!isset($extended['VisionCart']['billing_as_shipping']) || (int) $extended['VisionCart']['billing_as_shipping'] == 1) {
	$extended['VisionCart']['billing_as_shipping'] = 1;
	$extended['VisionCart']['billingaddress'] = $extended['VisionCart']['shippingaddress'];
}

$profile->set('extended', $extended);
$profile->save();

foreach($extended['VisionCart']['shippingaddress'] as $key => $value) {
	$fields['shipping_'.$key] = $value;
}

foreach($extended['VisionCart']['billingaddress'] as $key => $value) {
	$fields['billing_'.$key] = $value;
}

$fields['billing_as_shipping'] = (int) $extended['VisionCart']['billing_as_shipping'];
$hook->setValues($fields);

unset($profile, $extended, $fields, $key, $value);
return '';
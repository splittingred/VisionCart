<?php
/**
 * @package visioncart
 */

$vc =& $modx->visioncart;

if (!isset($scriptProperties['id']) || !is_numeric($scriptProperties['id'])) {
	return;
}

$scriptProperties['config'] = $modx->getOption('config', $scriptProperties, 'default');
$config = $vc->getConfigFile($vc->shop->get('id'), 'getProductImages', null, array('config' => $scriptProperties['config']));
$scriptProperties = array_merge($config, $scriptProperties);

$scriptProperties['rel'] = $modx->getOption('rel', $scriptProperties, '');
$scriptProperties['limit'] = $modx->getOption('limit', $scriptProperties, 0);
$scriptProperties['offset'] = $modx->getOption('offset', $scriptProperties, 0);
$scriptProperties['prefix'] = $modx->getOption('prefix', $scriptProperties, false);
$scriptProperties['shopId'] = $modx->getOption('shopId', $scriptProperties, 0);

$internal = array(
	'limit' => 0,
	'offset' => 0,
	'count' => 0
);

$path = array(
	'url' => $vc->config['assetsUrl'].'web/images/products/',
	'abs' => $vc->config['assetsBasePath'].'web/images/products/'
);

// Load the shop for the images config
if ($scriptProperties['shopId'] != 0) {
	$shop = $vc->getShop(array(
		'asArray' => true,
		'value' => $scriptProperties['shopId']
	));
} else {
	$shop = $vc->getShop(array(
		'asArray' => true
	));
}

if (!isset($shop['config']['thumbnails']) || $shop['config']['thumbnails'] == '') {
	return;
}

$thumbnails = $vc->getThumbnailSettings($shop['id']);

// Load the product
$product = $vc->getProduct($scriptProperties['id'], array(
	'asArray' => true
));

if (!empty($product) && isset($product['pictures']) && !empty($product['pictures'])) {
	$output = '';
	
	if ($scriptProperties['prefix'] != false) {
		foreach($thumbnails as $thumbnail) {
			if ($thumbnail['prefix'] == $scriptProperties['prefix']) {
				break;
			}
		}
		
		foreach($product['pictures'] as $image) {
			if ($scriptProperties['limit'] != 0 && $internal['limit'] >= $scriptProperties['limit']) {
				break;
			}
			
			if ($scriptProperties['offset'] != 0 && $internal['offset'] < $scriptProperties['offset']) {
				$internal['offset']++;
				continue;
			}
			
			if ($thumbnail['crop'] == 'false') {
				$dimensions = getimagesize($path['abs'].$thumbnail['prefix'].$image);
			}
			
			$output .= $vc->parseChunk($scriptProperties['tpl'], array(
				'src' => $path['url'].$thumbnail['prefix'].$image,
				'title' => $product['name'],
				'alt' => $product['name'],
				'rel' => $scriptProperties['rel'],
				'width' => isset($dimensions) ? $dimensions[0] : '',
				'height' => isset($dimensions) ? $dimensions[1] : '',
				'count' => $internal['count']
			));
			
			$internal['limit']++;
			$internal['count']++;
			unset($dimensions);
		}
	} else {
		foreach($thumbnails as $thumbnail) {
			foreach($product['pictures'] as $image) {
				if ($thumbnail['crop'] == 'false') {
					$dimensions = getimagesize($path['abs'].$thumbnail['prefix'].$image);
				}
				
				$output .= $vc->parseChunk($scriptProperties['tpl'], array(
					'src' => $path['url'].$thumbnail['prefix'].$image,
					'title' => $product['name'],
					'alt' => $product['name'],
					'rel' => $scriptProperties['rel'],
					'width' => isset($dimensions) ? $dimensions[0] : '',
					'height' => isset($dimensions) ? $dimensions[1] : '',
				));
				
				unset($dimensions);
			}
		}
	}
	
	return $output;
}
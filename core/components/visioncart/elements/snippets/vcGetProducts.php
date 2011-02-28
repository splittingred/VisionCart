<?php
/**
 * @package visioncart
 */

$vc =& $modx->visioncart;

$output = array(
    'master' => ''
);

$scriptProperties['config'] = $modx->getOption('config', $scriptProperties, 'default');
$config = $vc->getConfigFile($vc->shop->get('id'), 'getProducts', null, array('config' => $scriptProperties['config']));

$scriptProperties = array_merge($config, $scriptProperties);

// Settings
$scriptProperties['parents'] = $modx->getOption('parents', $scriptProperties, 0);
$scriptProperties['limit'] = $modx->getOption('limit', $scriptProperties, 0);
$scriptProperties['offset'] = $modx->getOption('offset', $scriptProperties, 0);
$scriptProperties['hideInactive'] = $modx->getOption('hideInactive', $scriptProperties, 0);
$scriptProperties['includeContent'] = $modx->getOption('includeContent', $scriptProperties, 0);
$scriptProperties['hideSKU'] = $modx->getOption('hideSKU', $scriptProperties, true);
$scriptProperties['exclude'] = $modx->getOption('exclude', $scriptProperties, '');
$scriptProperties['scheme'] = $modx->getOption('scheme', $scriptProperties, -1);

// Sorting settings
$scriptProperties['sort'] = $modx->getOption('sort', $scriptProperties, 'ASC');
$scriptProperties['sortBy'] = $modx->getOption('sortBy', $scriptProperties, 'name');
$scriptProperties['sortable'] = $modx->getOption('sortable', $scriptProperties, 'name,price');

// Sorting settings for extra fields
$scriptProperties['extraFieldSort'] = $modx->getOption('extraFieldSort', $scriptProperties, 'ASC');
$scriptProperties['extraFieldSortBy'] = $modx->getOption('extraFieldSortBy', $scriptProperties, '');

// Templating
$scriptProperties['tpl'] = $modx->getOption('tpl', $scriptProperties, '');
$scriptProperties['tplOdd'] = $modx->getOption('tplOdd', $scriptProperties, $scriptProperties['tpl']);
$scriptProperties['tplFirst'] = $modx->getOption('tplFirst', $scriptProperties, $scriptProperties['tpl']);
$scriptProperties['tplLast'] = $modx->getOption('tplLast', $scriptProperties, $scriptProperties['tpl']);

if (!isset($scriptProperties['tpl'])  || $scriptProperties['tpl'] == '') {
    return '';
}

// Querystring based search
$router = $vc->router['query'];
unset($router['q']);

$scriptProperties['sort'] = $modx->getOption('sort', $router, 'asc');

if (isset($router['index']) && in_array($router['index'], explode(',', $scriptProperties['sortable']))) {
    $scriptProperties['sortBy'] = $modx->getOption('index', $router, 'asc');    
}

$internal = array(
    'limit' => 0,
    'offset' => 0
);

if ($scriptProperties['parents'] == false) {
    return '';
}

foreach($scriptProperties as $parameter => $property) {
    if (substr($parameter, 0, 3) == 'tpl' && !in_array($parameter, array('tpl', 'tplOdd', 'tplFirst', 'tplLast'))) {
        if (!is_numeric(substr($parameter, 3))) {
            continue;
        }
        
        $scriptProperties['tplNth'][] = array(
            'nth' => (int) substr($parameter, 3),
            'tpl' => $property
        );
        
        unset($scriptProperties[$parameter]); // Clean up on isle 5!
    }
}

if ($scriptProperties['parents'] == 0) {
    return '';
}

$scriptProperties['parents'] = explode(',',  $scriptProperties['parents']);
$scriptProperties['exclude'] = explode(',',  $scriptProperties['exclude']);

$products = array();
foreach($scriptProperties['parents'] as $parent) {
    // Fetch the parent (for shop ID)
    if (!isset($modx->visioncart) || !isset($modx->visioncart->shop)) {
        $category = $modx->getObject('vcCategory', (int) $parent);
        $shopId = $category->get('shopid');
    } else {
        $shopId = $modx->visioncart->shop->get('id');
    }

    $stack = $vc->getProducts($parent, array(
        'hideSKU' => $scriptProperties['hideSKU'],
        'shopId' => $shopId
    ));

    if ($stack['total'] != 0 && !empty($stack['data'])) {
        foreach($stack['data'] as $product) {
            if (in_array($product->get('id'), $scriptProperties['exclude'])) {
            	continue;	
            }
            
            $product->set('url', $vc->makeUrl(array(
                'productCategory' => $product->get('linkId'),
                'shopId' => $product->get('shopid'),
                'scheme' => $scriptProperties['scheme']
            )));
            
            $priceData = $vc->calculateProductPrice($product, true);
            $product->set('display', array('price' => $priceData));

            $products[] = $product->toArray();    
        }
    }
    
    unset($stack);
}

if ($scriptProperties['extraFieldSortBy'] != '') {
    $products = array_values($vc->multiNaturalSort($products, 'customfields.'.$scriptProperties['extraFieldSortBy'], strtolower($scriptProperties['extraFieldSort'])));
} else {
    $products = array_values($vc->multiNaturalSort($products, $scriptProperties['sortBy'], strtolower($scriptProperties['sort'])));
}

for($inc = 0; $inc < count($products); $inc++) {
    if ($scriptProperties['offset'] != 0 && $internal['offset'] <= $scriptProperties['offset']) {
        $internal['offset']++;
        continue;
    }
    
    if ($scriptProperties['limit'] != 0 && $internal['limit'] >= $scriptProperties['limit']) {
        break;
    }
    
    $internal['limit']++;
    
    if ($inc == 0) {
        $output['master'] .= $vc->parseChunk($scriptProperties['tplFirst'], array_merge($products[$inc], array(
            'count' => $inc
        )));
        continue;
    } else if ($inc == count($products)) {
        $output['master'] .= $vc->parseChunk($scriptProperties['tplLast'], array_merge($products[$inc], array(
            'count' => $inc
        )));
        continue;
    }
    
    // Raffle through a set of nth templates, if they've been set
    if (isset($scriptProperties['tplNth']) && !empty($scriptProperties['tplNth'])) {
        $break = false;
        
        foreach($scriptProperties['tplNth'] as $tpl) {
            if ($tpl['nth'] == $inc) {
                $output['master'] .= $vc->parseChunk($tpl['tpl'], array_merge($products[$inc], array(
                    'count' => $inc
                )));
                $break = true;
                break;
            }
        }
    }
    
    // Break the loop since a nth tpl has been found
    if (isset($break) && $break == true) {
        continue;
    }
    
    if ($inc % 2) {
        $output['master'] .= $vc->parseChunk($scriptProperties['tplOdd'], array_merge($products[$inc], array(
            'count' => $inc
        )));
        continue;
    }
    
    $output['master'] .= $vc->parseChunk($scriptProperties['tpl'], array_merge($products[$inc], array(
        'count' => $inc
    )));
}

unset($scriptProperties, $products, $product, $interal);
return $output['master'];
<?php
/**
 * @package visioncart
 */

if (!isset($scriptProperties['id'])) {
	return;	
}

$vc =& $modx->visioncart;

$columns = array();
$indexes = array();
$output = array(
	'master' => '',
	'headers' => '',
	'content' => '',
	'footer' => array(),
	'tmp' => ''
);

$defaults = array(
	'limit' => 10,
	'offset' => 0
);

$pagination = array(
	'items' => 0,
	'pages' => 0,
	'page' => 1
);

$scriptProperties['config'] = $modx->getOption('config', $scriptProperties, 'default');
$config = $vc->getConfigFile($scriptProperties['shopId'], 'getProductList', null, array('config' => $scriptProperties['config']));
$scriptProperties = array_merge($config, $scriptProperties);

// Configuration
$scriptProperties['columns'] = $modx->getOption('columns', $scriptProperties, 'Name==name,Description==description,Price==price,Quantity==quantity');
$scriptProperties['searchable'] = $modx->getOption('searchable', $scriptProperties, 'name,alias,description,articlenumber');
$scriptProperties['linkable'] = $modx->getOption('linkable', $scriptProperties, 'name');
$scriptProperties['columnLexicon'] = $modx->getOption('columnLexicon', $scriptProperties, false);
$scriptProperties['lexicon'] = $modx->getOption('lexicon', $scriptProperties, 'visioncart:default');
$scriptProperties['limit'] = $modx->getOption('limit', $scriptProperties, $defaults['limit']);
$scriptProperties['offset'] = $modx->getOption('offset', $scriptProperties, $defaults['offset']);
$scriptProperties['sort'] = $modx->getOption('sort', $scriptProperties, 'ASC');
$scriptProperties['sortBy'] = $modx->getOption('sortBy', $scriptProperties, 'name');
$scriptProperties['scheme'] = $modx->getOption('scheme', $scriptProperties, -1);

// Template
$scriptProperties['wrapperTpl'] = $modx->getOption('wrapperTpl', $scriptProperties);
$scriptProperties['headerTpl'] = $modx->getOption('headerTpl', $scriptProperties);
$scriptProperties['headerTplColumn'] = $modx->getOption('headerTplColumn', $scriptProperties);
$scriptProperties['rowTpl'] = $modx->getOption('rowTpl', $scriptProperties);
$scriptProperties['rowTplColumn'] = $modx->getOption('rowTplColumn', $scriptProperties);
$scriptProperties['rowLinkTplColumn'] = $modx->getOption('rowLinkTplColumn', $scriptProperties);
$scriptProperties['footerTpl'] = $modx->getOption('footerTpl', $scriptProperties);
$scriptProperties['limitTpl'] = $modx->getOption('limitTpl', $scriptProperties);
$scriptProperties['limitItemTpl'] = $modx->getOption('limitItemTpl', $scriptProperties);
$scriptProperties['paginationWrapper'] = $modx->getOption('paginationWrapper', $scriptProperties);
$scriptProperties['paginationTpl'] = $modx->getOption('paginationTpl', $scriptProperties);

// Load lexicons
$modx->getService('lexicon', 'modLexicon');
$modx->lexicon->load($scriptProperties['lexicon']);

$category = $vc->getCategory(end($vc->router['categories']), array(
	'whereColumn' => 'alias'
));
$category->set('url', $vc->makeUrl(array(
	'categoryId' => $category->get('id'),
	'scheme' => $scriptProperties['scheme']
)));

// Parse the columns and translate where needed
$columns = explode(',', $scriptProperties['columns']);
$searchable = explode(',', $scriptProperties['searchable']);
$linkable = explode(',', $scriptProperties['linkable']);

foreach($columns as $key => $column) {
	$column = explode('==', $column);
	
	if ($scriptProperties['columnLexicon'] == true) {
		$column[0] = $modx->lexicon($column[0]);
	}
	
	$indexes[] = $column[1];
	$columns[$key] = array(
		'dataIndex' => $column[1],
		'translation' => $column[0]
	);
}

// Parse query string for relevant data
if (isset($vc->router['query'])) {
	$scriptProperties['sort'] = (string) strtoupper($modx->getOption('sort', $vc->router['query'], $scriptProperties['sort']));
	// Prevent sorting on anything but ASC and DESC
	if (!in_array($scriptProperties['sort'], array('ASC', 'DESC'))) {
		$scriptProperties['sort'] = 'ASC';
	}

	$scriptProperties['sortBy'] = (string) strtolower($modx->getOption('index', $vc->router['query'], $scriptProperties['sortBy']));
	// Prevent sorting on unknown columns
	if (!in_array($scriptProperties['sortBy'], $indexes)) {
		$scriptProperties['sortBy'] = 'name';
	}
	
	$scriptProperties['limit'] = $modx->getOption('limit', $vc->router['query'], $scriptProperties['limit']);
	// Prevent limit being a string or anything larger than specified
	if (!is_numeric($scriptProperties['limit'])) {
		$scriptProperties['limit'] = $defaults['limit'];
	}
	
	$scriptProperties['offset'] = $modx->getOption('offset', $vc->router['query'], $scriptProperties['offset']);
	// Prevent limit being a string or anything larger than specified
	if (!is_numeric($scriptProperties['offset'])) {
		$scriptProperties['offset'] = $defaults['offset'];
	}
}

$params = array(
	'asArray' => true,
	'sort' => $scriptProperties['sort'],
	'sortBy' => ($scriptProperties['sortBy'] == 'sort') ? 'vcProductCategory.'.$scriptProperties['sortBy'] : 'vcProduct.'.$scriptProperties['sortBy'],
	'limit' => $scriptProperties['limit'],
	'offset' => $scriptProperties['offset']
);

if (isset($vc->router['query']['query']) && $vc->router['query']['query'] != '') {
	$conditions = array();
	
	$inc = 0;
	$searchable = explode(',', $scriptProperties['searchable']);		
	foreach($searchable as $column) {	
		$conditions = array_merge($conditions, array(
			(($inc == 0) ? 'AND:' : 'OR:').'vcProduct.'.$column.':LIKE' => '%'.$vc->router['query']['query'].'%'
		));
		
		$inc++;
	}
	
	$params = array_merge($params, array('queryAnd' => $conditions));
}

$products = $vc->getProducts($scriptProperties['id'], $params);

$pagination['items'] = $products['total'];
$pagination['pages'] = ceil($pagination['items'] / $scriptProperties['limit']);
$products = $products['data'];

if (empty($products)) {
	return 'There are no products in this category';
}

$product = reset($products);
foreach($columns as $column) {
	if (isset($product[$column['dataIndex']])) {
		$output['headers'] .= $vc->parseChunk($scriptProperties['headerTplColumn'], array(
			'name' => $column['translation'],
			'url' => $category->get('url').$vc->buildQueryString($vc->router['query'], array(
				'strip' => array('q'),
				'overwrite' => array(
					'index' => $column['dataIndex'],
					'sort' => ($vc->router['query']['index'] == $column['dataIndex'] && $vc->router['query']['sort'] == 'asc') ? 'desc' : 'asc'
				)
			)),
			'sort' => ($vc->router['query']['index'] == $column['dataIndex'] && $vc->router['query']['sort'] == 'asc') ? 'desc' : 'asc'
		));
	}
}

foreach($products as $product) {
	foreach($columns as $column) {
		if (isset($product[$column['dataIndex']])) {
			if (in_array($column['dataIndex'], $linkable)) {
				$output['tmp'] .= $vc->parseChunk($scriptProperties['rowLinkTplColumn'], array(
					'field' => $product[$column['dataIndex']],
					'url' => $vc->makeUrl(array(
						'productCategory' => $product['linkId'],
						'shopId' => $product['shopid'],
						'scheme' => $scriptProperties['scheme']
					))
				));
			} else {
				$output['tmp'] .= $vc->parseChunk($scriptProperties['rowTplColumn'], array(
					'field' => $product[$column['dataIndex']]
				));
			}
		}
	}
	
	// Assign the parsed columns to the row template and reset the tmp
	$output['content'] .= $vc->parseChunk($scriptProperties['rowTpl'], array(
		'columns' => $output['tmp']
	));
	$output['tmp'] = '';
}


foreach(array(1, 2, 5, 10, 25, 50, 100) as $limit) {
	$output['tmp'] .= $vc->parseChunk($scriptProperties['limitItemTpl'], array(
		'value' => $limit,
		'selected' => ($scriptProperties['limit'] == $limit) ? 'selected' : ''
	));
}

$output['footer']['limit'] = $vc->parseChunk($scriptProperties['limitTpl'], array(
	'options' => $output['tmp']
));

$output['footer']['pagination'] = '';
for($page = 1; $page <= $pagination['pages']; $page++) {
	$output['footer']['pagination'] .= $vc->parseChunk($scriptProperties['paginationTpl'], array(
		'page' => $page,
		'url' => $category->get('url').$vc->buildQueryString($vc->router['query'], array(
			'strip' => array('q'),
			'overwrite' => array(
				'offset' => ($page - 1) * $scriptProperties['limit'],
			)
		))
	));
}

$output['footer']['pagination'] = $vc->parseChunk($scriptProperties['paginationWrapper'], array(
	'pages' => $output['footer']['pagination']
));

$output['footer'] = $vc->parseChunk($scriptProperties['footerTpl'], array(
	'limitbox' => $output['footer']['limit'],
	'pagination' => $output['footer']['pagination']
));

$output['master'] .= $vc->parseChunk($scriptProperties['wrapperTpl'], array(
	'headers' => $output['headers'],
	'content' => $output['content'],
	'footer' => $output['footer']
));

// Set template placeholders for configuration in the template
$modx->toPlaceholders(array(
	'vc' => array(
		'list' => array(
			'config' => array(
				'columns' => (int) count($columns)
			),
			'pagination' => $pagination,
			'router' => $vc->router
		)
	)
));

return $output['master'];
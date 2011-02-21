<?php

$mainCategory = $modx->getObject('modCategory', array(
	'category' => 'VisionCart'
));

$attributes = array(
    xPDOTransport::UNIQUE_KEY => 'category',
    xPDOTransport::PRESERVE_KEYS => false,
    xPDOTransport::UPDATE_OBJECT => true,
    xPDOTransport::RELATED_OBJECTS => true,
    xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array (
    	'Children' => array(
			xPDOTransport::PRESERVE_KEYS => false,
			xPDOTransport::UPDATE_OBJECT => true,
			xPDOTransport::UNIQUE_KEY => 'category',
    	),
		'Snippets' => array(
			xPDOTransport::PRESERVE_KEYS => false,
			xPDOTransport::UPDATE_OBJECT => true,
			xPDOTransport::UNIQUE_KEY => 'name',
		),
		'Chunks' => array (
			xPDOTransport::PRESERVE_KEYS => false,
			xPDOTransport::UPDATE_OBJECT => true,
			xPDOTransport::UNIQUE_KEY => 'name',
		)
    )
);

// Get all subcategories
$subCategories = $mainCategory->getMany('Children');
foreach($subCategories as $category) {
	$snippets = $category->getMany('Snippets');
	$chunks = $category->getMany('Chunks');
	
	$category->addMany($snippets);
	$category->addMany($chunks);
	$mainCategory->addMany($category);
	unset($snippets, $chunks);
}

$vehicle = $builder->createVehicle($mainCategory, $attributes);
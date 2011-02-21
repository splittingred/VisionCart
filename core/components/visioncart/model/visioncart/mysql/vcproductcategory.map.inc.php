<?php
$xpdo_meta_map['vcProductCategory']= array (
  'package' => 'visioncart',
  'table' => 'visioncart_products_categories',
  'fields' => 
  array (
    'shopid' => 0,
    'categoryid' => 0,
    'productid' => 0,
    'sort' => 0,
  ),
  'fieldMeta' => 
  array (
    'shopid' => 
    array (
      'dbtype' => 'int',
      'phptype' => 'integer',
      'precision' => '8',
      'null' => false,
      'default' => 0,
    ),
    'categoryid' => 
    array (
      'dbtype' => 'int',
      'phptype' => 'integer',
      'precision' => '8',
      'null' => false,
      'default' => 0,
      'index' => 'index',
    ),
    'productid' => 
    array (
      'dbtype' => 'int',
      'phptype' => 'integer',
      'precision' => '8',
      'null' => false,
      'default' => 0,
      'index' => 'index',
    ),
    'sort' => 
    array (
      'dbtype' => 'int',
      'phptype' => 'integer',
      'precision' => '8',
      'null' => false,
      'default' => 0,
    ),
  ),
  'aggregates' => 
  array (
    'Product' => 
    array (
      'class' => 'vcProduct',
      'local' => 'productid',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
    'Shop' => 
    array (
      'class' => 'vcShop',
      'local' => 'shopid',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
    'Category' => 
    array (
      'class' => 'vcCategory',
      'local' => 'categoryid',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
  ),
);

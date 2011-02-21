<?php
$xpdo_meta_map['vcCategory']= array (
  'package' => 'visioncart',
  'table' => 'visioncart_categories',
  'fields' => 
  array (
    'shopid' => 0,
    'name' => '',
    'alias' => '',
    'description' => '',
    'parent' => 0,
    'sort' => 0,
    'config' => '',
    'customfields' => '',
    'pricechange' => 0,
    'pricepercent' => 0,
    'active' => 0,
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
    'name' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '50',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'alias' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '50',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
      'index' => 'index',
    ),
    'description' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'parent' => 
    array (
      'dbtype' => 'int',
      'phptype' => 'integer',
      'precision' => '8',
      'null' => false,
      'default' => 0,
    ),
    'sort' => 
    array (
      'dbtype' => 'int',
      'phptype' => 'integer',
      'precision' => '8',
      'null' => false,
      'default' => 0,
    ),
    'config' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'json',
      'null' => false,
      'default' => '',
    ),
    'customfields' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'json',
      'null' => false,
      'default' => '',
    ),
    'pricechange' => 
    array (
      'dbtype' => 'decimal',
      'precision' => '8,2',
      'phptype' => 'float',
      'null' => false,
      'default' => 0,
    ),
    'pricepercent' => 
    array (
      'dbtype' => 'tinyint',
      'precision' => '1',
      'attributes' => 'unsigned',
      'phptype' => 'boolean',
      'null' => false,
      'default' => 0,
    ),
    'active' => 
    array (
      'dbtype' => 'tinyint',
      'precision' => '1',
      'attributes' => 'unsigned',
      'phptype' => 'boolean',
      'null' => false,
      'default' => 0,
    ),
  ),
  'composites' => 
  array (
    'ProductCategory' => 
    array (
      'class' => 'vcProductCategory',
      'local' => 'id',
      'foreign' => 'categoryid',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
  ),
  'aggregates' => 
  array (
    'Shop' => 
    array (
      'class' => 'vcShop',
      'local' => 'shopid',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
  ),
);

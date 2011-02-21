<?php
$xpdo_meta_map['vcProduct']= array (
  'package' => 'visioncart',
  'table' => 'visioncart_products',
  'fields' => 
  array (
    'shopid' => 0,
    'parent' => 0,
    'taxcategory' => 0,
    'name' => '',
    'alias' => '',
    'description' => '',
    'articlenumber' => '',
    'price' => 0,
    'weight' => 0,
    'shippingprice' => 0,
    'publishdate' => 0,
    'unpublishdate' => 0,
    'customfields' => '',
    'pictures' => '',
    'stock' => 0,
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
    'parent' => 
    array (
      'dbtype' => 'int',
      'phptype' => 'integer',
      'precision' => '8',
      'null' => false,
      'default' => 0,
    ),
    'taxcategory' => 
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
      'precision' => '100',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'alias' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '100',
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
    'articlenumber' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '100',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
      'index' => 'index',
    ),
    'price' => 
    array (
      'dbtype' => 'decimal',
      'precision' => '8,2',
      'phptype' => 'float',
      'null' => false,
      'default' => 0,
      'index' => 'index',
    ),
    'weight' => 
    array (
      'dbtype' => 'decimal',
      'precision' => '8,2',
      'phptype' => 'float',
      'null' => false,
      'default' => 0,
    ),
    'shippingprice' => 
    array (
      'dbtype' => 'decimal',
      'precision' => '8,2',
      'phptype' => 'float',
      'null' => false,
      'default' => 0,
    ),
    'publishdate' => 
    array (
      'dbtype' => 'int',
      'precision' => '20',
      'phptype' => 'timestamp',
      'null' => false,
      'default' => 0,
      'index' => 'index',
    ),
    'unpublishdate' => 
    array (
      'dbtype' => 'int',
      'precision' => '20',
      'phptype' => 'timestamp',
      'null' => false,
      'default' => 0,
      'index' => 'index',
    ),
    'customfields' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'json',
      'null' => false,
      'default' => '',
    ),
    'pictures' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'json',
      'null' => false,
      'default' => '',
    ),
    'stock' => 
    array (
      'dbtype' => 'int',
      'phptype' => 'integer',
      'precision' => '8',
      'null' => false,
      'default' => 0,
    ),
    'active' => 
    array (
      'dbtype' => 'tinyint',
      'precision' => '1',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => false,
      'default' => 0,
    ),
  ),
  'composites' => 
  array (
    'ProductOption' => 
    array (
      'class' => 'vcProductOption',
      'local' => 'id',
      'foreign' => 'productid',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
    'ProductCategory' => 
    array (
      'class' => 'vcProductCategory',
      'local' => 'id',
      'foreign' => 'productid',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
  ),
  'aggregates' => 
  array (
    'TaxCategory' => 
    array (
      'class' => 'vcCategory',
      'local' => 'taxcategory',
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
  ),
);

<?php
$xpdo_meta_map['vcShop']= array (
  'package' => 'visioncart',
  'table' => 'visioncart_shops',
  'fields' => 
  array (
    'name' => '',
    'alias' => '',
    'description' => '',
    'config' => '',
    'context' => '',
    'active' => 0,
  ),
  'fieldMeta' => 
  array (
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
    'config' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'json',
      'null' => false,
      'default' => '',
    ),
    'context' => 
    array (
      'dbtype' => 'varchar',
      'phptype' => 'string',
      'precision' => '100',
      'null' => false,
      'default' => '',
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
    'Category' => 
    array (
      'class' => 'vcCategory',
      'local' => 'id',
      'foreign' => 'shopid',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
    'Product' => 
    array (
      'class' => 'vcProduct',
      'local' => 'id',
      'foreign' => 'shopid',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
    'Option' => 
    array (
      'class' => 'vcOption',
      'local' => 'id',
      'foreign' => 'shopid',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
    'OptionValue' => 
    array (
      'class' => 'vcOptionValue',
      'local' => 'id',
      'foreign' => 'shopid',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
    'Order' => 
    array (
      'class' => 'vcOrder',
      'local' => 'id',
      'foreign' => 'shopid',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
  ),
);

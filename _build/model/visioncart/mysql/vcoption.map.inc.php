<?php
$xpdo_meta_map['vcOption']= array (
  'package' => 'visioncart',
  'table' => 'visioncart_options',
  'fields' => 
  array (
    'shopid' => 0,
    'name' => '',
    'inputsnippet' => '',
    'outputsnippet' => '',
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
      'precision' => '100',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
      'index' => 'index',
    ),
    'inputsnippet' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '50',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'outputsnippet' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '50',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
  ),
  'composites' => 
  array (
    'OptionValue' => 
    array (
      'class' => 'vcOptionValue',
      'local' => 'id',
      'foreign' => 'optionid',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
    'ProductOption' => 
    array (
      'class' => 'vcProductOption',
      'local' => 'id',
      'foreign' => 'optionid',
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

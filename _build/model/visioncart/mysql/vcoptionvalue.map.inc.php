<?php
$xpdo_meta_map['vcOptionValue']= array (
  'package' => 'visioncart',
  'table' => 'visioncart_options_values',
  'fields' => 
  array (
    'shopid' => 0,
    'optionid' => 0,
    'value' => '',
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
    'optionid' => 
    array (
      'dbtype' => 'int',
      'phptype' => 'integer',
      'precision' => '8',
      'null' => false,
      'default' => 0,
      'index' => 'index',
    ),
    'value' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
  ),
  'composites' => 
  array (
    'Product' => 
    array (
      'class' => 'vcProductOption',
      'local' => 'id',
      'foreign' => 'valueid',
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
    'Option' => 
    array (
      'class' => 'vcOption',
      'local' => 'optionid',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
  ),
);

<?php
$xpdo_meta_map['vcProductOption']= array (
  'package' => 'visioncart',
  'table' => 'visioncart_products_options',
  'fields' => 
  array (
    'productid' => 0,
    'optionid' => 0,
    'valueid' => 0,
    'sort' => 0,
  ),
  'fieldMeta' => 
  array (
    'productid' => 
    array (
      'dbtype' => 'int',
      'phptype' => 'integer',
      'precision' => '10',
      'null' => false,
      'default' => 0,
      'index' => 'index',
    ),
    'optionid' => 
    array (
      'dbtype' => 'int',
      'phptype' => 'integer',
      'precision' => '10',
      'null' => false,
      'default' => 0,
      'index' => 'index',
    ),
    'valueid' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'phptype' => 'integer',
      'null' => false,
      'default' => 0,
    ),
    'sort' => 
    array (
      'dbtype' => 'int',
      'precision' => '2',
      'phptype' => 'integer',
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
    'Option' => 
    array (
      'class' => 'vcOption',
      'local' => 'optionid',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
    'OptionValue' => 
    array (
      'class' => 'vcOptionValue',
      'local' => 'valueid',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
  ),
);

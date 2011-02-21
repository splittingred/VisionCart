<?php
$xpdo_meta_map['vcOrder']= array (
  'package' => 'visioncart',
  'table' => 'visioncart_orders',
  'fields' => 
  array (
    'shopid' => 0,
    'userid' => 0,
    'userdata' => '',
    'ordernumber' => '',
    'basket' => '',
    'basketid' => '',
    'extradata' => '',
    'shippingid' => 0,
    'shippingcostsex' => 0,
    'shippingcostsin' => 0,
    'shippingdata' => '',
    'paymentid' => 0,
    'paymentcostsex' => 0,
    'paymentcostsin' => 0,
    'paymentdata' => '',
    'totalweight' => 0,
    'totalproductamountex' => 0,
    'totalproductamountin' => 0,
    'totalorderamountin' => 0,
    'totalorderamountex' => 0,
    'paidamount' => 0,
    'ordertime' => 0,
    'updatedby' => 0,
    'updatetime' => 0,
    'status' => 0,
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
    'userid' => 
    array (
      'dbtype' => 'int',
      'phptype' => 'integer',
      'precision' => '8',
      'null' => false,
      'default' => 0,
    ),
    'userdata' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'json',
      'null' => false,
      'default' => '',
    ),
    'ordernumber' => 
    array (
      'dbtype' => 'varchar',
      'phptype' => 'string',
      'precision' => '30',
      'null' => false,
      'default' => '',
    ),
    'basket' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'json',
      'null' => false,
      'default' => '',
    ),
    'basketid' => 
    array (
      'dbtype' => 'varchar',
      'phptype' => 'string',
      'precision' => '40',
      'null' => false,
      'default' => '',
    ),
    'extradata' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'json',
      'null' => false,
      'default' => '',
    ),
    'shippingid' => 
    array (
      'dbtype' => 'int',
      'phptype' => 'integer',
      'precision' => '8',
      'null' => false,
      'default' => 0,
    ),
    'shippingcostsex' => 
    array (
      'dbtype' => 'decimal',
      'precision' => '8,2',
      'phptype' => 'float',
      'null' => false,
      'default' => 0,
    ),
    'shippingcostsin' => 
    array (
      'dbtype' => 'decimal',
      'precision' => '8,2',
      'phptype' => 'float',
      'null' => false,
      'default' => 0,
    ),
    'shippingdata' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'json',
      'null' => false,
      'default' => '',
    ),
    'paymentid' => 
    array (
      'dbtype' => 'int',
      'phptype' => 'integer',
      'precision' => '8',
      'null' => false,
      'default' => 0,
    ),
    'paymentcostsex' => 
    array (
      'dbtype' => 'decimal',
      'precision' => '8,2',
      'phptype' => 'float',
      'null' => false,
      'default' => 0,
    ),
    'paymentcostsin' => 
    array (
      'dbtype' => 'decimal',
      'precision' => '8,2',
      'phptype' => 'float',
      'null' => false,
      'default' => 0,
    ),
    'paymentdata' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'json',
      'null' => false,
      'default' => '',
    ),
    'totalweight' => 
    array (
      'dbtype' => 'decimal',
      'precision' => '8,2',
      'phptype' => 'float',
      'null' => false,
      'default' => 0,
    ),
    'totalproductamountex' => 
    array (
      'dbtype' => 'decimal',
      'precision' => '8,2',
      'phptype' => 'float',
      'null' => false,
      'default' => 0,
    ),
    'totalproductamountin' => 
    array (
      'dbtype' => 'decimal',
      'precision' => '8,2',
      'phptype' => 'float',
      'null' => false,
      'default' => 0,
    ),
    'totalorderamountin' => 
    array (
      'dbtype' => 'decimal',
      'precision' => '8,2',
      'phptype' => 'float',
      'null' => false,
      'default' => 0,
    ),
    'totalorderamountex' => 
    array (
      'dbtype' => 'decimal',
      'precision' => '8,2',
      'phptype' => 'float',
      'null' => false,
      'default' => 0,
    ),
    'paidamount' => 
    array (
      'dbtype' => 'decimal',
      'precision' => '8,2',
      'phptype' => 'float',
      'null' => false,
      'default' => 0,
    ),
    'ordertime' => 
    array (
      'dbtype' => 'int',
      'precision' => '20',
      'phptype' => 'timestamp',
      'null' => false,
      'default' => 0,
      'index' => 'index',
    ),
    'updatedby' => 
    array (
      'dbtype' => 'int',
      'phptype' => 'integer',
      'precision' => '8',
      'null' => false,
      'default' => 0,
    ),
    'updatetime' => 
    array (
      'dbtype' => 'int',
      'precision' => '20',
      'phptype' => 'timestamp',
      'null' => false,
      'default' => 0,
      'index' => 'index',
    ),
    'status' => 
    array (
      'dbtype' => 'int',
      'phptype' => 'integer',
      'precision' => '1',
      'null' => false,
      'default' => 0,
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
    'PaymentModule' => 
    array (
      'class' => 'vcModule',
      'local' => 'paymentid',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
    'ShippingModule' => 
    array (
      'class' => 'vcModule',
      'local' => 'shippingid',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
  ),
);

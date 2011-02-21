<?php

if (!$modx->user->isAuthenticated('mgr')) return $modx->error->failure($modx->lexicon('permission_denied'));

$status = (int) $_REQUEST['status'];
$shopId = (int) $_REQUEST['shopId'];

$query = $modx->newQuery('vcOrder', array(
	'status' => $status,
	'shopid' => $shopId
));

$allOrders = $modx->getCollection('vcOrder', $query);

$_REQUEST['limit'] = $modx->getOption('limit', $_REQUEST, 25);
$_REQUEST['start'] = $modx->getOption('start', $_REQUEST, 0);
$_REQUEST['orderby'] = $modx->getOption('sort', $_REQUEST, 'ordertime');
$_REQUEST['orderdir'] = $modx->getOption('dir', $_REQUEST, 'DESC');
$_REQUEST['search'] = $modx->getOption('search', $_REQUEST, '');

$query->limit($_REQUEST['limit'], $_REQUEST['start']);
$query->sortby($_REQUEST['orderby'], $_REQUEST['orderdir']);

if ($_REQUEST['search'] != '') {
	$query->where(array('ordernumber:LIKE' => '%'.$_REQUEST['search'].'%'));
	$query->orCondition(array('userdata:LIKE' => '%fullname":"'.$_REQUEST['search'].'%'));
	$query->orCondition(array('userdata:LIKE' => '%address":"'.$_REQUEST['search'].'%'));
	$query->orCondition(array('userdata:LIKE' => '%zip":"'.$_REQUEST['search'].'%'));
	$query->orCondition(array('userdata:LIKE' => '%city":"'.$_REQUEST['search'].'%'));
	$query->orCondition(array('userdata:LIKE' => '%state":"'.$_REQUEST['search'].'%'));
	$query->orCondition(array('userdata:LIKE' => '%country":"'.$_REQUEST['search'].'%'));
}

$orders = $modx->getCollection('vcOrder', $query);
        
$list = array();
foreach ($orders as $order) {
	$orderArray = $order->toArray();
	$orderArray['email'] = $orderArray['userdata']['profile']['email'];
	$orderArray['fullname'] = $orderArray['userdata']['profile']['fullname'];

	$list[] = $orderArray;
}

return $this->outputArray($list, sizeof($allOrders));
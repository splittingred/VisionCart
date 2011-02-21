<?php

//get order id
$id = $_REQUEST['id'];

//get object from database
$order = $modx->getObject('vcOrder', $id);
$modx->visioncart->orderToPdf($order);

exit();
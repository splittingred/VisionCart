<?php
$option = $modx->getOption('option', $scriptProperties, array());

return $option['name'].'<br /><input type="text" name="vc_option_'.$option['name'].'" value="" />';
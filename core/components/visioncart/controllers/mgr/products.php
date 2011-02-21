<?php

$shop = $modx->getObject('vcShop', (int) $_REQUEST['shopid']);
$modx->regClientStartupHTMLBlock('<script type="text/javascript">
vcCore.config.shop = '.json_encode($shop->toArray()).';
</script>');

return '<div id="visioncart-container"></div><div id="vc-ajax-haze" class="vc-ajax-haze"></div>';
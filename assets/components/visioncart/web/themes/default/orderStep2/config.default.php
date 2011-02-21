<?php

$params = array();

$params['vcOrderStep2'] = '@CODE:<h2>Address</h2>
[[$vcOrderAddressForm]]
<hr />
<form action="[[+nextStep]]" method="post">
	<input type="submit" value="Previous" onclick="window.location=\'[[+previousStep]]\';"/>
	<input type="submit" value="Next" />
</form>'; 
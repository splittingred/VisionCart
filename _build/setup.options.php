<?php

switch ($options[xPDOTransport::PACKAGE_ACTION]) {
    case xPDOTransport::ACTION_INSTALL:
    case xPDOTransport::ACTION_UPGRADE:
		$output = 'Is it okay that we receive an email once you have completed the beta setup that contains the domain name you are installing this VisionCart on?<br /><br />
		This email will be sent once upon install and will never be released to the public. It is only used for our personal statistics! :-)';
		$output .= '<br /><br /><input type="radio" value="1" name="send_email" checked="checked" /> Yes <input type="radio" value="0" name="send_email" /> No';
		break;
}

return $output;
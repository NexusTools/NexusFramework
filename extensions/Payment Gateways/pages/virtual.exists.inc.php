<?php

$providerName = PageModule::getArgument(1);
//throw new Exception(json_encode(PaymentGateway::getGateways()));
$provider = PaymentGateway::getGateway($providerName);
if ($provider)
	$provider->handleCallback(relativepath(substr(REQUEST_URI, 27 + strlen($providerName))));

return false;
?>

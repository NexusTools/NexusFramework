<?php
$gateway = isset($_GET['gateway']) ? $_GET['gateway'] : false;
$breadedCrumbs = Array(false, Array("title" => "Payment Gateways", "action" => "ControlPanel.loadPopup('Shopping Cart', 'Payment Gateways')"));
if (!$gateway || !PaymentCore::isGatewayValid($gateway)) {
	echo "<h2>Invalid Gateway Requested</h2>The gateway requested does not exist.";
	array_push($breadedCrumbs, "Invalid Gateway");
} else {
	array_push($breadedCrumbs, $gateway);
}

return $breadedCrumbs;
?>

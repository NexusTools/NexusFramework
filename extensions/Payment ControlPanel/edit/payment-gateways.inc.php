<center><h2>Select a Gateway to Configure</h2>
<?php
foreach (PaymentCore::getGatewayNames() as $gateway) {
	echo "<img alt='$gateway' title='$gateway' class='item' src='";
	echo Framework::getReferenceURI(PaymentCore::getLogoForGateway($gateway));
	echo "' onclick=\"ControlPanel.loadPage('Shopping Cart', 'Configure Gateway', {gateway: '$gateway'})\" />";
}
?></center>

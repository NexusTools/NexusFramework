<?php
ControlPanel::registerPage("Shopping Cart", "Inventory", "edit/inventory.json", true, 0);
ControlPanel::registerPage("Shopping Cart", "Categories", "edit/categories.json", true, 0);

ControlPanel::registerPage("Shopping Cart", "Coupons", "edit/coupons.json", true, 1);
ControlPanel::registerPage("Shopping Cart", "Invoices", "edit/invoices.json", true, 1);
ControlPanel::registerPage("Shopping Cart", "Invoice Products", "edit/invoice-products.json", false);
ControlPanel::registerPage("Shopping Cart", "Tax Rates", "edit/tax-rates.inc.php", true);

ControlPanel::registerPage("Shopping Cart", "Payment Gateways", "edit/payment-gateways.inc.php", true, 2, 1, true);
ControlPanel::registerPage("Shopping Cart", "Configure Gateway", "edit/configure-gateway.inc.php", false);

ControlPanel::registerPage("Shopping Cart", "Create Category", "edit/create-category.json", false);
ControlPanel::registerPage("Shopping Cart", "Edit Category", "edit/edit-category.json", false);

ControlPanel::registerPage("Shopping Cart", "Create Inventory", "edit/create-inventory.json", false);
ControlPanel::registerPage("Shopping Cart", "Edit Inventory", "edit/edit-inventory.json", false);

function __paymentCore__ControlPanelWatcher($module, $event, $arguments) {
	switch ($event) {
	case "Database Query Filter":
		if ($arguments[0] == "Shopping Cart" && $arguments[1] == "Invoice Products")
			return Array("invoice" => $_GET['id']);

	}
}

Triggers::watchModule("ControlPanel", "__paymentCore__ControlPanelWatcher");
?>

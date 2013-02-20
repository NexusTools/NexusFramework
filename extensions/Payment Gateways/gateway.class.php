<?php
abstract class PaymentGateway {

	public static function getGateways(){
	}
	
	public static function getGateway($id){
	}
	
	public static function registerGateway($name, $impl){
		
	}
	
	
	public abstract function getLogo();
	public abstract function startCheckout($);
	public abstract function confirmCheckoutPayment($checkoutID);
	
	public abstract function handleCallback();

}
?>

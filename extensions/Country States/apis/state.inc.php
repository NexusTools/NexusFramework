<?php
$country = API::getCurrentArugment();
if($country)
	return CountryStates::getStates($country);
else
	return CountryStates::getCountries();
?>

<?php
$country = API::getCurrentArugment();
if ($country)
	return CountryData::listStatesForCountry($country);
else
	return CountryData::listCountries();
?>

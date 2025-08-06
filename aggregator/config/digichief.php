<?php
/*
 * 
 * Custom Configuration File for Digichief Endpoints
 * 
 */
return [
	'xmlAllNewsEndpoint' => 'https://api.digichief.com/newsv2/getNews/Spectrio/bcb5e2fe-e898-4e98-9cc3-34900dc2a8b7?category=all&format=xml',
	'xmlThisDateHistoryEndpoint' => 'https://api.digichief.com/infotainment/getinfotainment/Spectrio/bcb5e2fe-e898-4e98-9cc3-34900dc2a8b7?category=TDIH&qty=r2&format=xml',
	'xmlBornDateEndpoint' => 'https://api.digichief.com/infotainment/getinfotainment/Spectrio/bcb5e2fe-e898-4e98-9cc3-34900dc2a8b7?category=botd&qty=r2&format=xml',
	
	'xmlOlympicsEndpoint' => 'https://api.digichief.com/sports-olympics/getmedal/Spectrio/bcb5e2fe-e898-4e98-9cc3-34900dc2a8b7?abbr=USA,CAN&format=xml',
	'xmlWeatherEndpoint' => 'https://api.digichief.com/weather/getweather/Spectrio/bcb5e2fe-e898-4e98-9cc3-34900dc2a8b7?format=xml&zipcode=40509',
	'bannedNewsCategories' => array('War','Disaster','Religion'),
];

?>
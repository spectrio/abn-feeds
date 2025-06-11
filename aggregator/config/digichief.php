<?php
/*
 * 
 * Custom Configuration File for Digichief Endpoints
 * 
 */
return [
	'xmlAllNewsEndpoint' => 'https://api.digichief.com/newsv2/getNews/Spectrio/bcb5e2fe-e898-4e98-9cc3-34900dc2a8b7?category=all&format=xml',
	'xmlThisDateHistoryEndpoint' => 'http://silo.digichief.com/ABN-Live-2-1-2012/Almanac/XML/ThisDate.xml',
	'xmlBornDateEndpoint' => 'http://silo.digichief.com/ABN-Live-2-1-2012/Almanac/XML/BornDate.xml',
	
	'xmlOlympicsEndpoint' => 'http://silo.digichief.com/abn-live-2-1-2012/Sportstats/XML/Olympics-Medal-Count-Medals.XML',
	'xmlWeatherEndpoint' => 'http://silo.digichief.com/ABN-Live-2-1-2012/Weather/ZipCode/XML/',
	'bannedNewsCategories' => array('War','Disaster','Religion'),
];

?>
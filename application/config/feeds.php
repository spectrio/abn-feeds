<?php 
	$config['assetServer']  = 'http://feeds.abnetwork.com/';
	$config['assetDirectory'] = 'ASSETS/';
	$config['assetThemeDirectory'] = '/content/DEALER_TV_CONTENT/DTV_ASSETS/THEMES/'; //ASSETS/global/themes';
	$config['assetNetworksDirectory'] = 'ASSETS/networks/';
	$config['assetDataDirectory'] = 'http://feeds.abnetwork.com/ASSETS/data/';
	$config['assetWeatherIconDirectory'] = '/content/DEALER_TV_CONTENT/DTV_ASSETS/WEATHER_ICONS/';  //D:\\Infocasts\\ASSETS\\ASSETS\\WEATHER_ICONS\\';
	
	$config['assetLocalThemeDirectory'] = '/content/DEALER_TV_CONTENT/DTV_ASSETS/THEMES/'; //'D:\\Infocasts\\ASSETS\\ASSETS\\THEME_ASSETS\\';
	$config['assetLocalIconDirectory'] = '/content/DEALER_TV_CONTENT/DTV_ASSETS/NEWS_ICONS/'; //D:\\Infocasts\\ASSETS\\ASSETS\\ICON_ASSETS\\';
	$config['assetLocalMenuboardDirectory'] = 'D:\\Infocasts\\MB_ASSETS\\MB_ASSETS\\';
		
	$config['assetLocalDirectory'] = '/content/'; //'D:\\Infocasts\\';
	
	// Digichief RSS
	//$config['digichief_endpoint'] = 'http://silo.digichief.com/ABN-Live-2-1-2012/NEWS/XML/AllStoriesNoSchema.xml';
	//$config['digichief_endpoint'] = 'http://silo.digichief.com/ABN-Live-2-1-2012/NewsII/XML/AllStories.xml';
	$config['digichief_endpoint'] = '/var/www/feeds/digicache/AllStories.xml';
	//$config['digichief_endpoint'] = 'http://localhost/digicache/AllStoriesNoSchema.xml';
	
	//$config['digichief_thisdate'] = 'http://silo.digichief.com/ABN-Live-2-1-2012/Almanac/XML/ThisDate.xml';
	$config['digichief_thisdate'] = '/var/www/feeds/digicache/ThisDate.xml';
	//$config['digichief_borndate'] = 'http://silo.digichief.com/ABN-Live-2-1-2012/Almanac/XML/BornDate.xml';
	$config['digichief_borndate'] = '/var/www/feeds/digicache/BornDate.xml';
	
	$config['digichief_olympics_endpoint'] = 'http://silo.digichief.com/abn-live-2-1-2012/Sportstats/XML/Olympics-Medal-Count-Medals.XML';
	$config['banned_categories'] = array('War','Disaster','Religion');
	// Digichief Weather
	$config['weather_endpoint'] = '/var/www/feeds/digicache/';
	//$config['weather_endpoint'] = 'http://silo.digichief.com/ABN-Live-2-1-2012/Weather/ZipCode/XML/';
	$config['test_weather_endpoint'] = 'http://feeds.abnetwork.com/ASSETS/data/weather/';
	
	$config['digichief_nfl_json_endpoint'] = '/var/www/feeds/digicache/NFL.json';
	$config['digichief_nfl_xml_endpoint'] = '/var/www/feeds/digicache/NFL.xml';
	
	$config['rss_icons'] = array('topnews','arts','business','education','entertainment','health','humaninterest','international','labor','lifestyle','mlb','nba','nfl','nhl','politics','quirky','religion','science','social','sports','usnews','weather');
	$config['rss_default_icon'] = 'default';
	
?>

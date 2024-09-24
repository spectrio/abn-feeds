<?php 
	$config['assetServer']  = getenv('HTTP') . getenv('FEEDS_ABNETWORK');
	$config['assetDirectory'] = 'ASSETS/';
	$config['assetThemeDirectory'] = '/content/DEALER_TV_CONTENT/DTV_ASSETS/THEMES/'; //ASSETS/global/themes';
	$config['assetNetworksDirectory'] = 'ASSETS/networks/';
	$config['assetDataDirectory'] = getenv('HTTP') . getenv('FEEDS_ABNETWORK') . '/ASSETS/data/';
	$config['assetWeatherIconDirectory'] = '/content/DEALER_TV_CONTENT/DTV_ASSETS/WEATHER_ICONS/';  //D:\\Infocasts\\ASSETS\\ASSETS\\WEATHER_ICONS\\';
	
	$config['assetLocalThemeDirectory'] = '/content/DEALER_TV_CONTENT/DTV_ASSETS/THEMES/'; //'D:\\Infocasts\\ASSETS\\ASSETS\\THEME_ASSETS\\';
	$config['assetLocalIconDirectory'] = '/content/DEALER_TV_CONTENT/DTV_ASSETS/NEWS_ICONS/'; //D:\\Infocasts\\ASSETS\\ASSETS\\ICON_ASSETS\\';
	$config['assetLocalMenuboardDirectory'] = 'D:\\Infocasts\\MB_ASSETS\\MB_ASSETS\\';
		
	$config['assetLocalDirectory'] = '/content/'; //'D:\\Infocasts\\';
	
	// Digichief RSS
	//$config['digichief_endpoint'] = 'http://silo.digichief.com/ABN-Live-2-1-2012/NEWS/XML/AllStoriesNoSchema.xml';
	//$config['digichief_endpoint'] = 'http://silo.digichief.com/ABN-Live-2-1-2012/NewsII/XML/AllStories.xml';
	$config['digichief_endpoint'] = getenv('FEEDS_DIRECTORY_PATH') . '/digicache/AllStories.xml';
	//$config['digichief_endpoint'] = 'http://localhost/digicache/AllStoriesNoSchema.xml';
	
	//$config['digichief_thisdate'] = 'http://silo.digichief.com/ABN-Live-2-1-2012/Almanac/XML/ThisDate.xml';
	$config['digichief_thisdate'] = getenv('FEEDS_DIRECTORY_PATH') . '/digicache/ThisDate.xml';
	//$config['digichief_borndate'] = 'http://silo.digichief.com/ABN-Live-2-1-2012/Almanac/XML/BornDate.xml';
	$config['digichief_borndate'] = getenv('FEEDS_DIRECTORY_PATH') . '/digicache/BornDate.xml';
	
	$config['digichief_olympics_endpoint'] = 'http://silo.digichief.com/abn-live-2-1-2012/Sportstats/XML/Olympics-Medal-Count-Medals.XML';
	$config['banned_categories'] = array('War','Disaster','Religion');
	// Digichief Weather
	$config['weather_endpoint'] = getenv('FEEDS_DIRECTORY_PATH') . '/digicache/';
	//$config['weather_endpoint'] = 'http://silo.digichief.com/ABN-Live-2-1-2012/Weather/ZipCode/XML/';
	$config['test_weather_endpoint'] = getenv('HTTP') . getenv('FEEDS_ABNETWORK') . '/ASSETS/data/weather/';
	
	$config['digichief_nfl_json_endpoint'] = getenv('FEEDS_DIRECTORY_PATH') . '/digicache/NFL.json';
	$config['digichief_nfl_xml_endpoint'] = getenv('FEEDS_DIRECTORY_PATH') . '/digicache/NFL.xml';
	
	$config['rss_icons'] = array('topnews','arts','business','education','entertainment','health','humaninterest','international','labor','lifestyle','mlb','nba','nfl','nhl','politics','quirky','religion','science','social','sports','usnews','weather');
	$config['rss_default_icon'] = 'default';
	
?>

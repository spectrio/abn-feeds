<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @property CI_Config $config
 * @property CI_Loader $load
 * @property CI_Output $output
 * @property Remote_model $remote_model
 * @property string $assetServer
 * @property string $assetDirectory
 * @property string $assetThemeDirectory
 * @property string $assetNetworksDirectory
 * @property string $assetDataDirectory
 * @property string $assetWeatherIconDirectory
 * @property string $assetLocalThemeDirectory
 * @property string $assetLocalMenuboardDirectory
 * @property string $assetLocalIconDirectory
 * @property string $assetLocalDirectory
 */
class Feeds extends CI_Controller {
    
    public function __construct() {
        
        parent::__construct();
        
        // Custom Globals
        // See config/feeds for custom configs
        
        $this->assetServer = $this->config->item('assetServer');
        $this->assetDirectory = $this->config->item('assetDirectory');
        $this->assetThemeDirectory = $this->config->item('assetThemeDirectory');
        $this->assetNetworksDirectory = $this->config->item('assetNetworksDirectory');
        $this->assetDataDirectory = $this->config->item('assetDataDirectory');
        $this->assetWeatherIconDirectory = $this->config->item('assetWeatherIconDirectory');
        
        $this->assetLocalThemeDirectory = $this->config->item('assetLocalThemeDirectory');
        $this->assetLocalMenuboardDirectory = $this->config->item('assetLocalMenuboardDirectory');
        $this->assetLocalIconDirectory = $this->config->item('assetLocalIconDirectory');
        $this->assetLocalDirectory = $this->config->item('assetLocalDirectory');
        
        // All Output from this controller is XML
        
        //$this->output->set_header('Content-Type: text/xml; charset="utf-8"');
        
        // Codeigniter's profiling class - good for testing DB query loads and
        // script execution time - need to shut up the set_header XML or output is garbage
        $this->output->enable_profiler(FALSE);
    }
    
    public function index()
    { 
        $this->load->view('feeds');
    }
    
    private function theme($type, $asData = FALSE, $channel = NULL) {
        if (!is_null($channel)) {
            $this->load->model('remote_model');
            $theme = $this->remote_model->get_theme($channel);
            if ($channel == "XXCA9100101DTHCL") {
                $data['query'] = array('/content/TESTING/'.$type.'_'.$theme.'.png');
            } else {
                if ($theme) {
                    $data['query'] = array($this->assetLocalThemeDirectory.$type.'_'.$theme.'.png');
                } else {
                    $data['query'] = array($this->assetLocalThemeDirectory.$type.'_default.png');
                }
            }
            if (!$asData) {
                $this->load->view('asset_xml_out', $data);
            } else {
                return $this->load->view('asset_xml_out', $data, true);
            }
        } else {
            return false;
        }
    }
    
    private function curl_get_weather_contents($URL)
    {
        $c = curl_init();
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_URL, $URL);
        $contents = curl_exec($c);
        curl_close($c);
        
        if ($contents) return $contents;
        else return false;
    }
    
    public function weather($type = "3day", $channel = NULL) {
        $time = microtime();
        $time = explode(' ', $time);
        $time = $time[1] + $time[0];
        $start = $time;
        if (!is_null($channel)) {
            switch ($type) {
                case '3day' :
                    $data = array();
                    if ($channel == "") {
                        $this->load->view('weather_xml_out', $data);
                        break;
                    }
                    $this->load->model('remote_model');
                    $account_info = $this->remote_model->get_account_details($channel);
                    $data['account_info'] = $account_info;
                    
                    $postalCode = $account_info->account_zip;
                    /*if ($postalCode == "33301") {
                     $postalCode = "33301";
                     }*/
                    
                    @$xml = simplexml_load_file($this->config->item('weather_endpoint').$postalCode .'.XML');
                    
                    $today_str = "Today";
                    
                    if (!$xml) {
                        @$xml = simplexml_load_file($this->assetDataDirectory . 'weather/DEFAULT.xml');
                        $data['weather_data_loaded'] = "False";
                        $today_str = "";
                    } else {
                        $data['weather_data_loaded'] = "True";
                    }
                    
                    // MLIBBY - TODO Temporary Fix for CLEAR graphic until assets infocast is updated
                    if ($xml->conditions->graphic == "CLEAR") {
                        $xml->conditions->graphic = "SUNNY";
                    }
                    if ($xml->forecast[1]->graphic == "CLEAR") {
                        $xml->forecast[1]->graphic = "SUNNY";
                    }
                    if ($xml->forecast[2]->graphic == "CLEAR") {
                        $xml->forecast[2]->graphic = "SUNNY";
                    }
                    
                    $threeDayForecast = array("theme" => $this->theme('weather', TRUE, $channel), "today_label" => $today_str, "today_temp" => $xml->conditions->temp, "today_img" => $this->assetWeatherIconDirectory . $xml->conditions->graphic . '_icon.png', "tommorow_label" => $xml->forecast[1]->longday, "tommorow_img" => $this->assetWeatherIconDirectory . $xml->forecast[1]->graphic . '_icon.png', "tommorow_temp" => $xml->forecast[1]->high, "dayafter_label" => $xml->forecast[2]->longday, "dayafter_img" => $this->assetWeatherIconDirectory . $xml->forecast[2]->graphic . '_icon.png', "dayafter_temp" => $xml->forecast[2]->high);
                    $data['postalCode'] = $postalCode;
                    $data['query'] = $threeDayForecast;
                    $time = microtime();
                    $time = explode(' ', $time);
                    $time = $time[1] + $time[0];
                    $finish = $time;
                    $total_time = round(($finish - $start), 4);
                    $data['execution_time'] = $total_time;
                    $this->output->set_header('Content-Type: text/xml; charset="utf-8"');
                    $this->load->view('weather_xml_out', $data);
                    break;
                case '5day' :
                    $data = array();
                    if ($channel == "") {
                        $this->load->view('weather_xml_out', $data);
                        break;
                    }
                    $this->load->model('remote_model');
                    $account_info = $this->remote_model->get_account_details($channel);
                    $data['account_info'] = $account_info;
                    
                    $postalCode = $account_info->account_zip;
                    
                    @$xml = simplexml_load_file($this->config->item('weather_endpoint').$postalCode.'.XML');
                    
                    $today_str = "Today";
                    
                    if (!$xml) {
                        @$xml = simplexml_load_file($this->assetDataDirectory . 'weather/DEFAULT.xml');
                        $data['weather_data_loaded'] = "False";
                        $today_str = "";
                    } else {
                        $data['weather_data_loaded'] = "True";
                    }
                    
                    // MLIBBY - TODO Temporary Fix for CLEAR graphic until assets infocast is updated
                    if ($xml->conditions->graphic == "CLEAR") {
                        $xml->conditions->graphic = "SUNNY";
                    }
                    if ($xml->forecast[1]->graphic == "CLEAR") {
                        $xml->forecast[1]->graphic = "SUNNY";
                    }
                    if ($xml->forecast[2]->graphic == "CLEAR") {
                        $xml->forecast[2]->graphic = "SUNNY";
                    }
                    error_reporting(E_ALL);
                    ini_set('display_errors', '1');
                    $windsArray = array(
                        "N" => "north",
                        "NE" => "northeast",
                        "E" => "east",
                        "SE" => "southeast",
                        "S" => "south",
                        "SW" => "southwest",
                        "W" => "west",
                        "NW" => "northwest"
                    );
                    //echo print_r($xml,true);
                    $todayWinds = explode(" ", $xml->conditions->wind);
                    if (array_key_exists($todayWinds[0], $windsArray)) {
                        $todayWindDirection = $windsArray[$todayWinds[0]];
                        $todayWindSpeed = $todayWinds[1]." mph";
                    } else {
                        $todayWindDirection = "west";
                        $todayWindSpeed = "0 mph";
                    }
                    $todayForecast = "Today's Outlook: ".$xml->forecast[0]->description." The high will be ".$xml->forecast[0]->high." ";
                    $todayForecast.= "with a low of ".$xml->forecast[0]->low.".  The chance of precipitation is ".$xml->forecast[0]->precip.". ";
                    $todayForecast.= "Winds will be out of the ".$todayWindDirection." at ".$todayWindSpeed.".";
                    
                    $tomorrowWinds = explode(" ", $xml->forecast[1]->wind);
                    if (array_key_exists($tomorrowWinds[0], $windsArray)) {
                        $tomorrowWindDirection = $windsArray[$tomorrowWinds[0]];
                        $tomorrowWindSpeed = $tomorrowWinds[1]." mph";
                    } else {
                        $tomorrowWindDirection = "west";
                        $tomorrowWindSpeed = "0 mph";
                    }
                    $tomorrowForecast = "Tomorrow: ".$xml->forecast[1]->description." The high will be ".$xml->forecast[1]->high." ";
                    $tomorrowForecast.= "with a low of ".$xml->forecast[1]->low.".  The chance of precipitation is ".$xml->forecast[1]->precip.". ";
                    $tomorrowForecast.= "Winds will be out of the ".$tomorrowWindDirection." at ".$tomorrowWindSpeed.".";
                    
                    $secondDayWinds = explode(" ", $xml->forecast[2]->wind);
                    if (array_key_exists($secondDayWinds[0], $windsArray)) {
                        $secondDayWindDirection = $windsArray[$secondDayWinds[0]];
                        $secondDayWindSpeed = $secondDayWinds[1]." mph";
                    } else {
                        $secondDayWindDirection = "west";
                        $secondDayWindSpeed = "0 mph";
                    }
                    $secondDayForecast = date('l', strtotime('+2 day')).": ".$xml->forecast[2]->description." The high will be ".$xml->forecast[2]->high." ";
                    $secondDayForecast.= "with a low of ".$xml->forecast[2]->low.".  The chance of precipitation is ".$xml->forecast[2]->precip.". ";
                    $secondDayForecast.= "Winds will be out of the ".$secondDayWindDirection." at ".$secondDayWindSpeed.".";
                    
                    $thirdDayWinds = explode(" ", $xml->forecast[3]->wind);
                    if (array_key_exists($thirdDayWinds[0], $windsArray)) {
                        $thirdDayWindDirection = $windsArray[$thirdDayWinds[0]];
                        $thirdDayWindSpeed = $thirdDayWinds[1]." mph";
                    } else {
                        $thirdDayWindDirection = "west";
                        $thirdDayWindSpeed = "0 mph";
                    }
                    $thirdDayForecast = date('l', strtotime('+3 day')).": ".$xml->forecast[3]->description." The high will be ".$xml->forecast[3]->high." ";
                    $thirdDayForecast.= "with a low of ".$xml->forecast[3]->low.".  The chance of precipitation is ".$xml->forecast[3]->precip.". ";
                    $thirdDayForecast.= "Winds will be out of the ".$thirdDayWindDirection." at ".$thirdDayWindSpeed.".";
                    
                    $fourthDayWinds = explode(" ", $xml->forecast[4]->wind);
                    if (array_key_exists($fourthDayWinds[0], $windsArray)) {
                        $fourthDayWindDirection = $windsArray[$fourthDayWinds[0]];
                        $fourthDayWindSpeed = $fourthDayWinds[1]." mph";
                    } else {
                        $fourthDayWindDirection = "west";
                        $fourthDayWindSpeed = "0 mph";
                    }
                    $fourthDayForecast = date('l', strtotime('+4 day')).": ".$xml->forecast[4]->description." The high will be ".$xml->forecast[4]->high." ";
                    $fourthDayForecast.= "with a low of ".$xml->forecast[4]->low.".  The chance of precipitation is ".$xml->forecast[4]->precip.". ";
                    $fourthDayForecast.= "Winds will be out of the ".$fourthDayWindDirection." at ".$fourthDayWindSpeed.".";
                    
                    $fiveDayForecast = array("theme" => $this->theme('weather', TRUE, $channel),
                        "today_text" => $todayForecast,
                        "today_label" => $today_str,
                        "today_description" => $xml->conditions->description,
                        "today_temp" => $xml->conditions->temp,
                        "today_img" => $this->assetWeatherIconDirectory.$xml->conditions->graphic.'_V2W_icon.png',
                        "firstday_text" => $tomorrowForecast,
                        "firstday_label" => $xml->forecast[1]->longday,
                        "firstday_description" => $xml->forecast[1]->description,
                        "firstday_img" => $this->assetWeatherIconDirectory.$xml->forecast[1]->graphic.'_V2W_icon.png',
                        "firstday_temp" => $xml->forecast[1]->high,
                        "secondday_text" => $secondDayForecast,
                        "secondday_label" => $xml->forecast[2]->longday,
                        "secondday_description" => $xml->forecast[2]->description,
                        "secondday_img" => $this->assetWeatherIconDirectory.$xml->forecast[2]->graphic.'_V2W_icon.png',
                        "secondday_temp" => $xml->forecast[2]->high,
                        "thirdday_text" => $thirdDayForecast,
                        "thirdday_label" => $xml->forecast[3]->longday,
                        "thirdday_description" => $xml->forecast[3]->description,
                        "thirdday_img" => $this->assetWeatherIconDirectory.$xml->forecast[3]->graphic.'_V2W_icon.png',
                        "thirdday_temp" => $xml->forecast[3]->high,
                        "fourthday_text" => $fourthDayForecast,
                        "fourthday_label" => $xml->forecast[4]->longday,
                        "fourthday_description" => $xml->forecast[4]->description,
                        "fourthday_img" => $this->assetWeatherIconDirectory.$xml->forecast[4]->graphic.'_V2W_icon.png',
                        "fourthday_temp" => $xml->forecast[4]->high
                    );
                    $data['postalCode'] = $postalCode;
                    $data['query'] = $fiveDayForecast;
                    $time = microtime();
                    $time = explode(' ', $time);
                    $time = $time[1] + $time[0];
                    $finish = $time;
                    $total_time = round(($finish - $start), 4);
                    $data['execution_time'] = $total_time;
                    $this->output->set_header('Content-Type: text/xml; charset="utf-8"');
                    $this->load->view('5day_weather_xml_out', $data);
                    break;
                case '7dayold':
                    $data = array();
                    if ($channel == "") {
                        $this->load->view('weather_xml_out', $data);
                        break;
                    }
                    $this->load->model('remote_model');
                    $account_info = $this->remote_model->get_account_details($channel);
                    $data['account_info'] = $account_info;
                    
                    $postalCode = $account_info->account_zip;
                    
                    $json = $this->xml_to_json($this->config->item('weather_endpoint').$postalCode.'.XML');
                    header('Access-Control-Allow-Origin: *');
                    header("Access-Control-Allow-Methods: GET, OPTIONS");
                    header('Content-Type: application/json; charset="utf-8"');
                    echo $json;
                    break;
                case '7day':
                    $data = array();
                    $validation_failed = false;
                    if ($channel == "") {
                        $this->load->view('weather_xml_out', $data);
                        break;
                    }
                    $this->load->model('remote_model');
                    $account_info = $this->remote_model->get_account_details($channel);
                    if (is_object($account_info)) {
                        $data['account_info'] = $account_info;
                        $postalCode = $account_info->account_zip;
                    } else {
                        $validation_failed = true;
                    }
                    $json = $this->xml_to_json($this->config->item('weather_endpoint').$postalCode.'.XML');
                    $work_json = json_decode($json);
                    if (json_last_error() == 0 && (!$validation_failed)) {
                        $work_json->city = $account_info->physical_address_city_c;
                        $work_json->account_info = $data['account_info'];
                        /*if ($channel == "FLSW1234501DTHCL") {
                         $use_day_headers = $this->day_lists();
                         $z = 0;
                         foreach($work_json->forecast as &$work_forecast) {
                         if ($work_forecast->day != "Today") {
                         if ($z <= count($use_day_headers)) {
                         $work_json->forecast[$z]->day = strtoupper($use_day_headers['short_days'][$z]);
                         $work_json->forecast[$z]->longday = $use_day_headers['long_days'][$z];
                         $z++;
                         }
                         }
                         }
                         }*/
                        $json = json_encode($work_json);
                        header('Access-Control-Allow-Origin: *');
                        header("Access-Control-Allow-Methods: GET, OPTIONS");
                        header('Content-Type: application/json; charset="utf-8"');
                        //$this->output->set_header("Access-Control-Allow-Origin: *");
                        //$this->output->set_header("Access-Control-Allow-Methods: GET");
                        //$this->output->set_header('Content-Type: application/json; charset="utf-8"');
                        echo $json;
                    } else {
                        ob_clean();
                        $this->sendJsonResponse("No account information found for channel: $channel", 404);
                    }
                    break;
                case 'CORSTEST7day':
                    $data = array();
                    $validation_failed = false;
                    if ($channel == "") {
                        $this->load->view('weather_xml_out', $data);
                        break;
                    }
                    $this->load->model('remote_model');
                    $account_info = $this->remote_model->get_account_details($channel);
                    if (is_object($account_info)) {
                        $data['account_info'] = $account_info;
                        $postalCode = $account_info->account_zip;
                    } else {
                        $validation_failed = true;
                    }
                    $json = $this->xml_to_json($this->config->item('weather_endpoint').$postalCode.'.XML');
                    $work_json = json_decode($json);
                    if (json_last_error() == 0 && (!$validation_failed)) {
                        $work_json->city = $account_info->physical_address_city_c;
                        $work_json->account_info = $data['account_info'];
                        /*if ($channel == "FLSW1234501DTHCL") {
                         $use_day_headers = $this->day_lists();
                         $z = 0;
                         foreach($work_json->forecast as &$work_forecast) {
                         if ($work_forecast->day != "Today") {
                         if ($z <= count($use_day_headers)) {
                         $work_json->forecast[$z]->day = strtoupper($use_day_headers['short_days'][$z]);
                         $work_json->forecast[$z]->longday = $use_day_headers['long_days'][$z];
                         $z++;
                         }
                         }
                         }
                         }*/
                        $json = json_encode($work_json);
                        //header('Access-Control-Allow-Origin: *');
                        //header("Access-Control-Allow-Methods: GET, OPTIONS");
                        header('Content-Type: application/json; charset="utf-8"');
                        //$this->output->set_header("Access-Control-Allow-Origin: *");
                        //$this->output->set_header("Access-Control-Allow-Methods: GET");
                        //$this->output->set_header('Content-Type: application/json; charset="utf-8"');
                        echo $json;
                    } else {
                        ob_clean();
                        $this->sendJsonResponse("No account information found for channel: $channel", 404);
                    }
                    break;
                default:
                    // header("HTTP/1.1 500 Internal Server Error");

                    $this->sendJsonResponse("Internal Server Error", 500);

                    break;
            }
        }
    }
    
    private function returnWeather($type = "3day", $channel = NULL) {
        $time = microtime();
        $time = explode(' ', $time);
        $time = $time[1] + $time[0];
        $start = $time;
        if (!is_null($channel)) {
            switch ($type) {
                case '3day' :
                    $data = array();
                    if ($channel == "") {
                        $this->load->view('weather_xml_out', $data);
                        break;
                    }
                    $this->load->model('remote_model');
                    $account_info = $this->remote_model->get_account_details($channel);
                    $data['account_info'] = $account_info;
                    
                    $postalCode = $account_info->account_zip;
                    /*if ($postalCode == "33301") {
                     $postalCode = "33301";
                     }*/
                    
                    @$xml = simplexml_load_file($this->config->item('weather_endpoint') . $postalCode . '.XML');
                    
                    $today_str = "Today";
                    
                    if (!$xml) {
                        @$xml = simplexml_load_file($this->assetDataDirectory . 'weather/DEFAULT.xml');
                        $data['weather_data_loaded'] = "False";
                        $today_str = "";
                    } else {
                        $data['weather_data_loaded'] = "True";
                    }
                    
                    // MLIBBY - TODO Temporary Fix for CLEAR graphic until assets infocast is updated
                    if ($xml->conditions->graphic == "CLEAR") {
                        $xml->conditions->graphic = "SUNNY";
                    }
                    if ($xml->forecast[1]->graphic == "CLEAR") {
                        $xml->forecast[1]->graphic = "SUNNY";
                    }
                    if ($xml->forecast[2]->graphic == "CLEAR") {
                        $xml->forecast[2]->graphic = "SUNNY";
                    }
                    
                    $threeDayForecast = array("theme" => $this->theme('weather', TRUE, $channel), "today_label" => $today_str, "today_temp" => $xml->conditions->temp, "today_img" => $this->assetWeatherIconDirectory . $xml->conditions->graphic . '_icon.png', "tommorow_label" => $xml->forecast[1]->longday, "tommorow_img" => $this->assetWeatherIconDirectory . $xml->forecast[1]->graphic . '_icon.png', "tommorow_temp" => $xml->forecast[1]->high, "dayafter_label" => $xml->forecast[2]->longday, "dayafter_img" => $this->assetWeatherIconDirectory . $xml->forecast[2]->graphic . '_icon.png', "dayafter_temp" => $xml->forecast[2]->high);
                    $data['postalCode'] = $postalCode;
                    $data['query'] = $threeDayForecast;
                    $time = microtime();
                    $time = explode(' ', $time);
                    $time = $time[1] + $time[0];
                    $finish = $time;
                    $total_time = round(($finish - $start), 4);
                    $data['execution_time'] = $total_time;
                    $this->load->view('weather_xml_out', $data);
                    break;
                case '5day' :
                    $data = array();
                    if ($channel == "") {
                        $this->load->view('weather_xml_out', $data);
                        break;
                    }
                    $this->load->model('remote_model');
                    $account_info = $this->remote_model->get_account_details($channel);
                    $data['account_info'] = $account_info;
                    
                    $postalCode = $account_info->account_zip;
                    
                    @$xml = simplexml_load_file($this->config->item('weather_endpoint') . $postalCode . '.XML');
                    
                    $today_str = "Today";
                    
                    if (!$xml) {
                        @$xml = simplexml_load_file($this->assetDataDirectory . 'weather/DEFAULT.xml');
                        $data['weather_data_loaded'] = "False";
                        $today_str = "";
                    } else {
                        $data['weather_data_loaded'] = "True";
                    }
                    
                    // MLIBBY - TODO Temporary Fix for CLEAR graphic until assets infocast is updated
                    if ($xml->conditions->graphic == "CLEAR") {
                        $xml->conditions->graphic = "SUNNY";
                    }
                    if ($xml->forecast[1]->graphic == "CLEAR") {
                        $xml->forecast[1]->graphic = "SUNNY";
                    }
                    if ($xml->forecast[2]->graphic == "CLEAR") {
                        $xml->forecast[2]->graphic = "SUNNY";
                    }
                    error_reporting(E_ALL);
                    ini_set('display_errors', '1');
                    $windsArray = array(
                        "N" => "north",
                        "NE" => "northeast",
                        "E" => "east",
                        "SE" => "southeast",
                        "S" => "south",
                        "SW" => "southwest",
                        "W" => "west",
                        "NW" => "northwest"
                    );
                    //echo print_r($xml,true);
                    $todayWinds = explode(" ", $xml->conditions->wind);
                    if (array_key_exists($todayWinds[0], $windsArray)) {
                        $todayWindDirection = $windsArray[$todayWinds[0]];
                        $todayWindSpeed = $todayWinds[1]." mph";
                    } else {
                        $todayWindDirection = "west";
                        $todayWindSpeed = "0 mph";
                    }
                    $todayForecast = "Today's Outlook: ".$xml->forecast[0]->description." The high will be ".$xml->forecast[0]->high." ";
                    $todayForecast.= "with a low of ".$xml->forecast[0]->low.".  The chance of precipitation is ".$xml->forecast[0]->precip.". ";
                    $todayForecast.= "Winds will be out of the ".$todayWindDirection." at ".$todayWindSpeed.".";
                    
                    $tomorrowWinds = explode(" ", $xml->forecast[1]->wind);
                    if (array_key_exists($tomorrowWinds[0], $windsArray)) {
                        $tomorrowWindDirection = $windsArray[$tomorrowWinds[0]];
                        $tomorrowWindSpeed = $tomorrowWinds[1]." mph";
                    } else {
                        $tomorrowWindDirection = "west";
                        $tomorrowWindSpeed = "0 mph";
                    }
                    $tomorrowForecast = "Tomorrow: ".$xml->forecast[1]->description." The high will be ".$xml->forecast[1]->high." ";
                    $tomorrowForecast.= "with a low of ".$xml->forecast[1]->low.".  The chance of precipitation is ".$xml->forecast[1]->precip.". ";
                    $tomorrowForecast.= "Winds will be out of the ".$tomorrowWindDirection." at ".$tomorrowWindSpeed.".";
                    
                    $secondDayWinds = explode(" ", $xml->forecast[2]->wind);
                    if (array_key_exists($secondDayWinds[0], $windsArray)) {
                        $secondDayWindDirection = $windsArray[$secondDayWinds[0]];
                        $secondDayWindSpeed = $secondDayWinds[1]." mph";
                    } else {
                        $secondDayWindDirection = "west";
                        $secondDayWindSpeed = "0 mph";
                    }
                    $secondDayForecast = date('l', strtotime('+2 day')).": ".$xml->forecast[2]->description." The high will be ".$xml->forecast[2]->high." ";
                    $secondDayForecast.= "with a low of ".$xml->forecast[2]->low.".  The chance of precipitation is ".$xml->forecast[2]->precip.". ";
                    $secondDayForecast.= "Winds will be out of the ".$secondDayWindDirection." at ".$secondDayWindSpeed.".";
                    
                    $thirdDayWinds = explode(" ", $xml->forecast[3]->wind);
                    if (array_key_exists($thirdDayWinds[0], $windsArray)) {
                        $thirdDayWindDirection = $windsArray[$thirdDayWinds[0]];
                        $thirdDayWindSpeed = $thirdDayWinds[1]." mph";
                    } else {
                        $thirdDayWindDirection = "west";
                        $thirdDayWindSpeed = "0 mph";
                    }
                    $thirdDayForecast = date('l', strtotime('+3 day')).": ".$xml->forecast[3]->description." The high will be ".$xml->forecast[3]->high." ";
                    $thirdDayForecast.= "with a low of ".$xml->forecast[3]->low.".  The chance of precipitation is ".$xml->forecast[3]->precip.". ";
                    $thirdDayForecast.= "Winds will be out of the ".$thirdDayWindDirection." at ".$thirdDayWindSpeed.".";
                    
                    $fourthDayWinds = explode(" ", $xml->forecast[4]->wind);
                    if (array_key_exists($fourthDayWinds[0], $windsArray)) {
                        $fourthDayWindDirection = $windsArray[$fourthDayWinds[0]];
                        $fourthDayWindSpeed = $fourthDayWinds[1]." mph";
                    } else {
                        $fourthDayWindDirection = "west";
                        $fourthDayWindSpeed = "0 mph";
                    }
                    $fourthDayForecast = date('l', strtotime('+4 day')).": ".$xml->forecast[4]->description." The high will be ".$xml->forecast[4]->high." ";
                    $fourthDayForecast.= "with a low of ".$xml->forecast[4]->low.".  The chance of precipitation is ".$xml->forecast[4]->precip.". ";
                    $fourthDayForecast.= "Winds will be out of the ".$fourthDayWindDirection." at ".$fourthDayWindSpeed.".";
                    
                    $fiveDayForecast = array("theme" => $this->theme('weather', TRUE, $channel),
                        "today_text" => $todayForecast,
                        "today_label" => $today_str,
                        "today_description" => $xml->conditions->description,
                        "today_temp" => $xml->conditions->temp,
                        "today_img" => $this->assetWeatherIconDirectory.$xml->conditions->graphic.'_V2W_icon.png',
                        "firstday_text" => $tomorrowForecast,
                        "firstday_label" => $xml->forecast[1]->longday,
                        "firstday_description" => $xml->forecast[1]->description,
                        "firstday_img" => $this->assetWeatherIconDirectory.$xml->forecast[1]->graphic.'_V2W_icon.png',
                        "firstday_temp" => $xml->forecast[1]->high,
                        "secondday_text" => $secondDayForecast,
                        "secondday_label" => $xml->forecast[2]->longday,
                        "secondday_description" => $xml->forecast[2]->description,
                        "secondday_img" => $this->assetWeatherIconDirectory.$xml->forecast[2]->graphic.'_V2W_icon.png',
                        "secondday_temp" => $xml->forecast[2]->high,
                        "thirdday_text" => $thirdDayForecast,
                        "thirdday_label" => $xml->forecast[3]->longday,
                        "thirdday_description" => $xml->forecast[3]->description,
                        "thirdday_img" => $this->assetWeatherIconDirectory.$xml->forecast[3]->graphic.'_V2W_icon.png',
                        "thirdday_temp" => $xml->forecast[3]->high,
                        "fourthday_text" => $fourthDayForecast,
                        "fourthday_label" => $xml->forecast[4]->longday,
                        "fourthday_description" => $xml->forecast[4]->description,
                        "fourthday_img" => $this->assetWeatherIconDirectory.$xml->forecast[4]->graphic.'_V2W_icon.png',
                        "fourthday_temp" => $xml->forecast[4]->high
                    );
                    $data['postalCode'] = $postalCode;
                    $data['query'] = $fiveDayForecast;
                    $time = microtime();
                    $time = explode(' ', $time);
                    $time = $time[1] + $time[0];
                    $finish = $time;
                    $total_time = round(($finish - $start), 4);
                    $data['execution_time'] = $total_time;
                    return $this->load->view('5day_weather_xml_out', $data, true);
                    break;
                case '7dayold':
                    $data = array();
                    if ($channel == "") {
                        $this->load->view('weather_xml_out', $data);
                        break;
                    }
                    $this->load->model('remote_model');
                    $account_info = $this->remote_model->get_account_details($channel);
                    $data['account_info'] = $account_info;
                    
                    $postalCode = $account_info->account_zip;
                    
                    $json = $this->xml_to_json($this->config->item('weather_endpoint').$postalCode.'.XML');
                    $this->output->set_header("Access-Control-Allow-Origin: *");
                    $this->output->set_header('Content-Type: application/json; charset="utf-8"');
                    echo $json;
                    break;
                case '7dayold':
                    $data = array();
                    if ($channel == "") {
                        $this->load->view('weather_xml_out', $data);
                        break;
                    }
                    $this->load->model('remote_model');
                    $account_info = $this->remote_model->get_account_details($channel);
                    $data['account_info'] = $account_info;
                    
                    $postalCode = $account_info->account_zip;
                    
                    $json = $this->xml_to_json($this->config->item('weather_endpoint').$postalCode.'.XML');
                    $this->output->set_header("Access-Control-Allow-Origin: *");
                    $this->output->set_header('Content-Type: application/json; charset="utf-8"');
                    echo $json;
                    break;
                case '7day':
                    $data = array();
                    $validation_failed = false;
                    if ($channel == "") {
                        $this->load->view('weather_xml_out', $data);
                        break;
                    }
                    $this->load->model('remote_model');
                    $account_info = $this->remote_model->get_account_details($channel);
                    if (is_object($account_info)) {
                        $data['account_info'] = $account_info;
                        $postalCode = $account_info->account_zip;
                    } else {
                        $validation_failed = true;
                    }
                    $json = $this->xml_to_json($this->config->item('weather_endpoint').$postalCode.'.XML');
                    $work_json = json_decode($json);
                    if (json_last_error() == 0 && (!$validation_failed)) {
                        $work_json->city = $account_info->physical_address_city_c;
                        /*if ($channel == "FLSW1234501DTHCL") {
                         $use_day_headers = $this->day_lists();
                         $z = 0;
                         foreach($work_json->forecast as &$work_forecast) {
                         if ($work_forecast->day != "Today") {
                         if ($z <= count($use_day_headers)) {
                         $work_json->forecast[$z]->day = strtoupper($use_day_headers['short_days'][$z]);
                         $work_json->forecast[$z]->longday = $use_day_headers['long_days'][$z];
                         $z++;
                         }
                         }
                         }
                         }*/
                        $json = json_encode($work_json);
                        
                        $this->output->set_header("Access-Control-Allow-Origin: *");
                        $this->output->set_header('Content-Type: application/json; charset="utf-8"');
                        echo $json;
                    } else {
                        ob_clean();
                        $this->sendJsonResponse("No account information found for channel: $channel", 404);
                    }
                    break;
                default:
                    header("HTTP/1.1 500 Internal Server Error");
                    break;
            }
        }
    }

    private function sendJsonResponse($message = '', $statusCode = 200)
    {
        $response = [
            'error' => [
                'code' => $statusCode,
                'message' => $message,
            ]
        ];

        $this->output
            ->set_status_header($statusCode)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
            ->_display();
        exit;
    }
    
    
    private function day_lists() {
        $short_days = array();
        $long_days = array();
        $dates = array();
        $date_to_use = "06-23-2016";
        
        for ($i=1; $i<=8; $i++) {
            $date = new DateTime(strtotime($date_to_use));
            $date->add(new DateInterval('P'.$i.'D'));
            $short_days[] = strtoupper($date->format('D'));
            $long_days[] = strtoupper($date->format('l'));
            
        }
        $dates['long_days'] = $long_days;
        $dates['short_days'] = $short_days;
        return $dates;
    }
    
    private function xml_to_json($url) {
        $fileContents= file_get_contents($url);
        $fileContents = str_replace(array("\n", "\r", "\t"), '', $fileContents);
        $fileContents = trim(str_replace('"', "'", $fileContents));
        $simpleXml = simplexml_load_string($fileContents);
        $json = json_encode($simpleXml);
        return $json;
    }
    
    
    private function custom_scroller($channel = null) {
        if (!is_null($channel)) {
            $this->load->model('remote_model');
            
            $data['query'] = $this->remote_model->get_custom_scroller($channel);
            $data['theme'] = $this->theme('scroll1',true,$channel);
            return $data;
            //$this->load->view('custom_scroller_xml_out', $data);
        } else {
            $data['theme'] = $this->theme('scroll2', TRUE);
            $this->load->view('custom_scroller_xml_out_empty', $data);
        }
    }
    
    private function added_scroller($channel = null, $scrollerType = null) {
        if (!is_null($channel) && !is_null($scrollerType)) {
            $bad_words_array = $this->feeds_model->check_bad_word_array();
            $data = array();
            
            $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
            
            $hash = md5($scrollerType . $channel);
            
            if (!$this->cache->get($hash)) {
                @$xml = simplexml_load_file($this->config->item('digichief_'.strtolower($scrollerType)));
                $this->cache->save($hash, @$xml->asXML(), 900);
                $data['cache'] = "False";
            } else {
                $xml = $this->cache->get($hash);
                @$xml = simplexml_load_string($xml);
                $data['cache'] = "True";
            }
            
            if (!$xml) {
                @$xml = simplexml_load_file($this->assetDataDirectory . 'news/'.$scrollerType.'.xml');
            }
            
            $data['query'] = array();
            $bannedCount = 0;
            
            if (strtolower($scrollerType) == "thisdate") {
                $thisDate = array();
                foreach ($xml->item as $thisDateItem) {
                    $banned = false;
                    $words = explode(" ", $thisDateItem->description);
                    foreach (array_unique($words) as $checkTarget) {
                        if (strlen($checkTarget) >= 3) {
                            $needle = array(',',':','-','!','?','.',';','\'');
                            if (in_array(str_replace($needle, "", strtolower($checkTarget)), $bad_words_array)) {
                                $banned = true;
                                $bannedCount++;
                                break;
                            }
                        }
                    }
                    if (strlen($thisDateItem->description) < '210' && strlen($thisDateItem->description) > '30' && !$banned) {
                        $thisDate['scroller_message'] = preg_replace('!\s+!', ' ',"This Date in History: ".$thisDateItem->description);
                        $data['query'][] = $thisDate;
                    } else {
                        $data['banned'][] = preg_replace('!\s+!', ' ',"This Date in History: ".$thisDateItem->description);
                    }
                }
            } elseif (strtolower($scrollerType) == "borndate") {
                $bornDate = array();
                foreach ($xml->item as $bornDateItem) {
                    $banned = false;
                    $words = explode(" ", $bornDateItem->description);
                    foreach (array_unique($words) as $checkTarget) {
                        if (strlen($checkTarget) >= 3) {
                            $needle = array(',',':','-','!','?','.',';','\'');
                            if (in_array(str_replace($needle, "", strtolower($checkTarget)), $bad_words_array)) {
                                $banned = true;
                                $bannedCount++;
                                break;
                            }
                        }
                    }
                    if (strlen($bornDateItem->description) < '210' && strlen($bornDateItem->description) > '30' && !$banned) {
                        $bornDate['scroller_message'] = preg_replace('!\s+!', ' ',"Born on this Date: ".$bornDateItem->description);
                        $data['query'][] = $bornDate;
                    } else {
                        $data['banned'][] = preg_replace('!\s+!', ' ',"Born on this Date: ".$bornDateItem->description);
                    }
                    
                }
            }
            $data['theme'] = $this->theme('scroll1',true,$channel);
            return $data;
            //$this->load->view('custom_scroller_xml_out', $data);
        } else {
            $data['theme'] = $this->theme('scroll2', TRUE);
            $this->load->view('custom_scroller_xml_out_empty', $data);
        }
    }
    
    public function social_scroller($channel = null) {
        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
        
        $hash = md5($channel.'social');
        
        $socialFeedArray = array();
        
        if (!$this -> cache -> get($hash)) {
            $attributes = $this->Source_model->socialScroller($channel);
            if ($attributes) {
                foreach ($attributes as $socialFeedAttributes) {
                    switch ( $socialFeedAttributes->social_type_c ) {
                        case 'twitter' :
                            array_push($socialFeedArray, $this->twitterConnection($channel, $socialFeedAttributes));
                            break;
                        case 'dealerrater' :
                            array_push($socialFeedArray, $this->dealerRaterConnection($channel, $socialFeedAttributes));
                            break;
                        case 'foursquare' :
                            array_push($socialFeedArray, $this->fourSquareConnection($channel, $socialFeedAttributes));
                            break;
                        case 'facebook' :
                            array_push($socialFeedArray, $this->facebookConnection($channel, $socialFeedAttributes));
                            break;
                        default :
                            array_push($socialFeedArray, array( array(' ', 'default')));
                            break;
                    }
                }
                $data['query'] = $socialFeedArray;
            } else {
                array_push($socialFeedArray, array( array(' ', 'default')));
                $data['query'] = $socialFeedArray;
            }
            $this -> cache -> save($hash, $socialFeedArray, $this -> config -> item('social_cache'));
            $data['cache'] = "False";
            
        } else {
            $data['query'] = $this -> cache -> get($hash);
            $data['cache'] = "True";
        }
        
        $data['hash'] = $hash;
        $data['memoryUsage'] = $this->benchmark->memory_usage();
        $data['execTime'] = $this->benchmark->elapsed_time();
        $data['iconLocation'] = $this->assetLocalIconDirectory;
        $data['theme'] = $this->theme('scroll1', TRUE);
        
        $this->load->view('social_scroller_xml_out', $data);
        
    }
    
    public function twitterConnection($channel, $socialFeedAttributes) {
        $fetchData = $this->social->twitterFetch(
            $socialFeedAttributes->api_key_username_c,
            $socialFeedAttributes->depth_c,
            $socialFeedAttributes->max_time_c,
            $socialFeedAttributes->randomize_order_c,
            $socialFeedAttributes->show_incentive_c,
            $socialFeedAttributes->incentive_text_c,
            $socialFeedAttributes->show_feeds_c
            );
        
        if ($fetchData) {
            return $fetchData;
        } else {
            return array( array(' ', 'default'));
        }
    }
    
    public function facebookConnection($channel, $socialFeedAttributes) {
        $fetchData = $this->social->facebookFetch(
            $socialFeedAttributes->facebook_data_type_c,
            $socialFeedAttributes->api_key_username_c,
            $socialFeedAttributes->facebook_fan_page_id_c,
            $socialFeedAttributes->depth_c,
            $socialFeedAttributes->max_time_c,
            $socialFeedAttributes->randomize_order_c,
            $socialFeedAttributes->show_incentive_c,
            $socialFeedAttributes->incentive_text_c,
            $socialFeedAttributes->show_feeds_c
            );
        
        if ($fetchData) {
            return $fetchData;
        } else {
            return array( array(' ', 'default'));
        }
    }
    
    public function dealerraterConnection($channel, $socialFeedAttributes) {
        $fetchData = $this->social->dealerRaterFetch(
            $socialFeedAttributes->api_key_username_c,
            $socialFeedAttributes->depth_c,
            $socialFeedAttributes->max_time_c,
            $socialFeedAttributes->randomize_order_c,
            $socialFeedAttributes->show_incentive_c,
            $socialFeedAttributes->incentive_text_c,
            $socialFeedAttributes -> show_feeds_c
            );
        
        if ($fetchData) {
            return $fetchData;
        } else {
            return array( array(' ', 'default'));
        }
    }
    
    public function foursquareConnection($channel, $socialFeedAttributes) {
        $fetchData = $this->social->fourSquareFetch(
            $socialFeedAttributes->api_key_username_c,
            $socialFeedAttributes->depth_c,
            $socialFeedAttributes->max_time_c,
            $socialFeedAttributes->randomize_order_c,
            $socialFeedAttributes->show_incentive_c,
            $socialFeedAttributes->incentive_text_c,
            $socialFeedAttributes->show_feeds_c
            );
        
        if ($fetchData) {
            return $fetchData;
        } else {
            return array( array(' ', 'default'));
        }
    }
    
    private function utf8_for_xml($string)
    {
        include_once APPPATH.'classes/Encoding.php';
        
        $interpret = new ForceUTF8\Encoding();
        return $interpret->fixUTF8($string);
    }
    
    public function scroller($channel = NULL, $json = NULL) {
        $time = microtime();
        $time = explode(' ', $time);
        $time = $time[1] + $time[0];
        $start = $time;
        $data = array();
        if (!is_null($channel)) {
            include_once APPPATH.'classes/Encoding.php';
            $interpret = new ForceUTF8\Encoding();
            
            $accountNumber = substr($channel,4,5);
            
            $workTheme = $this->theme('scroll1',true,$channel);
            
            preg_match_all('/<url>(.*?)<\/url>/s', $workTheme, $matches);
            $theme = $matches[0][0];
            
            $customerScrollers = array();
            $custom_scroller = $this->custom_scroller($channel);
            
            if (array_key_exists("query", $custom_scroller)) {
                if (is_array($custom_scroller["query"])) {
                    foreach ($custom_scroller["query"] as $item) {
                        $item['scroller_message'] = str_replace(
                            array("\xe2\x80\x98", "\xe2\x80\x99", "\xe2\x80\x9c", "\xe2\x80\x9d", "\xe2\x80\x93", "\xe2\x80\x94", "\xe2\x80\xa6"),
                            array("'", "'", '"', '"', '-', '--', '...'),
                            $item['scroller_message']);
                        
                        $item['scroller_message'] = str_replace(
                            array(chr(145), chr(146), chr(147), chr(148), chr(150), chr(151), chr(133)),
                            array("'", "'", '"', '"', '-', '--', '...'),
                            $item['scroller_message']);
                        
                        $item['scroller_message'] = utf8_encode($interpret->fixUTF8($item['scroller_message']));
                        
                        $customerScrollers[] = array("type" => "custom", "story" => $item['scroller_message'], "image" => $theme, "icon" => "/content/DEALER_TV_CONTENT/DTV_ASSETS/NEWS_ICONS/default.png");
                    }
                }
            }
            
            /*echo "<pre>";
             echo print_r($custom_scroller, true);
             echo print_r($customerScrollers, true);*/
            
            $xml = false;
            $scrollerCacheFile = getenv('FEEDS_DIRECTORY_PATH') . '/digicache/FinalScrollerCache.xml';
            if (file_exists($scrollerCacheFile)) {
                if (filesize($scrollerCacheFile) > 1) {
                    $xml = simplexml_load_file($scrollerCacheFile);
                } else {
                    $xml = new stdClass();
                }
            }
            
            $data['query'] = array();
            
            if (property_exists($xml,'stories')) {
                if (property_exists($xml->stories,'item')) {
                    foreach($xml->stories->item as $story) {
                        $story->text = str_replace(
                            array("\xe2\x80\x98", "\xe2\x80\x99", "\xe2\x80\x9c", "\xe2\x80\x9d", "\xe2\x80\x93", "\xe2\x80\x94", "\xe2\x80\xa6"),
                            array("'", "'", '"', '"', '-', '--', '...'),
                            $story->text);
                        
                        $story->text = str_replace(
                            array(chr(145), chr(146), chr(147), chr(148), chr(150), chr(151), chr(133)),
                            array("'", "'", '"', '"', '-', '--', '...'),
                            $story->text);
                        
                        $story->text = utf8_encode($interpret->fixUTF8($story->text));
                        
                        if ($story->category == 'customer') {
                            foreach($customerScrollers as $customerItem) {
                                $data['query'][] = $customerItem;
                            }
                        } else {
                            $icon = $story->category.'.png';
                            if ($icon == '') {
                                $icon = 'default.png';
                            }
                            if ($accountNumber == '11205' || $accountNumber == '20335') {
                                $testCategory = (string)$story->category;
                                if (strtoupper($testCategory) == 'SPORTS') {
                                    $data["query"][] = array("type" => (string)$story->category, "story" => (string)$story->text, "image" => $theme, "icon" => "/content/DEALER_TV_CONTENT/DTV_ASSETS/NEWS_ICONS/default.png");
                                }
                            } elseif ($accountNumber == '20698') {
                                $testCategory = (string)$story->category;
                                if (strtoupper($testCategory) == 'SPORTS' || strtoupper($testCategory) == 'DEFAULT') {
                                    $data["query"][] = array("type" => (string)$story->category, "story" => (string)$story->text, "image" => $theme, "icon" => "/content/DEALER_TV_CONTENT/DTV_ASSETS/NEWS_ICONS/default.png");
                                }
                            } else {
                                $data["query"][] = array("type" => (string)$story->category, "story" => (string)$story->text, "image" => $theme, "icon" => "/content/DEALER_TV_CONTENT/DTV_ASSETS/NEWS_ICONS/default.png");
                            }
                        }
                    }
                }
            } else {
                $data['query'] = $customerScrollers;
            }
            
            $data['query'] = array_slice($data['query'],0,195);
            $data["cacheone"] = '';//$news_one["cache"];
            $data["cachetwo"] = '';//$news_two["cache"];
            
            //shuffle($data["query"]);
            $time = microtime();
            $time = explode(' ', $time);
            $time = $time[1] + $time[0];
            $finish = $time;
            $total_time = round(($finish - $start), 4);
            $data['execution_time'] = $total_time;
            
            /*echo "<pre>";
             echo print_r($data, true);
             return;*/
            
            if (!empty($json)) {
                $xml = $this->load->view('news_scroller_xml_out', $data, true);
                $workXML = trim(preg_replace('/\t+/', '', $xml));
                $workXML = trim(preg_replace('/\n+/', '', $workXML));
                $tempXML = simplexml_load_string($workXML, null, LIBXML_NOCDATA);
                header('Access-Control-Allow-Origin: *');
                header('Content-Type: application/json;');
                echo json_encode($tempXML);
            } else {
                $this->output->set_content_type('text/xml');
                $this->output->set_header('Content-Type: text/xml; charset="utf-8"');
                $this->load->view('news_scroller_xml_out', $data);
            }
        } else {
            return false;
        }
    }
    
    
    
    private function returnScroller($channel = NULL) {
        $time = microtime();
        $time = explode(' ', $time);
        $time = $time[1] + $time[0];
        $start = $time;
        if (!is_null($channel)) {
            include_once APPPATH.'classes/Encoding.php';
            $interpret = new ForceUTF8\Encoding();
            
            $accountNumber = substr($channel,4,5);
            
            $workTheme = $this->theme('scroll1',true,$channel);
            
            preg_match_all('/<url>(.*?)<\/url>/s', $workTheme, $matches);
            $theme = $matches[0][0];
            
            $customerScrollers = array();
            $custom_scroller = $this->custom_scroller($channel);
            
            if (array_key_exists("query", $custom_scroller)) {
                if (is_array($custom_scroller["query"])) {
                    foreach ($custom_scroller["query"] as $item) {
                        $customerScrollers[] = array("type" => "custom", "story" => utf8_encode($interpret->fixUTF8($item['scroller_message'])), "image" => $theme, "icon" => "/content/DEALER_TV_CONTENT/DTV_ASSETS/NEWS_ICONS/default.png");
                    }
                }
            }
            
            /*echo "<pre>";
             echo print_r($custom_scroller, true);
             echo print_r($customerScrollers, true);*/
            
            $xml = false;
            $scrollerCacheFile = getenv('FEEDS_DIRECTORY_PATH') . '/digicache/FinalScrollerCache.xml';
            if (file_exists($scrollerCacheFile)) {
                if (filesize($scrollerCacheFile) > 1) {
                    $xml = simplexml_load_file($scrollerCacheFile);
                } else {
                    $xml = new stdClass();
                }
            }
            
            $data['query'] = array();
            
            if (property_exists($xml,'stories')) {
                if (property_exists($xml->stories,'item')) {
                    foreach($xml->stories->item as $story) {
                        if ($story->category == 'customer') {
                            foreach($customerScrollers as $customerItem) {
                                $data['query'][] = $customerItem;
                            }
                        } else {
                            $icon = $story->category.'.png';
                            if ($icon == '') {
                                $icon = 'default.png';
                            }
                            $data["query"][] = array("type" => (string)$story->category, "story" => (string)$story->text, "image" => $theme, "icon" => "/content/DEALER_TV_CONTENT/DTV_ASSETS/NEWS_ICONS/default.png");
                        }
                    }
                }
            } else {
                $data['query'] = $customerScrollers;
            }
            
            $data["cacheone"] = '';//$news_one["cache"];
            $data["cachetwo"] = '';//$news_two["cache"];
            
            $time = microtime();
            $time = explode(' ', $time);
            $time = $time[1] + $time[0];
            $finish = $time;
            $total_time = round(($finish - $start), 4);
            $data['execution_time'] = $total_time;
            
            return $this->load->view('news_scroller_xml_out', $data, true);
        } else {
            return false;
        }
    }
    
    public function oldScroller($channel = NULL) {
        $time = microtime();
        $time = explode(' ', $time);
        $time = $time[1] + $time[0];
        $start = $time;
        if (!is_null($channel)) {
            include_once APPPATH.'classes/Encoding.php';
            $interpret = new ForceUTF8\Encoding();
            
            $accountNumber = substr($channel,4,5);
            $data["query"] = array();
            $custom_scroller = $this->custom_scroller($channel);
            if ($custom_scroller["query"] !== false) {
                preg_match_all('/<url>(.*?)<\/url>/s', $custom_scroller["theme"], $matches);
                $theme = $matches[0][0];
                foreach ($custom_scroller["query"] as $item) {
                    $data["query"][] = array("type" => "custom", "story" => utf8_encode($interpret->fixUTF8($item['scroller_message'])), "image" => $theme, "icon" => "/content/DEALER_TV_CONTENT/DTV_ASSETS/NEWS_ICONS/default.png");
                }
            }
            $news_one = $this->digichief($channel,"news","all",1);
            preg_match_all('/<url>(.*?)<\/url>/s', $news_one["theme"], $matches);
            $theme = $matches[0][0];
            $counter = 0;
            foreach ($news_one["query"] as $item) {
                $data["query"][] = array("type" => "news", "story" => (string)$item[0], "image" => $theme, "icon" => "/content/DEALER_TV_CONTENT/DTV_ASSETS/NEWS_ICONS/".$news_one["icon"][$counter].".png");
                $counter++;
            }
            if ($accountNumber != '11205' && $accountNumber != '20136' && $accountNumber != '20335') {
                $added_scroller = $this->added_scroller($channel, 'thisdate');
                if ($added_scroller["query"] !== false) {
                    preg_match_all('/<url>(.*?)<\/url>/s', $added_scroller["theme"], $matches);
                    $theme = $matches[0][0];
                    foreach ($added_scroller["query"] as $item) {
                        $data["query"][] = array("type" => "custom", "story" => utf8_encode($interpret->fixUTF8($item['scroller_message'])), "image" => $theme, "icon" => "/content/DEALER_TV_CONTENT/DTV_ASSETS/NEWS_ICONS/default.png");
                    }
                }
            }
            $news_two = $this->digichief($channel,"news","all",2);
            preg_match_all('/<url>(.*?)<\/url>/s', $news_two["theme"], $matches);
            $theme = $matches[0][0];
            $counter = 0;
            foreach ($news_two["query"] as $item) {
                $data["query"][] = array("type" => "news", "story" => (string)$item[0], "image" => $theme, "icon" => "/content/DEALER_TV_CONTENT/DTV_ASSETS/NEWS_ICONS/".$news_two["icon"][$counter].".png");
                $counter++;
            }
            if ($accountNumber != '11205' && $accountNumber != '20136' && $accountNumber != '20335') {
                $added_scroller = $this->added_scroller($channel, 'borndate');
                if ($added_scroller["query"] !== false) {
                    preg_match_all('/<url>(.*?)<\/url>/s', $added_scroller["theme"], $matches);
                    $theme = $matches[0][0];
                    foreach ($added_scroller["query"] as $item) {
                        $data["query"][] = array("type" => "custom", "story" => utf8_encode($interpret->fixUTF8($item['scroller_message'])), "image" => $theme, "icon" => "/content/DEALER_TV_CONTENT/DTV_ASSETS/NEWS_ICONS/default.png");
                    }
                }
            }
            $data["cacheone"] = $news_one["cache"];
            $data["cachetwo"] = $news_two["cache"];
            
            //shuffle($data["query"]);
            $time = microtime();
            $time = explode(' ', $time);
            $time = $time[1] + $time[0];
            $finish = $time;
            $total_time = round(($finish - $start), 4);
            $data['execution_time'] = $total_time;
            
            $this->output->set_content_type('text/xml');
            $this->output->set_header('Content-Type: text/xml; charset="utf-8"');
            $this->load->view('news_scroller_xml_out', $data);
        } else {
            return false;
        }
    }
    
    public function testAdded($channel = NULL) {
        $data["query"] = array();
        $added_scroller = $this->added_scroller($channel, 'thisdate');
        $data["thisDateOriginal"] = $added_scroller;
        if ($added_scroller["query"] !== false) {
            preg_match_all('/<url>(.*?)<\/url>/s', $added_scroller["theme"], $matches);
            $theme = $matches[0][0];
            foreach ($added_scroller["query"] as $item) {
                $data["query"][] = array("type" => "custom", "story" => utf8_encode($this->utf8_for_xml($item['scroller_message'])), "image" => $theme, "icon" => "/content/DEALER_TV_CONTENT/DTV_ASSETS/NEWS_ICONS/default.png", "length" => strlen($item['scroller_message']));
            }
        }
        
        $added_scroller = $this->added_scroller($channel, 'borndate');
        $data["bornDateOriginal"] = $added_scroller;
        if ($added_scroller["query"] !== false) {
            preg_match_all('/<url>(.*?)<\/url>/s', $added_scroller["theme"], $matches);
            $theme = $matches[0][0];
            foreach ($added_scroller["query"] as $item) {
                $data["query"][] = array("type" => "custom", "story" => utf8_encode($this->utf8_for_xml($item['scroller_message'])), "image" => $theme, "icon" => "/content/DEALER_TV_CONTENT/DTV_ASSETS/NEWS_ICONS/default.png", "length" => strlen($item['scroller_message']));
            }
        }
        $data['cacheone']  = '';
        $data['cachetwo']  = '';
        echo "<pre>";
        echo print_r($data,true);
        echo "</pre>";
        exit;
        
        $this->output->set_content_type('text/xml');
        $this->output->set_header('Content-Type: text/xml; charset="utf-8"');
        $this->load->view('news_scroller_xml_out', $data);
    }
    
    public function old_scroller($type, $category = NULL, $chunk = NULL, $channel = NULL) {
        
        // sample URI(s):
        // All Articles in All categories
        // http://feeds.abnetwork.com/assets/scroller/digichief/?location=HOSW00001-01DSVC
        // Only all of the Arts articles
        // http://feeds.abnetwork.com/assets/scroller/digichief/Art/?location=HOSW00001-01DSVC
        // Only the third iteration of each category
        // http://feeds.abnetwork.com/assets/scroller/digichief/all/3/?location=HOSW00001-01DSVC
        // Only the first iteration of Art category
        // http://feeds.abnetwork.com/assets/scroller/digichief/Art/1/?location=HOSW00001-01DSVC
        
        if (!is_null($channel)) {
            switch ($type) {
                case 'news':
                    $this->digichief($channel, $type, $category, $chunk);
                    break;
                case 'custom':
                    $this->custom_scroller($channel);
                    break;
                case 'social':
                    $this->social_scroller($channel);
                    break;
                default:
                    break;
            }
        }
    }
    
    public function testBadWords($channel) {
        $xml = $this->digichief($channel,"news","all",1);
        echo "<pre>";
        echo print_r($xml);
    }
    
    public function digichief($channel, $type, $category, $chunk) {
        $time = microtime();
        $time = explode(' ', $time);
        $time = $time[1] + $time[0];
        $start = $time;
        
        $categoryIcon = strtolower($category);
        $debug = '';
        $bad_words_array = $this->feeds_model->check_bad_word_array();
        $data = array();
        $xml = false;
        
        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
        
        $hash = md5($type.$category.$chunk);
        
        if (!$this->cache->get($hash)) {
            @$xml = simplexml_load_file($this->config->item('digichief_endpoint'));
            $this->cache->save($hash, @$xml->asXML(), 900);
            $data['cache'] = "False";
        } else {
            $xml = $this->cache->get($hash);
            @$xml = simplexml_load_string($xml);
            $data['cache'] = "True";
        }
        
        if (!$xml) {
            @$xml = simplexml_load_file($this->assetDataDirectory . 'news/DefaultNoSchema.xml');
        }
        
        // process each story check length and check bad word list
        // TODO we will need to move this filter to the XML loader when
        // we go live
        
        $items = array();
        $icons = array();
        $stringNode = array();
        $categoryCount = array();
        $bannedCount = 0;
        $categories = array();
        
        $xpath = $xml -> xpath("//news/category");
        
        while (list(, $node) = each($xpath)) {
            $stringNode = sprintf("%s", $node[0]);
            if ($category == 'all' || $category == Null) {
                array_push($categories, $stringNode);
            } elseif ($stringNode == $category) {
                array_push($categories, $stringNode);
            }
        }
        
        $categoryCount = array_count_values($categories);
        
        $total = array_sum($categoryCount);
        
        if (!$chunk) {
            $feedBlock = max($categoryCount) + 1;
        } else {
            $feedBlock = $chunk;
        };
        
        for ($i = 0; $i < $feedBlock; $i++) {
            foreach (array_unique($categories) as $iteration) {
                if ($chunk) {
                    $i = $chunk;
                }
                foreach ($xml->xpath ( "//news[category='$iteration'][$i]" ) as $stories) {
                    $banned = FALSE;
                    $words = explode(" ", $stories->item);
                    foreach (array_unique($words) as $checkTarget) {
                        if (strlen($checkTarget) >= 3) {
                            $needle = array(',',':','-','!','?','.',';','\'');
                            if (in_array(str_replace($needle, "", strtolower($checkTarget)), $bad_words_array)) {
                                $banned = TRUE;
                                $bannedCount++;
                                break;
                            }
                        }
                    }
                    
                    if (!$banned) {
                        if (strlen($stories->item) < '150' && strlen($stories->item) > '75' && !in_array($iteration, $this->config->item('banned_categories'))) {
                            $iteration = str_replace(" ", "", strtolower($iteration));
                            array_push($items, $stories->item);
                            
                            if (in_array($iteration, $this->config->item('rss_icons'))) {
                                array_push($icons, $iteration);
                            } else {
                                array_push($icons, $this->config->item('rss_default_icon'));
                            }
                        } else {
                            $bannedCount++;
                        }
                    }
                }
            }
        }
        $debug = 'Banned Categories: ' . var_export($this->config->item('banned_categories'), TRUE);
        
        $data['debug'] = $debug;
        $data['hash'] = $hash;
        $data['memoryUsage'] = $this->benchmark->memory_usage();
        
        $time = microtime();
        $time = explode(' ', $time);
        $time = $time[1] + $time[0];
        $finish = $time;
        $total_time = round(($finish - $start), 4);
        $data['execution_time'] = $total_time;
        
        $data['execTime'] = $this->benchmark->elapsed_time();
        $data['iconLocation'] = $this->assetLocalIconDirectory;
        $data['theme'] = $this->theme('scroll1',true,$channel);
        $data['count'] = count(array_unique($items));
        $data['banned'] = $bannedCount;
        $data['duplicates'] = count($items) - count(array_unique($items));
        $data['categories'] = array_unique($categories);
        $data['total'] = $total;
        $data['query'] = array_unique($items);
        $data['icon'] = $icons;
        
        return $data;
        //$this->load->view('news_scroller_xml_out', $data);
    }
    
    public function data_digichief($channel, $type, $category, $chunk) {
        $categoryIcon = strtolower($category);
        $debug = '';
        $bad_words_array = $this->feeds_model->check_bad_word_array();
        
        $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));
        
        $hash = md5($type . $category . $chunk);
        
        if (!$this->cache->get($hash)) {
            @$xml = simplexml_load_file($this->config->item('digichief_endpoint'));
            $this->cache->save($hash, @$xml->asXML(), $this->config->item('rss_cache'));
            $data['cache'] = "False";
        } else {
            $xml = $this -> cache -> get($hash);
            @$xml = simplexml_load_string($xml);
            $data['cache'] = "True";
        }
        
        if (!$xml) {
            @$xml = simplexml_load_file($this->assetDataDirectory . 'news/DefaultNoSchema.xml');
        }
        
        // process each story check length and check bad word list
        // TODO we will need to move this filter to the XML loader when
        // we go live
        
        $items = array();
        $icons = array();
        $stringNode = array();
        $categoryCount = array();
        $bannedCount = 0;
        $categories = array();
        
        $xpath = $xml -> xpath("//news/category");
        
        while (list(, $node) = each($xpath)) {
            $stringNode = sprintf("%s", $node[0]);
            if ($category == 'all' || $category == Null) {
                array_push($categories, $stringNode);
            } elseif ($stringNode == $category) {
                array_push($categories, $stringNode);
            }
        }
        
        $categoryCount = array_count_values($categories);
        
        $total = array_sum($categoryCount);
        
        if (!$chunk) {
            $feedBlock = max($categoryCount) + 1;
        } else {
            $feedBlock = $chunk;
        };
        
        for ($i = 0; $i < $feedBlock; $i++) {
            foreach (array_unique($categories) as $iteration) {
                if ($chunk) {
                    $i = $chunk;
                }
                foreach ($xml->xpath ( "//news[category='$iteration'][$i]" ) as $stories) {
                    $banned = FALSE;
                    $words = explode(" ", $stories->item);
                    foreach (array_unique($words) as $checkTarget) {
                        if (strlen($checkTarget) >= 3) {
                            $needle = array(',',':','-','!','?','.',';','\'');
                            if (in_array(str_replace($needle, "", strtolower($checkTarget)), $bad_words_array)) {
                                $banned = TRUE;
                                $bannedCount++;
                                break;
                            }
                        }
                    }
                    
                    if (!$banned) {
                        if (strlen($stories->item) < '150' && strlen($stories->item) > '75' && !in_array($iteration, $this->config->item('banned_categories'))) {
                            $iteration = str_replace(" ", "", strtolower($iteration));
                            array_push($items, $stories->item);
                            
                            if (in_array($iteration, $this->config->item('rss_icons'))) {
                                array_push($icons, $iteration);
                            } else {
                                array_push($icons, $this->config->item('rss_default_icon'));
                            }
                        } else {
                            $bannedCount++;
                        }
                    }
                }
            }
        }
        $debug = 'Banned Categories: ' . var_export($this->config->item('banned_categories'), TRUE);
        
        $data['debug'] = $debug;
        $data['hash'] = $hash;
        $data['memoryUsage'] = $this->benchmark->memory_usage();
        $data['execTime'] = $this->benchmark->elapsed_time();
        $data['iconLocation'] = $this->assetLocalIconDirectory;
        $data['theme'] = $this->theme('scroll1',true,$channel);
        $data['count'] = count(array_unique($items));
        $data['banned'] = $bannedCount;
        $data['duplicates'] = count($items) - count(array_unique($items));
        $data['categories'] = array_unique($categories);
        $data['total'] = $total;
        $data['query'] = array_unique($items);
        $data['icon'] = $icons;
        
        echo "<pre>";
        echo print_r($data, true);
        echo "</pre>";
        
        //$this->load->view('news_scroller_xml_out', $data);
    }
    
    public function menuboard($channel = null, $json = null) {
        if (!is_null($channel)) {
            $this->load->model('remote_model');
            if (!empty($json) && $channel == 'GBSW2053302MBHCO') {
                $data['query'] = $this->remote_model->get_menuboard('CHOT4444407MBHCL');
            } else {
                $data['query'] = $this->remote_model->get_menuboard($channel);
            }
            /*echo "<pre>";
             echo print_r($data, true);
             echo "</pre>";*/
            //$this->output->clear_all_cache();
            if (!empty($json)) {
                //echo print_r($data, true);
                $xml = $this->load->view('dtap_menuboard_xml_out', $data, true);
                $workXML = trim(preg_replace('/\t+/', '', $xml));
                $workXML = trim(preg_replace('/\n+/', '', $workXML));
                $tempXML = simplexml_load_string($workXML);
                header('Access-Control-Allow-Origin: *');
                header('Content-Type: application/json;');
                echo json_encode($tempXML);
            } else {
                $this->output->set_header('Content-Type: text/xml; charset="utf-8"');
                if (substr($channel,4,5) == "20001" || substr($channel,4,5) == "91002" || $channel == "FLSW1234501MBHCL" || $channel == "GKSL9100202MBHCL" || $channel == "CHSL9100202MBHCL") {
                    $this->load->view('dtap_menuboard_xml_out', $data);
                } else {
                    $this->load->view('dtap_menuboard_xml_out', $data);
                }
            }
        } else {
            return false;
        }
    }
    
    public function dtv($channel = null, $json = null) {
        if (!is_null($channel)) {
            $this->load->model('remote_model');
            $data['query'] = $this->remote_model->get_dtv($channel);
            if (!empty($json)) {
                if (is_array($data['query'])) {
                    $workData = $data['query'][0];
                    $accountNumber = substr($workData->channel,4,5);
                    $pathParts = pathinfo($workData->dtv_chrome_logo);
                    $basePath = $pathParts['dirname'];
                    $workData->lightlogo = $basePath."/".$accountNumber."_ABN_LIVE_LOGO_WHITE.png";
                    $workData->darklogo = $basePath."/".$accountNumber."_ABN_LIVE_LOGO.png";
                    $data['query'][0] = $workData;
                }
                $xml = $this->load->view('dtv_xml_to_json_out', $data, true);
                $workXML = trim(preg_replace('/\t+/', '', $xml));
                $workXML = trim(preg_replace('/\n+/', '', $workXML));
                $tempXML = simplexml_load_string($workXML, null, LIBXML_NOCDATA);
                header('Access-Control-Allow-Origin: *');
                header('Content-Type: application/json;');
                echo json_encode($tempXML);
            } else {
                $this->output->set_header('Content-Type: text/xml; charset="utf-8"');
                $this->load->view('dtv_xml_out', $data);
            }
        } else {
            return false;
        }
    }
    
    public function logo($channel = null, $json = null) {
        if (!is_null($channel)) {
            $json = true;
            $this->load->model('remote_model');
            $data['query'] = $this->remote_model->get_dtv($channel);
            if (!empty($json)) {
                if (is_array($data['query'])) {
                    $workData = $data['query'][0];
                    $accountNumber = substr($workData->channel,0,5);
                    $pathParts = pathinfo($workData->dtv_chrome_logo);
                    $basePath = $pathParts['dirname'];
                    $basePath = str_replace("/content/",getenv('HTTPS') . getenv('ABNCDN'), $basePath);
                    $workData->lightlogo = $basePath."/".$accountNumber."_ABN_LIVE_LOGO_WHITE.png";
                    $workData->darklogo = $basePath."/".$accountNumber."_ABN_LIVE_LOGO.png";
                    $data['query'][0] = $workData;
                }
                $xml = $this->load->view('logo_xml_to_json_out', $data, true);
                $workXML = trim(preg_replace('/\t+/', '', $xml));
                $workXML = trim(preg_replace('/\n+/', '', $workXML));
                $tempXML = simplexml_load_string($workXML, null, LIBXML_NOCDATA);
                header('Access-Control-Allow-Origin: *');
                header('Content-Type: application/json;');
                echo json_encode($tempXML);
            } else {
                $this->output->set_header('Content-Type: text/xml; charset="utf-8"');
                $this->load->view('dtv_xml_out', $data);
            }
        } else {
            return false;
        }
    }
    
    public function dtvversion2($channel = null, $json = null) {
        if (!is_null($channel)) {
            $this->load->model('remote_model');
            $data['query'] = $this->remote_model->get_dtv($channel);
            /*echo "<pre>";
             echo print_r($data, true);
             echo "</pre>";*/
            if (!empty($json)) {
                if (is_array($data['query'])) {
                    $workData = $data['query'][0];
                    $accountNumber = substr($workData->channel,4,5);
                    $pathParts = pathinfo($workData->dtv_chrome_logo);
                    $basePath = $pathParts['dirname'];
                    $workData->lightlogo = $basePath."/".$accountNumber."_ABN_LIVE_LOGO_WHITE.png";
                    $workData->darklogo = $basePath."/".$accountNumber."_ABN_LIVE_LOGO.png";
                    $data['query'][0] = $workData;
                }
                $xml = $this->load->view('dtv_xml_to_json_out', $data, true);
                $workXML = trim(preg_replace('/\t+/', '', $xml));
                $workXML = trim(preg_replace('/\n+/', '', $workXML));
                $tempXML = simplexml_load_string($workXML, null, LIBXML_NOCDATA);
                header('Access-Control-Allow-Origin: *');
                header('Content-Type: application/json;');
                echo json_encode($tempXML);
            } else {
                $this->output->set_header('Content-Type: text/xml; charset="utf-8"');
                $this->load->view('dtv_version2_xml_out', $data);
            }
        } else {
            return false;
        }
    }
    
    public function dtvcombined($channel = null) {
        if (!is_null($channel)) {
            $time = microtime();
            $time = explode(' ', $time);
            $time = $time[1] + $time[0];
            $start = $time;
            
            $this->load->model('remote_model');
            $data['query'] = $this->remote_model->get_dtv($channel);
            
            
            $data['scroller'] = $this->returnScroller($channel);
            $data['weather'] = $this->returnWeather('5day', $channel);
            
            $time = microtime();
            $time = explode(' ', $time);
            $time = $time[1] + $time[0];
            $finish = $time;
            $total_time = round(($finish - $start), 4);
            $data['execution_time'] = $total_time;
            
            $workView = $this->load->view('dtv_combined_xml_out', $data, true);
            $xml = simplexml_load_string($workView, 'SimpleXMLElement', LIBXML_NOCDATA);
            $json = json_encode($xml);
            
            $this->output->set_header("Access-Control-Allow-Origin: *");
            $this->output->set_header('Content-Type: application/json; charset="utf-8"');
            echo $json;
            //$array = json_decode($json, TRUE);
        } else {
            return false;
        }
    }
    
    public function anNotify($hyperionCode = null) {
        if (!is_null($hyperionCode)) {
            $this->load->model('feeds_model');
            $this->output->set_header('Content-type: application/json; charset="utf-8"');
            $notifications = $this->feeds_model->testANNotifications($hyperionCode);
            echo print_r($notifications,true);
        }
    }
    
    public function happyCustomer($accountNumber = null) {
        if (!is_null($accountNumber)) {
            $this->load->model('remote_model');
            $content = file_get_contents("https://abn-live.herokuapp.com/accounts/".substr($accountNumber,0,5)."/json_feed?items_count=5&photo_version=large");
            $data = json_decode($content,true);
            $channel = "FEED".$accountNumber."CHANNEL";
            $tempAccount = $this->remote_model->get_dtv($channel);
            //echo print_r($tempAccount,true);
            $tempPathParts = pathinfo($tempAccount[0]->dtv_chrome_logo);
            $data['dealer_logo'] = $tempPathParts['dirname']."/".$accountNumber."_ABN_LIVE_LOGO.png";
            $uri = "https://abn-live.herokuapp.com/api/accounts/".$accountNumber;
            
            $curl = curl_init($uri);
            curl_setopt_array($curl, array(
                CURLOPT_HTTPHEADER => array('Accept: application/vnd.abn_mobile.v2'),
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_VERBOSE => true,
                CURLOPT_USERAGENT => 'ABN Happy Customer Logo Request'
            ));
            $resp = curl_exec($curl);
            curl_close($curl);
            
            $result = json_decode($resp);
            
            if (json_last_error() === JSON_ERROR_NONE && property_exists($result, "logo")) {
                $workLogo = $result->logo->original;
            } else {
                $workLogo = "https://s3.amazonaws.com/abnlive/accounts/logos/original/b68427440f074f19e23a5f42935429d351f80781.png?1462563978";
            }
            if ($workLogo != '' && !is_null($workLogo)) {
                $remoteURL = $workLogo;
            } else {
                $remoteURL = "https://s3.amazonaws.com/abnlive/accounts/logos/original/b68427440f074f19e23a5f42935429d351f80781.png?1462563978";
            }
            $result = (array) $result;
            $data['dealer_logo'] = $remoteURL;
            
            $tempPosts = $data["posts"];
            unset($data["posts"]);
            $data['posts'] = $tempPosts;
            
            $this->output->set_header('Content-type: application/json; charset="utf-8"');
            echo json_encode($data);
        }
    }
    
    public function nfl() {
        $rawJSON = file_get_contents($this->config->item('digichief_nfl_json_endpoint'));
        $nflDataSecondary = json_decode($rawJSON, true);
        $rawJSON = $this->xml_to_json($this->config->item('digichief_nfl_xml_endpoint'));
        $nflData = json_decode($rawJSON, true);
        $checkFields = array('team','teamfullname','score','team record','st_time','st_period','TeamID');
        $fullGames = array();
        if (isset($nflData['score'])) {
            if (is_array($nflData['score'])) {
                if (isset($nflData['score']['team1'])) {
                    $tempData = $nflData['score'];
                    unset($nflData['score']);
                    $nflData['score'] = array();
                    $nflData['score'][] = $tempData;
                }
                foreach($nflData['score'] as $nflGame) {
                    $gameArray = array();
                    for ($i=1; $i<3; $i++) {
                        foreach($checkFields as $field) {
                            if ($i == 1) {
                                $prefix = 'home_';
                            } else {
                                $prefix = 'visitor_';
                            }
                            $testField = '';
                            if (preg_match('/\s/',$field)) {
                                $testField = str_replace(' ', $i, $field);
                            } elseif (preg_match('/_/',$field)) {
                                $testField = str_replace('_', '', $field);
                            } else {
                                $testField = $field.$i;
                            }
                            if (isset($nflGame[$testField])) {
                                $fixedField = str_replace($i,'',$testField);
                                $fixedField = str_replace('TeamID','abbreviation',$fixedField);
                                $nflGame[$testField] = str_replace('NFL-','',$nflGame[$testField]);
                                $gameArray[$prefix.$fixedField] = $nflGame[$testField];
                            }
                        }
                    }
                    $fullGames[] = $gameArray;
                }
            }
        }
        /*echo "<pre>";
         echo print_r($fullGames);
         exit;*/
        $gamesData = array();
        
        if (is_array($fullGames)) {
            $gamesData['current'] = array();
            $gamesData['future'] = array();
            foreach($fullGames as $nflKey => &$nflGame) {
                unset($nflGame['visitor_sttime']);
                unset($nflGame['visitor_stperiod']);
                if (isset($nflGame['home_sttime'])) {
                    if (is_array($nflGame['home_sttime'])) {
                        if (is_array($nflGame['home_score'])) {
                            unset($nflGame['home_score']);
                        }
                        unset($nflGame['home_sttime']);
                        if (is_array($nflGame['visitor_score'])) {
                            unset($nflGame['visitor_score']);
                        }
                        $nflGame['game_time'] = $nflGame['home_stperiod'];
                        unset($nflGame['home_stperiod']);
                        $gamesData['future'][] = $nflGame;
                    } else {
                        $nflGame['game_quarter'] = $nflGame['home_stperiod'];
                        unset($nflGame['home_stperiod']);
                        $nflGame['game_time'] = $nflGame['home_sttime'];
                        unset($nflGame['home_sttime']);
                        $gamesData['current'][] = $nflGame;
                    }
                }
            }
            usort($gamesData['future'], function ($a, $b) {
                return $a['game_time'] - $b['game_time'];
            });
        }
        $this->output->set_header("Access-Control-Allow-Origin: *");
        $this->output->set_header('Content-Type: application/json; charset="utf-8"');
        echo json_encode($gamesData);
    }
    
    private function fixModelCaps($oemModel) {
        $exceptions = array('crv', 'lx', 'exl', 'hrv', 'ex', 'rt', 'rtle', 'amg');
        $result = "";
        $words = explode(" ", $oemModel);
        foreach ($words as $word) {
            if (in_array($word, $exceptions)) {
                $result .= " ".strtoupper($word);
            } else {
                $result .= " ".ucwords($word);
            }
        }
        if (strpos($result, 'Plug In') !== FALSE) {
            $result = str_replace('Plug In','Plug-In', $result);
        }
        return trim($result);
    }
    
    public function myeco($feedType = null, $widgetOEM = null, $locationID = null) {
        if (!is_null($feedType) && !is_null($widgetOEM) && !is_null($locationID)) {
            $feedType = strtoupper($feedType);
            $locationID = strtoupper($locationID);
            $orientation = strtoupper(substr($locationID,13,1));
            $oemCode = strtoupper(substr($locationID,0,2));
            
            $this->load->model('myeco_model');
            $media = $this->myeco_model->get_media($feedType, $orientation, $widgetOEM, $locationID);
            if (is_array($media)) {
                $models = [];
                $modelMedia = [];
                foreach($media as $item) {
                    $mediaKey = preg_replace( "/[^a-z]/i", "", $item->oemModel.$item->oemTrim);
                    $modelMedia[$mediaKey][] = $item->path.$item->name;
                }
                foreach($media as $item) {
                    if (strlen($item->oemTrim) < 1) {
                        $item->oemTrim = '';
                    }
                    $mediaKey = preg_replace( "/[^a-z]/i", "", $item->oemModel.$item->oemTrim);
                    $models[$mediaKey] = array(
                        'year' => $item->oemYear,
                        'model' => $item->oemModel,
                        'sub-model' => $item->oemTrim,
                        'media' => array((object)$modelMedia[$mediaKey])
                    );
                }
            }
            $media = array();
            $media['myeco'.$feedType.'Models'] = array_values($models);
            header('Content-Type: application/json');
            echo json_encode($media);
        }
        return false;
    }
}
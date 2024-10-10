<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Remote_model extends CI_Model 
{

	function __construct() 
	{
		parent::__construct();
	}
	
	public function get_account_details($channel = NULL) {
		if (!is_null($channel)) {
			$url = getenv('HTTPS'). getenv('REMOTE_BETA_ENDPOINT') . "/source/get_account_details/";
			$account_id = substr($channel,4,5);

			$feedsDB = $this->load->database('default', TRUE);
			
			$sql = "SELECT accountName, accountID AS account_c, accountZip AS account_zip, accountCity AS physical_address_city_c FROM cacheAccount where accountID = '".$account_id."'";
			
			$query = $feedsDB->query($sql);
			
			if ($query->num_rows() > 0) {
			    $results = $query->result_object();
			    return $results[0];
			}
			return false;
			exit;
			
			$json = file_get_contents($url.$account_id."/abn-scala-request-".strrev($account_id));
			//$json = file_get_contents('http://scalafeeds.abnetwork.com/aggregator/portalFeeds/account/'.$channel);
			$obj = json_decode($json);
			return $obj;
		} else {
			return false;
		}
	}
	
	public function get_appointments($appointmentType = NULL, $accountNumber = NULL) {
		if (!is_null($appointmentType) && !is_null($accountNumber)) {
			$url = getenv('HTTPS') . getenv('APPOINTMENT_ENDPOINT') . "/index.php/api/".$appointmentType."/".$accountNumber;
				
			$json = file_get_contents($url);
			$obj = json_decode($json);
			return $obj;
		} else {
			return false;
		}
	}

	public function get_theme($channel = NULL) {
		if (!is_null($channel)) {
		    $feedsDB = $this->load->database('default', TRUE);
		    
		    $sql = "SELECT channelTheme FROM cacheTheme WHERE channelName = '".$channel."' LIMIT 1";
		    $query = $feedsDB->query($sql);
		    
		    if ($query->num_rows() > 0) {
		        $results = $query->result_object();
		        return $results[0]->channelTheme;
		    } else {
		        return "blue";
		    }
		    return false;
		    exit;
		    
		    $url = getenv('HTTPS'). getenv('REMOTE_BETA_ENDPOINT') . "/source/get_theme/";
			$account_id = substr($channel,4,5);
				
			$json = file_get_contents($url.$channel."/abn-scala-request-".strrev($channel));
			//$json = file_get_contents('http://scalafeeds.abnetwork.com/aggregator/portalFeeds/theme/'.$channel);
			
			$obj = json_decode($json);
			return $obj;
		} else {
			return false;
		}
	}
	
	public function get_dtv($channel = NULL) {
		if (!is_null($channel)) {
			if ($channel == "CHSW9100201DTHCL") {
				$dtvTimestamp = date('Y-m-d H:i:s');
				$json = '[{"id":"00","channel":"CHSW9100201DTHCL","dtv_background":"\/content\/DEALER_TV_CONTENT\/DTV_ASSETS\/THEMES\/ground_gunmetal.png","dtv_frame":"\/content\/DEALER_TV_CONTENT\/DTV_ASSETS\/THEMES\/frame_gunmetal.png","dtv_chrome_logo":"\/content\/DEALER_SPECIFIC_CONTENT\/HOMETOWN_CHEVROLET_91002\/ASSETS\/91002_CHROME.png","dtv_cube":"\/content\/DEALER_SPECIFIC_CONTENT\/HOMETOWN_CHEVROLET_91002\/ASSETS\/91002_CUBE.mp4","timestamp":"'.$dtvTimestamp.'"}]';
			} else {
				$url = getenv('HTTPS'). getenv('REMOTE_BETA_ENDPOINT') . "/source/get_dtv/";
				$account_id = substr($channel,4,5);
		
				$json = file_get_contents($url.$channel."/abn-scala-request-".strrev($channel));
				//$json = file_get_contents('http://scalafeeds.abnetwork.com/aggregator/portalFeeds/dtv/'.$channel);
			}
			$obj = json_decode($json);
			return $obj;
		} else {
			return false;
		}
	}
	
	public function get_menuboard($channel = NULL) {
		if (!is_null($channel)) {
			if (substr($channel,4,5) == "20001" || substr($channel,4,5) == "12345" || $channel == "GKSL9100202MBHCL" || $channel == "CHSL9100202MBHCL") {
				$url = getenv('HTTPS') . getenv('GM_ENDPOINT')."/source/get_menuboard/";
			} else {
				$url = getenv('HTTPS'). getenv('REMOTE_BETA_ENDPOINT')."/source/get_menuboard/";
			}
			$account_id = substr($channel,4,5);
	
			$json = file_get_contents($url.$channel."/abn-scala-request-".strrev($channel));
			//$json = file_get_contents('http://scalafeeds.abnetwork.com/aggregator/portalFeeds/menuboard/'.$channel);
			$obj = json_decode($json, true);
			return $obj;
		} else {
			return false;
		}
	}
	
	public function get_custom_scroller($channel = NULL) {
		if (!is_null($channel)) {
		    $feedsDB = $this->load->database('default', TRUE);
		    
		    $sql = "SELECT scrollerID AS id,
                accountID AS account,
                channelName AS channel,
                scrollerType AS scroller_type,
                scrollerMessage AS scroller_message,
                scrollerStart AS scroller_start,
                scrollerEnd AS scroller_end
            FROM cacheScroller
            WHERE ((accountID = '".substr($channel,4,5)."' AND channelName = 'ALL') OR (channelName = '".$channel."'))
    		AND ((DATE(now()) BETWEEN DATE(scrollerStart) AND DATE(scrollerEnd))
    		OR (DATE(now()) >= DATE(scrollerStart) AND DATE(scrollerEnd) = '0000-00-00')
    		OR (DATE(scrollerStart) = '0000-00-00' AND DATE(now()) < DATE(scrollerEnd))
    		OR (DATE(scrollerStart) = '0000-00-00' AND DATE(scrollerEnd) = '0000-00-00'))";
		    
		    $query = $feedsDB->query($sql);
		    
		    if ($query->num_rows() > 0) {
		        $results = $query->result_array();
		        return $results;
		    }
		    return false;
		    exit;
		    
			if ($channel == "CHSW9100201DTHCL") {
				$url = getenv('HTTPS') . getenv('GM_ENDPOINT')."/source/get_scroller/";
			} else {
				$url = getenv('HTTPS'). getenv('REMOTE_BETA_ENDPOINT')."/source/get_scroller/";
			}
			$account_id = substr($channel,4,5);
	
			$json = file_get_contents($url.$channel."/abn-scala-request-".strrev($channel));
			//$json = file_get_contents('http://scalafeeds.abnetwork.com/aggregator/portalFeeds/scroller/'.$channel);
			$obj = json_decode($json, true);
			return $obj;
		} else {
			return false;
		}
	}
}

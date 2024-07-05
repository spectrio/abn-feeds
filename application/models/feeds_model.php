<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Feeds_model extends CI_Model 
{

	function __construct() 
	{
		parent::__construct();
	}

	private function CallESBAPI($method, $url, $queryData = NULL, $postData = NULL, $token = NULL)
	{
		$api_url = getenv('ABNSBS') . "/api/v2/files/service_ros/";
		$query = http_build_query($queryData);
		$url = $api_url.$url."?".$query;
		//error_log('ESBAPI URL: '.$url, 0);
			
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$curl_headers = array();
		$curl_headers[] = "Content-type: application/json";
		if (is_null($token)) {
			$curl_headers[] = "X-DreamFactory-Api-Key: b5cb82af7b5d4130f36149f90aa2746782e59a872ac70454ac188743cb55b0ba";
		} else {
			$curl_headers[] = "X-DreamFactory-Api-Key: ".$token;
		}
		curl_setopt($curl, CURLOPT_HTTPHEADER, $curl_headers);
	
		if ($method == "POST") {
			if (!is_null($postData)) {
				$content = json_encode($data);
			} else {
				$content = array();
			}
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $content);
		}
		if ($method == "PUT") {
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
			curl_setopt($curl, CURLOPT_POSTFIELDS, $content);
		}
		if ($method == "DELETE") {
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
		}
	
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	
		$json_response = curl_exec($curl);
		
		error_log($json_response);
	
		$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	
		if ($status != 201 && $status != 200 && $status != 203 && $status != 204) {
			error_log("Error in CURL call: ".$status." - JSON Response: ".$json_response." - URL: ".$url);
			//redirect('error');
			//die("Error: call to URL $url failed with status $status, response $json_response, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl)."<br />".print_r($content,true)." - ".$token." - ".$url);
		}
	
		curl_close($curl);
	
		$response = json_decode($json_response, true);
	
		return $response;
	}
	
	private function TestESBAPI($method, $url, $queryData = NULL, $postData = NULL, $token = NULL)
	{
		$api_url = getenv('ABNSBS'). "/api/v2/files/service_ros/";
		$query = http_build_query($queryData);
		$url = $api_url.$url."?".$query;
		//error_log('ESBAPI URL: '.$url, 0);
		
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$curl_headers = array();
		$curl_headers[] = "Content-type: application/json";
		if (is_null($token)) {
			$curl_headers[] = "X-DreamFactory-Api-Key: b5cb82af7b5d4130f36149f90aa2746782e59a872ac70454ac188743cb55b0ba";
		} else {
			$curl_headers[] = "X-DreamFactory-Api-Key: ".$token;
		}
		curl_setopt($curl, CURLOPT_HTTPHEADER, $curl_headers);
		
		if ($method == "POST") {
			if (!is_null($postData)) {
				$content = json_encode($data);
			} else {
				$content = array();
			}
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $content);
		}
		if ($method == "PUT") {
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
			curl_setopt($curl, CURLOPT_POSTFIELDS, $content);
		}
		if ($method == "DELETE") {
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
		}
		
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		
		$json_response = curl_exec($curl);
		
		error_log($json_response);
		
		$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		
		if ($status != 201 && $status != 200 && $status != 203 && $status != 204) {
			error_log("Error in CURL call: ".$status." - JSON Response: ".$json_response." - URL: ".$url);
			//redirect('error');
			//die("Error: call to URL $url failed with status $status, response $json_response, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl)."<br />".print_r($content,true)." - ".$token." - ".$url);
		}
		
		curl_close($curl);
		
		$response = json_decode($json_response, true);
		
		return $response;
	}
	
	function check_bad_word($string) {
		$this->db->select('*');
		$this->db->from('bad_words');
		$this->db->where('word', $string);
		$query = $this->db->get();
		$result = $query->result();
	
		return ($result ? FALSE : TRUE);
	}
	
	function check_bad_word_array() {
		$output = array();
		$query = $this->db->query("SELECT LCASE(word) FROM bad_words");
	
		if ($query->num_rows() > 0) {
		    return $query->result_array();
		} else {
			return;
		}
		return $output;
	}
	
	
	function check_bad_word_mini($string) {
		$this->db->select('*');
		$this->db->from('bad_words_mini');
		$this->db->where('word', $string);
		$query = $this->db->get();
		$result = $query->result();
	
		return ($result ? FALSE : TRUE);
	}
	
	
	function check_bad_word_mini_array() {
		$output = array();
		$query = $this->db->query("SELECT LCASE(word) FROM bad_words_mini");
		
		if ($query->num_rows() > 0) {
		    return $query->result_array();
		} else {
			return;
		}
		return $output;
	}
	
	function set_notification($data) {
		$feedsDB = $this->load->database('default', TRUE);
		if ($this->db->insert_batch('notification_cache', $data)) {
			return true;
		} else {
			return false;
		}
	}
	
	function get_notifications($location,$remote_ip) {
		$feedsDB = $this->load->database('default', TRUE);
	
		$sql = "SELECT * FROM notification_cache WHERE channel_id = '".$location."' AND requested = 0 ORDER BY entry_datetime ASC LIMIT 1";
	
		$query = $feedsDB->query($sql);
	
		if ($query->num_rows() > 0) {
			$results = $query->result();
			$sql = "UPDATE notification_cache SET requested = 1, ipaddress = '".$remote_ip."' WHERE id = ".$results[0]->id;
			$uQuery = $feedsDB->query($sql);
			return $results;
		} else {
			return FALSE;
		}
	}
	
	function set_pttv($data) {
		$feedsDB = $this->load->database('default', TRUE);
		if ($this->db->insert('pttv_requests', $data)) {
			return true;
		} else {
			return false;
		}
	}
	
	function get_pttv($location, $remote_ip) {
		$feedsDB = $this->load->database('default', TRUE);
	
		$sql = "SELECT * FROM pttv_requests WHERE location_id = '".$location."' AND requested = 0 ORDER BY timestamp ASC LIMIT 1";
	
		$query = $feedsDB->query($sql);
	
		if ($query->num_rows() > 0) {
		    $results = $query->result();
			$sql = "UPDATE pttv_requests SET requested = 1, remote_ip = '".$remote_ip."', time_modified = NULL WHERE location_id = '".$location."' AND id = '".$results[0]->id."'";
			$uQuery = $feedsDB->query($sql);
			return $results;
		} else {
			return FALSE;
		}
	}
	
	function getANNotifications($hyperionCode = null) {
		//$hyperionCode = "2045";
		if (!is_null($hyperionCode)) {
			$results = $this->callESBAPI("GET","",array("last" => "1"));
			if (is_array($results)) {
				if (array_key_exists("name",$results)) {
					$results = $this->callESBAPI("GET", $results["name"], array("hyperion" => $hyperionCode),"", "b5cb82af7b5d4130f36149f90aa2746782e59a872ac70454ac188743cb55b0ba");
					if (is_array($results)) {
						$sendArray = array();
						foreach ($results as $notify) {
							$statusText = strtolower(trim($notify[9]));
							$statusCode = strtoupper(trim($notify[14]));
							if ($statusCode == "C95" || $statusCode == "H97" || $statusCode == "H98" || $statusCode == "I97") {
							//if (strtolower($statusText) == "work completed. see advisor" || strtolower($statusText) == "see advisor") {
								$addNotification = false;
								$feedsDB = $this->load->database('default', TRUE);
								$sql = "SELECT * FROM notification_display_count WHERE notificationID = '".trim($notify[2])."' ORDER BY timeCreated DESC LIMIT 1";
								$query = $feedsDB->query($sql);
								if ($query->num_rows() > 0) {
									$workArray = $query->result_array();
									foreach($workArray as $key => $row) {
										if ($row["displayCount"] < 12) {
											$row["displayCount"]++;
											$insertArray = array();
											$insertArray[] = $row;
											$feedsDB->update_batch('notification_display_count', $insertArray, 'id');
											$addNotification = true;
										}
									}
								} else {
									$workArray = array();
									$workArray[] = array("id" => null, "notificationID" => $notify[2], "displayCount" => 1, "timeCreated" => null);
									$feedsDB->insert_batch('notification_display_count', $workArray);
									$addNotification = true;
								}
								if ($addNotification) {
									$objNotify = new stdClass();
									$objNotify->content = ucfirst($notify[12]).' '.strtoupper(substr($notify[13],0,1)).'., please see your Service Advisor.';
									$objNotify->entry_datetime = date("Y-m-d H:i:s");
									$sendArray[] = $objNotify;
									$addNotification = false;
								}
							}
						}
						$results = $sendArray;
					}
				}
			}
			return $results;
		} else {
			return array();
		}
	}
	
	function testANNotifications($hyperionCode = null) {
		if (!is_null($hyperionCode)) {
			$results = $this->testESBAPI("GET","",array("last" => "1"));
			if (is_array($results)) {
				if (array_key_exists("name",$results)) {
					$results = $this->testESBAPI("GET", $results["name"], array("hyperion" => $hyperionCode),"", "b5cb82af7b5d4130f36149f90aa2746782e59a872ac70454ac188743cb55b0ba");
					return $results;
				}
			} else {
				return $results;
			}
		} else {
			$hyperionCode = '2045';
		}
		
	}
}

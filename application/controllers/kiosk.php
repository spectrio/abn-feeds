<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Kiosk extends CI_Controller {

	public function __construct() {
		parent::__construct();
		$this->data['feeds_server'] = "http://127.0.0.1"; //"http://scalafeeds.abnetwork.com";
	}
	
	public function index()
	{
		$this->load->view('feeds');
	}

	public function pttv($channelID = null) {
		$data = array();
		$data["channelID"] = $channelID;
		$this->load->view('pttv',$data);
	}
	
	function livetv($type = null) {
		if (!is_null($type)) {
			$channelID = $this->input->post('location_id');
			date_default_timezone_set('US/Eastern');
			$this->load->model('remote_model');
			$account_info = $this->remote_model->get_account_details($channelID);
			$account_tz = $account_info->timezone_c;
			if ($account_tz == "pleasechoose") {
				$account_tz = "EST";
			}

			switch (strtolower($account_tz)) {
				case "est":
					$account_tz_offset = 0;
					break;
				case "cst":
					$account_tz_offset = 1;
					break;
				case "mst":
					$account_tz_offset = 2;
					break;
				case "pst":
					$account_tz_offset = 3;
					break;
			}
	
			switch (strtolower($type)) {
				case "stop":
					$location_id = $this->input->post('location_id');
					$start_tv = "STOP";
					$end_tv = "STOP";
					$hour_offset = null;
					$start_hour = null;
					$end_hour = null;
					$end_halfhour = null;
						
					$feeds_start = "STOP";
					$feeds_end = "STOP";
					$feeds_trigger = $this->data["feeds_server"]."/pttv/set/".$location_id."/".$feeds_start."/".$feeds_end;
						
					$feeds_result = $this->feeds_trigger($feeds_trigger);
	
					echo $feeds_result;
					break;
				case "scheduled":
				case "timed":
					$post_start = $this->input->post('start_tv');
					$location_id = $this->input->post('location_id');
					$start_tv = date("U",strtotime($post_start) - 3600 - (3600 * $account_tz_offset));
					$end_tv = date("U",strtotime($this->input->post('end_tv')) - 3600 - (3600 * $account_tz_offset));
					$hour_offset = intval($this->input->post('hour_offset'));
					$customer_offset = $this->input->post('customer_offset');
	
					/*if ($hour_offset > 0) {
					 $start_secs = strtotime($post_start);
					 $start_secs = $start_secs + (3600 * $hour_offset);
					 $end_tv = date("U",$start_secs);
					}*/
					$end_hour = $end_tv - ($end_tv % 1800) + 3600;
					$end_halfhour = $end_hour + 1800;
					$start_hour = gmdate("c",$start_tv);
					$end_hour = gmdate("c",$end_hour);
					$end_halfhour = gmdate("c",$end_halfhour);
	
					$feeds_start = date("Hi",strtotime($start_hour));
					$feeds_end = date("Hi",strtotime($end_hour));
					$feeds_trigger = $this->data["feeds_server"]."/pttv/set/".$location_id."/".$feeds_start."/".$feeds_end;

					$feeds_result = $this->feeds_trigger($feeds_trigger);

					echo $feeds_result;
					break;
				default:
					echo false;
					break;
			}
		}
	}

	private function feeds_trigger($feeds_trigger = NULL, $data = NULL, $post = false) {
		if (!is_null($feeds_trigger)) {
			$curl = curl_init();
			if ($post) {
				curl_setopt_array($curl, array(
						CURLOPT_RETURNTRANSFER => 1,
						CURLOPT_URL => $feeds_trigger,
						CURLOPT_USERAGENT => 'Customer Portal Feeds Trigger',
						CURLOPT_POST => 1,
						CURLOPT_POSTFIELDS => $data
				));
			} else {
				curl_setopt_array($curl, array(
						CURLOPT_RETURNTRANSFER => 1,
						CURLOPT_URL => $feeds_trigger,
						CURLOPT_USERAGENT => 'Customer Portal Feeds Trigger'
				));
			}
			// Send the request & save response to $resp
			$resp = curl_exec($curl);
			// Close request to clear up some resources
			curl_close($curl);
			return $resp;
		}
	}
}

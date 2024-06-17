<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/* Migration Notes:
 * 
 * The following files are required:
 * 
 * config/
 * 			bitauth.php ( needs to be autoloaded )
 * 				
 * controllers/
 * 
 * 			notification.php
 * 
 * libraries/
 * 
 * 			phpass.php
 * 			Bitauth.php
 * 			Crypt_Blowfish.php
 * 			SugarEncrypt.php
 * 			SugarDefaultKey.php
 * 
 * views/
 * 	
 * 			notification/*
 * 			authenticate/login.php
 * 
 * 
 * models/
 * 
 * 			notifications_model.php
 * 			crm.php
 */


class Notification extends CI_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->helper('url');
		$this->load->helper('date');
		$this->load->model('feeds_model');
	}

	public function set($location = NULL, $message = NULL) {
		if (!is_null($location) && !is_null($message)) {
			$data = array(
				'content' => urldecode($message),
				'entry_datetime' => date('Y-m-d H:i:s', time()),
				'channel_id' => $location,
				'display_count' => 3,
				'display_interval' => 10,
				'account_id' => substr($location,4,5),
				'member_id' => 1,
				'username' => "Development Test",
				'ipaddress' => "0.0.0.0",
				'requested' => 0
			);
			$batch_insert = array();
			for ($i=1; $i<4; $i++) {
				array_push($batch_insert,$data);
			}
			if ($this->feeds_model->set_notification($batch_insert)) {
				$data["result"] = true;
			} else {
				$data["result"] = false;
			}
			echo json_encode($data);
		}
	}
	
	public function portal_set($location=NULL) {
		if (!is_null($location) && !is_null($this->input->post('message'))) {
			$data = array(
					'content' => urldecode($this->input->post('message')),
					'entry_datetime' => date('Y-m-d H:i:s', time()),
					'channel_id' => $location,
					'display_count' => 3,
					'display_interval' => 10,
					'account_id' => substr($location,4,5),
					'member_id' => 1,
					'username' => "Development Test",
					'ipaddress' => "0.0.0.0",
					'requested' => 0
			);
			$batch_insert = array();
			for ($i=1; $i<7; $i++) {
				array_push($batch_insert,$data);
			}
			if ($this->feeds_model->set_notification($batch_insert)) {
				$data["result"] = true;
			} else {
				$data["result"] = false;
			}
			echo json_encode($data);
		}
	}
}


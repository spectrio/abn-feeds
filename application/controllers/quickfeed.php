<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Quickfeed extends CI_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->helper('url');
		$this->load->helper('date');
		$this->load->model('feeds_model');
	}

	function _remap($method, $params) {
		if (method_exists($this, $method)) {
			$this->$method();
		} else {
			$this->index($method, $params);
		}
	}
	
	public function index($channel = null, $customerStoreID = false) {
		if (!is_null($channel)) {
			$remote_ip = $_SERVER['REMOTE_ADDR'];
			$data['remote_ip'] = $remote_ip;
			$data['notifies_checked'] = 'primary';
			$returnNotifies = $this->feeds_model->get_notifications($channel, $remote_ip);
			if (!is_null($customerStoreID)) {
				if (is_array($customerStoreID)) {
					if (count($customerStoreID) > 0) {
						$customerStoreID = $customerStoreID[0];
						if (strtolower(substr($customerStoreID,0,5)) == "anusa") {
							$hyperionCode = str_ireplace("ANUSA","",$customerStoreID);
							$data['notifies_checked'].= ' anusa';
							$anusaNotifications = $this->feeds_model->getANNotifications($hyperionCode);
							if (is_array($anusaNotifications)) {
								if (count($anusaNotifications) > 0) {
									foreach ($anusaNotifications as $anusaRow) {
										$returnNotifies[] = $anusaRow;
									}
								}
							}
						}
					}
				}
			}
			$data['notifications'] = $returnNotifies;
			$data['pttv'] = $this->feeds_model->get_pttv($channel,$remote_ip);
			$data['refreshchannel'] = false;
			//$data['refreshupdate'] = $this->feeds_model->get_refresh($id);
		}
		
		$this->output->set_header('Content-Type: text/xml; charset="utf-8"');
		$this->load->view('quickfeed_xml_out', $data);
		$this->output->set_header('Connection: close');
	}
}

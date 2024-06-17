<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Api extends CI_Controller {

	public function __construct() {

		parent::__construct();

		// Custom Globals
		// See config/feeds for custom configs

		/*$this->assetServer = $this->config->item('assetServer');
		$this->assetDirectory = $this->config->item('assetDirectory');
		$this->assetThemeDirectory = $this->config->item('assetThemeDirectory');
		$this->assetNetworksDirectory = $this->config->item('assetNetworksDirectory');
		$this->assetDataDirectory = $this->config->item('assetDataDirectory');
		$this->assetWeatherIconDirectory = $this->config->item('assetWeatherIconDirectory');

		$this->assetLocalThemeDirectory = $this->config->item('assetLocalThemeDirectory');
		$this->assetLocalMenuboardDirectory = $this->config->item('assetLocalMenuboardDirectory');
		$this->assetLocalIconDirectory = $this->config->item('assetLocalIconDirectory');
		$this->assetLocalDirectory = $this->config->item('assetLocalDirectory');*/

		// All Output from this controller is JSON

		//$this->output->set_header('Content-Type: text/xml; charset="utf-8"');

		// Codeigniter's profiling class - good for testing DB query loads and
		// script execution time - need to shut up the set_header XML or output is garbage
		// $this->output->enable_profiler(TRUE);
	}

	public function index()
	{
		$this->load->view('feeds');
	}
	
	public function appointments($appointmentType = NULL, $accountNumber = NULL) {
		if (!is_null($appointmentType) && !is_null($accountNumber)) {
			$this->load->model('remote_model');
			if (strtolower($appointmentType) == "sales" || strtolower($appointmentType) == "service") {
				$appointmentType = strtolower($appointmentType);
				if ($accountNumber == "88888") {
					$appointmentType = "sales";
					$accountNumber = "11205";
				}
				$appointments = $this->remote_model->get_appointments($appointmentType,substr($accountNumber,0,5));
				$apptReturn = array();
				$apptReturn["APPOINTMENTS"] = array();
				$apptReturn["APPOINTMENTS"]["APPOINTMENT"] = array();
				$this->output->set_header("Access-Control-Allow-Origin: *");
				$this->output->set_header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
				$this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate');
				$this->output->set_header('Cache-Control: post-check=0, pre-check=0', FALSE);
				$this->output->set_header('Pragma: no-cache');
				$this->output->set_header('Content-Type: application/json; charset="utf-8"');
				if (is_array($appointments)) {
					if (count($appointments) > 0) {
						foreach ($appointments as $appt) {
							$apptWork = array();
							$apptWork["FIRST_NAME"] = $appt->customer_first_name;
	  						$apptWork["FULL_NAME"] = $appt->customer_display_name;
	  						$apptWork["START_TIME"] = date("g:i A",strtotime($appt->start_datetime));
	  						$apptWork["AGENT_NAME"] = $appt->employee_display_name;
	  						if (!is_null($appt->vehicle_year) && $appt->vehicle_year != "") {
	  							if (!is_null($appt->vehicle_make) && $appt->vehicle_make != "") {
	  								if (!is_null($appt->vehicle_model) && $appt->vehicle_model != "") {
	  									$apptWork["YEAR"] = $appt->vehicle_year;
	  									$apptWork["MAKE"] = strtoupper($appt->vehicle_make);
	  									$apptWork["MODEL"] = strtoupper($appt->vehicle_model);
	  								}
	  							}
	  							
	  						}
	  						$apptReturn["APPOINTMENTS"]["APPOINTMENT"][] = $apptWork;
						}
						$json = json_encode($apptReturn);
						echo $json;
						exit;
					} else {
						$json = json_encode($apptReturn);
						echo $json;
						exit;
					}
				} else {
					$json = json_encode($apptReturn);
					echo $json;
					exit;
				}
			}
		}
		$this->load->view('feeds');
	}
	
	public function testappts($appointmentType = NULL, $accountNumber = NULL) {
		$this->load->model('remote_model');
		$appointments = $this->remote_model->get_appointments($appointmentType,substr($accountNumber,0,5));
		echo "<pre>".print_r($appointments,true)."</pre>";
		exit;
		if (!is_null($appointmentType) && !is_null($accountNumber)) {
			$this->load->model('remote_model');
			if (strtolower($appointmentType) == "sales" || strtolower($appointmentType) == "service") {
				$appointments = $this->remote_model->get_appointments($appointmentType,substr($accountNumber,0,5));
				echo "<pre>".print_r($appointments,true)."</pre>";
			}
		}
		$this->load->view('feeds');
	}
}

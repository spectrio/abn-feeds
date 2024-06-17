<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Logs extends CI_Controller {

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
		// $this->output->enable_profiler(TRUE);
	}

	public function index()
	{
		$this->load->view('feeds');
	}
	
	public function pttv($table_only = null)
	{
		$this->load->model('logs_model');
		$this->data["log_entries"] = $this->logs_model->get_pttv();
		if (!is_null($table_only)) {
			$this->data["table_only"] = true;
			header('Access-Control-Allow-Origin:*');
		} else {
			$this->data["table_only"] = false;
		}
		$this->load->view('pttv_logs',$this->data);
	}
	
	public function notify()
	{
		$this->load->model('logs_model');
		$this->data["log_entries"] = $this->logs_model->get_notifications();
		$this->load->view('notify_logs',$this->data);
	}

}
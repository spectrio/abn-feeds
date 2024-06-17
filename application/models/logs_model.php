<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Logs_model extends CI_Model 
{

	function __construct() 
	{
		parent::__construct();
	}
	
	function get_notifications() {
		$feedsDB = $this->load->database('default', TRUE);
	
		$sql = "SELECT * FROM notification_cache";
	
		$query = $feedsDB->query($sql);
	
		if ($query->num_rows() > 0) {
			return $query->result_array();
		} else {
			return FALSE;
		}
	}
	
	function get_pttv() {
		$feedsDB = $this->load->database('default', TRUE);
	
		$sql = "SELECT * FROM pttv_requests";
	
		$query = $feedsDB->query($sql);
	
		if ($query->num_rows() > 0) {
			return $query->result_array();
		} else {
			return FALSE;
		}
	}
}
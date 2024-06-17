<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class MYECO_model extends CI_Model 
{

	function __construct() 
	{
		parent::__construct();
	}
	
	function get_media($mediaType = NULL, $orientation = NULL, $widgetOEM = NULL, $locationID = NULL)
	{
	    if (!is_null($mediaType) && !is_null($orientation) && !is_null($widgetOEM) && !is_null($locationID)) {
	        $this->db->select('*');
	        $this->db->from('oem_twocodes');
	        $this->db->where('groups', strtoupper($widgetOEM));
	        $query = $this->db->get();
	        if ($query->num_rows() > 0) {
	            $oemData = $query->result_array();
	            foreach ($oemData as $row) {
	                $oem = $row['code'];
	            }
 	            $this->db->select('*');
	            $this->db->from('myeco_media');
	            $this->db->where('type', $mediaType);
	            $this->db->where('orientation', $orientation);
	            $this->db->where('oemCode', $oem);
	            $query = $this->db->get();
	            $result = $query->result();
	            return ($result ? $result : FALSE);
	        }
        }
        return false;
	}
}

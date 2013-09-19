<?php
	// this is an interface to the Open source Racktables REST API
	class racktables_model extends CI_Model {
		private $_data;
		private $_emess;

		public function __construct()
		{

		}
		public function __destruct()
		{
		}
		
		public function data()
		{
		    return $this->_data;
		}
		
		public function errormessage()
		{
		    return $this->_emess;
		}	

		//TODO :this could be a helper  instead of being included here. 
		public function curl($url){
			$ch=curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			//curl_setopt($ch, CURLOPT_USERPWD, "admin:LockM3D0wn");  shouldn't need this because in the racktables API, i've added the script flag as true
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);			
			$data=curl_exec($ch);
			//print_r($data);
			curl_close($ch);
			return $data;
		}
	
		public function get_all_switches()
		{		 

			$url = "http://localhost/rackAPI/index.php/devices/getallswitches";
			$jsondata = $this->curl($url);	
			echo $jsondata;  //send as is.  don't decode the json.
		}
		
		public function get_switch_details($object_id)
		{
		
			$url = "http://localhost/rackAPI/index.php/devices/getswitchdetails/".$object_id;
			$jsondata = $this->curl($url);
			$data = $this->decodejsondata($jsondata);  
		  
		     return $data;	
		}
				
		public function get_waps() 
		{

		
			$url = "http://localhost/racktables14/apiv2.php?method=get_objectsoftype&object_type_id=965";
			$jsondata = $this->curl($url);
			//true option on json_decode decodes data into associative arrays instead of stdClass objects.
			$data = json_decode($jsondata, true);  
		    return $data;		
		}		
				
		public function get_routers() 
		{
			$url = "http://localhost/racktables14/apiv2.php?method=get_objectsoftype&object_type_id=7";
			$jsondata = $this->curl($url);
			//true option on json_decode decodes data into associative arrays instead of stdClass objects.
			$data = json_decode($jsondata, true);  
		    return $data;		
		}			

		public function get_locations()
		{
			$url = "http://localhost/rackAPI/index.php/devices/getlocations";
			$jsondata = $this->curl($url);			
			$data = $this->decodejsondata($jsondata); 			
			return $data;
		}


		public function get_switches_by_location($branch_id, $building_id, $room_id)
		{
			$url = "http://localhost/rackAPI/index.php/devices/getswitchesbylocation/".$branch_id.'/'.$building_id.'/'.$room_id;
			$jsondata = $this->curl($url);
			return $jsondata;
		}

		public function get_locked_ports($object_id)
		{
			$url = "http://localhost/rackAPI/index.php/devices/getlockedports/".$object_id;
			$jsondata = $this->curl($url);
			$data = $this->decodejsondata($jsondata);
			if ($data){ 
				return $data;
			}
			else{
				return false;
			}
		}

		public function decodejsondata($data) 
		{		
		  $jsondata = json_decode($data, true);  	
		  switch (json_last_error()) {
		      case JSON_ERROR_DEPTH:
			  log_message('error', 'json_decode failed - Maximum stack depth exceeded'); 	
			  return false;
			  break;
		      case JSON_ERROR_STATE_MISMATCH:
			  log_message('error', 'json_decode failed - Underflow or the modes mismatch'); 
			  return false;
			  break;
		      case JSON_ERROR_CTRL_CHAR:
			  log_message('error', 'json_decode failed - Unexpected control character found');
			  return false;
			  break;
		      case JSON_ERROR_SYNTAX:
			  log_message('error', 'json_decode failed - Syntax error, malformed JSON');
			  return false;
			  break;
		      case JSON_ERROR_UTF8:
			  log_message('error', 'json_decode - Malformed UTF-8 characters, possibly incorrectly encoded');
			  return false;
			  break;
		  }	
		  
		  return $jsondata;
		}
	}

<?php
  /*
    openLSM - Light Weight Switch Management Tool
    Copyright (C) 2013 Julie Lee
    
    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

    Contact Information: openlsmdev@gmail.com
*/   
	class switches_model extends CI_Model {
	
		private $_data;
		private $_emess;
		private $_password;
		private $_userId;

		public function __construct()
		{
			$this->_password = 'radius_password';  			//radius password
   			$this->_userId="radius_account";   				//radius account
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
		/* 
		Determines if we can support the hardware.  Looks up name of switch in a hardcoded array. 
		if it exists in the array, then the controller displays options on the view that let's user 
		manage switch.
		*/
		public function isthissupported($hardwaremodel) 	
		{		    

		    $hardwaremodel=trim(strtoupper($hardwaremodel));	    	    
		    $supported = array("HP.PROCURVE.5406", "HP.PROCURVE.5412", "HP.PROCURVE.3500", "CISCO.SF.300-48", "CISCO.IE.3000-8TC");

		    //remove all spaces from $swtichname
		    $hardwaremodel = str_replace(' ','',$hardwaremodel);
		    foreach ($supported as $supportedswitch) {
			
			$supportedswitchNameBits = explode('.',$supportedswitch);
			$bfound=NULL;

			foreach ($supportedswitchNameBits as $partofname){
			    //the minute you don't find one of the parts of the support switch name in the switch you're checking, exit
			    $pos = strpos($hardwaremodel, $partofname);
			    if ($pos === false) {
			      $bfound = false; 
			      break;		      
			    }//end if
			    else {		    
			      $bfound = true;
			      $lookupname = $supportedswitch;
			      $lookupname= str_replace('.','',$supportedswitch);
			    }//end else			 
			    
			}// end inner loop		
			if ($bfound) {
			  break;		    
			}//end if
		      }//end of supported foreach
		      
		      if ($bfound) {
			    //determine which class needs to be loaded in the backend.
                            $switchclass=$this->switchToClassName($lookupname);			      
			    return true;
		      }
		      else {
			    return false;
		      }
		    }//end of function isthissupported
		/* decodes name of a swtich to a class name. TODO:  this is going to change.  No longer hardcode specific model names.  
		* lookup by family.  So  HP PROCURVE 5406 should match all types of %$06
		*/
		public function switchToClassName($hardwaremodel) 
		{
		    //remove all spaces, hyphens from switch name
		    $hardwaremodel = str_replace('%20','',$hardwaremodel);
		    $hardwaremodel = str_replace('-','',$hardwaremodel);
		    //convert to uppercase
		    $hardwaremodel = strtoupper($hardwaremodel);

		    switch ($hardwaremodel) {
		    	case "HPPROCURVE5412ZLJ8698A":
		    	case "HPPROCURVE5406":
				case "HPPROCURVE5406ZL48G":
				case "HPPROCURVE5406ZL":
				case "HPPROCURVE3500YL48GPWR":
				case "HPPROCURVE3500YL24GPWR":
				case "HPPROCURVE5406ZLJ8697A":	
				case "HPPROCURVE350024":					
				case "HPPROCURVE5406ZL48GJ8699A":	
				    return 'HP5406_ssh';
				    break;
				case "CISCOSF30048":
				    return 'ciscoSF302_ssh';
				    break;
				case "CISCOIE30008TC":
					return 'ciscoIE3000_ssh';
					break;
				default:
				    return false;
			}
		}
		
		public function save_config($fqdn, $hardwaremodel)
		{
		  $classname = $this->switchToClassName($hardwaremodel);
		   try{	
					include_once(APPPATH.'libraries/'.$classname.'.php');		
					$switch_obj = new $classname($fqdn, $this->_password, $this->_userId);
					if ($switch_obj->connect() )  {
					    if( $data = $switch_obj->saveconfig() ){
						    return true;
					    }	
					    else {
					    	$switch_obj->disconnect();
					    	throw new Exception('Failure');
					    }
					}
				    else  {
				       throw new Exception('Connection');
				    }
			 }
			catch (Exception $e) {
						$this->error_handler($e->getMessage());
					    return false;
			  }

		}
		public function get_macaddresses($fqdn, $hardwaremodel)  
		{			
			try {		
				    //find out what class library we need to load.
				    $classname = $this->switchToClassName($hardwaremodel);			
				    //include file - must force to to start with current file location. 
				    //include_once dirname(__FILE__) . '/../libraries/'.$classname.'.php';
				    include_once(APPPATH.'libraries/'.$classname.'.php');		  
				    $switch_obj = new $classname($fqdn, $this->_password, $this->_userId);
				    
				    //attempt to connect to switch
				    if ( $switch_obj->connect() )  {
				    	if ( $data = $switch_obj->showMacAddressTable() ) {				    								
							$switch_obj->disconnect(); 
							$macaddies = Array();
							foreach ($data as $macaddyandport){
							    $macaddy = explode(' ', $macaddyandport);
								array_push($macaddies,$macaddy);
					  		}
					    	return $macaddies;
				    	} 
				    	else  {
				    		$switch_obj->disconnect(); 
				    		 throw new Exception('Empty Data Set');
				    	} //end if showMacAddressTable
				    }
				    else  {
				       throw new Exception('Connection');
				    }// end if connect
			 }//end try

			catch (Exception $e) {
						$this->error_handler($e->getMessage());
					    return false;
			  }//end catch		 
		}// end get_macaddresses

		/*
		** The purpose of this method is to filter error messages.  
		** Some messages we want the user to see. Other messages that may reveal table structures etc
		** we want to just log and exit with a generic message for security reasons.
		*/
		private function error_handler($emess)
		{
			switch ($emess)
			{
				case 'Max Connections Reached':
					$userfriendlyemess = "The maximum number of connections to this switch has been reached!  You could wait a few minutes and retry.  Or if you feel this error message is bogus, contact your administrator! (2)";
					break;
				case 'Connection':
					$userfriendlyemess = "Unable to connect to device. Please contact administrator! (1)";
					break;
				case 'Empty Data Set':
					$userfriendlyemess = "Device connected.  Unable to retrieve requested data due to port mis-configuration issues.  Please contact administrator! (3)";
					break;
			 	case 'Failure':
			 		$userfriendlyemess = "Oops!  Unable to complete command. Please contact administrator! (4)";
			 		break;
				//handling unknown errors	
				default:
					$userfriendlyemess = "Oops!  Something big just happened. (99)";
					log_message('error', $emess); //write to ci logs
					break;
			}
			throw new Exception($userfriendlyemess);			
		}
		/*
		  HP 2824  get_macaddresses returns an array that looks like: 
		  Array ( [0] => Array ( [0] => 00000c-07ac21 [1] => 1 ) [1] => Array ( [0] => 0001e6-aff383 [1] => 1 ) [2] => Array ( [0] => 000ffe-fcc799 [1] => 1 ) [3] => Array ( [0] => 000ffe-fcc811 [1] => 1 ) [4] => Array ( [0] => 000ffe-fcc898 [1] => 1 ) [5] => Array ( [0] => 000ffe-fcc91f [1] => 1 ) [6] => Array ( [0] => 000ffe-fcc92d [1] => 1 ) [7] => Array ( [0] => 000ffe-fcc965 [1] => 1 ) [8] => Array ( [0] => 000ffe-fcc9df [1] => 1 ) [9] => Array ( [0] => 000ffe-fcc9f4 
	
		
		HP Procurve 5400
		Array ( [0] => Array ( [0] => 00000c-07ac03 B20 3 [1] => 000883-ded381 B20 3 [2] => 000f20-4c5241 B20 3 [3] => 001985-e05120 B20 3 [4] => 002561-e32140 B20 3 [5] => 00e039-933f02 B20 3 [6] => 00e039-939354 B20 3 [7] => 08000f-1eb958 B20 3 [8] => 08000f-1ec57c B20 3 [9] => 082e5f-498900 B20 3 [10] => 44e4d9-bcb7d0 B20 3 [11] => 68efbd-2ccd7f B20 3 [12] => 8843e1-4f19ff B20 3 [13] => b439d6-b94800 B20 3 [14] => b439d6-cb6800 B20 3 [15] => d0c282-f74630 B20 3 [16] => d0c282-f765c0 B20 3 [17] => 00000c-07ac08 B20 8 [18] => 00005e-000007 B20 
		
		
		*/
		
		public function get_logs($fqdn, $hardwaremodel)
		{
		    $classname = $this->switchToClassName($hardwaremodel);
		    try {
					include_once(APPPATH.'libraries/'.$classname.'.php');	
					$switch_obj = new $classname($fqdn, $this->_password, $this->_userId);
					if ( $switch_obj->connect() )  {
					    if ( $data = $switch_obj->showLogging() ) {
					    	$switch_obj->disconnect();						    
					    	return $data;
					    }
					    else {
					    	 $switch_obj->disconnect();
						    throw new Exception('Empty Data Set');	    		    	
					    }
					}
					else  {
					    throw new Exception('Connection');
					}
			}
			catch (Exception $e) {
						$this->error_handler($e->getMessage());
					    return false;
			}
		}
	
		public function get_portstatusall($fqdn, $hardwaremodel)
		{			
		    $classname = $this->switchToClassName($hardwaremodel);
		    try {
				      include_once(APPPATH.'libraries/'.$classname.'.php');			      
				      $switch_obj = new $classname($fqdn, $this->_password, $this->_userId);
				      if ( $switch_obj->connect() )  {
						  if ( $data = $switch_obj->showInterfaceAll() )  {
						  		$switch_obj->disconnect();  
						  		return $data;
						  }
						  else  {
						  	 $switch_obj->disconnect();
						  	 throw new Exception('Empty Data Set');
						  }
				      }
				      else  {									    
						   throw new Exception('Connection');	    						   
				      }
					
		    }
			catch (Exception $e) {
						$this->error_handler($e->getMessage());
					    return false;
			}
		}	
		
		public function get_portstatus($fqdn,$hardwaremodel, $port_num)
		{

		    $classname = $this->switchToClassName($hardwaremodel);
		    try{
					include_once(APPPATH.'libraries/'.$classname.'.php');
					$switch_obj = new $classname($fqdn, $this->_password, $this->_userId);
					if ( $switch_obj->connect() )
					{
					    if ( $data = $switch_obj->showInterface($port_num) ) {
						    $switch_obj->disconnect();
						    return $data;
						}
						else  {
							$switch_obj->disconnect();
							throw new Exception('Empty Data Set');
						}

					}
				    else  {
				       throw new Exception('Connection');
				    }
			 }
			catch (Exception $e) {
						$this->error_handler($e->getMessage());
					    return false;
			  }		
		}
		
		public function disable_port($fqdn, $hardwaremodel, $port_num)
		{
		    $classname = $this->switchToClassName($hardwaremodel);
		    try{
				include_once(APPPATH.'libraries/'.$classname.'.php');			
				$switch_obj = new $classname($fqdn, $this->_password, $this->_userId);
				if ( $switch_obj->connect() )  {
				    if ( $data = $switch_obj->disablePort($port_num) ) {
					    $switch_obj->disconnect();
					    return $data;
					}
					else {
						 $switch_obj->disconnect();
						throw new Exception('Empty Data Set');
					}
				}
				
				else  {
				    throw new Exception('Connection');
				}
			 }
			catch (Exception $e) {
					$this->error_handler($e->getMessage());
				    return false;
			  }
		}
		
		public function enable_port($fqdn, $hardwaremodel, $port_num)
		{
		    $classname = $this->switchToClassName($hardwaremodel);
		    try{	
					include_once(APPPATH.'libraries/'.$classname.'.php');		
					$switch_obj = new $classname($fqdn, $this->_password, $this->_userId);
					
					if ($switch_obj->connect() )  {
					    if ( $data = $switch_obj->enablePort($port_num) ) {
						    $switch_obj->disconnect();
						    return $data;
						}
						else {
							$switch_obj->disconnect();	
							throw new Exception('Empty Data Set');
						}						
					}
				    else  {
				       throw new Exception('Connection');
				    }
			 }
			catch (Exception $e) {
						$this->error_handler($e->getMessage());
					    return false;
			  }
			    
		}
		
		public function show_known_vlans($fqdn, $hardwaremodel)
		{
		    $classname = $this->switchToClassName($hardwaremodel);
		    try{	
					include_once(APPPATH.'libraries/'.$classname.'.php');		
					$switch_obj = new $classname($fqdn, $this->_password, $this->_userId);
					if ( $switch_obj->connect() )  {
					    if ( $data = $switch_obj->showknownvlans($fqdn) ) {
						    $switch_obj->disconnect();		
						    $data = $this->_filter_vlan_list($data);			    
						    return $data;	
						}
						else {
							$switch_obj->disconnect();
							throw new Exception('Empty Data Set');
						}							
					}
				    else  {
				       throw new Exception('Connection');
				    }
			 }
			catch (Exception $e) {
						$this->error_handler($e->getMessage());
					    return false;
			}
		}


		/* 
		Remove any vlans that we don't want the user to see or use
		*/
		private function _filter_vlan_list($vlans)
		{			
 			//remove fddi-default		
			foreach ($vlans as $a=>$value) {
			    if ($value['Name'] =='fddi-default' ){			    	
			    	unset($vlans[$a]);
			    	break;
			    }
			}
 			//remove token-ring-default
			foreach ($vlans as $a=>$value) {
			    if ($value['Name']=='token-ring-default' ){
			    	unset($vlans[$a]);
			    	break;
			    }	
			}

			 //remove fddinet-default
			foreach ($vlans as $a=>$value) {
			    if ($value['Name'] =='fddinet-default' ){
			    	unset($vlans[$a]);
			    	break;
			    }	
			}		

			//Remove trnet-default
			foreach ($vlans as $a=>$value) {
			    if ($value['Name']=='trnet-default' ){
			    	unset($vlans[$a]);
			    	break;
			    }	
			}			

			//Remove trnet-default
			foreach ($vlans as $a=>$value) {
			    if ($value['VlanId'] =='1' ){
			    	unset($vlans[$a]);
			    	break;
			    }	
			}
		  	//reindex the entire array for json function in view to work properly 	  	   
		    $vlans = array_values($vlans); 	
		  	return $vlans;
		}

		public function show_port_vlan($fqdn, $hardwaremodel, $portname)
		{
			$portname = str_replace('~', '/', $portname);
			$classname = $this->switchToClassName($hardwaremodel);
			try{	
					include_once(APPPATH.'libraries/'.$classname.'.php');		
					$switch_obj = new $classname($fqdn, $this->_password, $this->_userId);
					if ($switch_obj->connect() )  {
					    if ( $data = $switch_obj->showportvlan($portname) ) {		
					    	$switch_obj->disconnect();	
					    	$data = $this->_filter_vlan_list($data);	
					    	//$this->_writeDataToFile($data);		    				    
						    return $data;
						}
						else {
							$switch_obj->disconnect();
							throw new Exception('Empty Data Set');
						}													
					}
				    else  {
				       throw new Exception('Connection');
				    }
			}
			catch (Exception $e) {
						$this->error_handler($e->getMessage());
					    return false;
			}
		}
		
		public function change_port_vlan($fqdn, $hardwaremodel, $portname, $newVlan, $mode)
		{		
		  $classname = $this->switchToClassName($hardwaremodel);
		   try{	
					include_once(APPPATH.'libraries/'.$classname.'.php');		
					$switch_obj = new $classname($fqdn, $this->_password, $this->_userId);
					if ( $switch_obj->connect() )  {
					    $data = $switch_obj->changeportvlan($portname, $newVlan, $mode);										
					    $data = $this->show_port_vlan($fqdn, $hardwaremodel, $portname); 					
					    $switch_obj->disconnect();			    
					    return $data;
					}
				    else  {
				       throw new Exception('Connection');
				    }
			 }
			catch (Exception $e) {
						$this->error_handler($e->getMessage());
					    return false;
			  }

		}		

		//ajax method...
		public function delete_port_vlan($fqdn, $hardwaremodel, $portname, $deleteVlan, $mode)
		{		
		  $classname = $this->switchToClassName($hardwaremodel);
		   try{	
					include_once(APPPATH.'libraries/'.$classname.'.php');		
					$switch_obj = new $classname($fqdn, $this->_password, $this->_userId);
					if ($switch_obj->connect() )  {

					    if( $data = $switch_obj->deleteportvlan($portname, $deleteVlan, $mode) ){

						    if ( $data = $switch_obj->showportvlan($portname) ) {	
						    	$data = $this->_filter_vlan_list($data);			
						    	$switch_obj->disconnect();	    
							    return $data;	
							}
							else {
								$switch_obj->disconnect();
								throw new Exception('Empty Data Set');
							}											    	
					    }	
					    else {
					    	$switch_obj->disconnect();
					    	throw new Exception('Failure');
					    }
					}
				    else  {
				       throw new Exception('Connection');
				    }
			 }
			catch (Exception $e) {
						$this->error_handler($e->getMessage());
					    return false;
			  }

		}

		public function add_port_vlan($fqdn, $hardwaremodel, $portname, $newVlan, $mode)
		{
		   $classname = $this->switchToClassName($hardwaremodel);
		   
		   try{	
					include_once(APPPATH.'libraries/'.$classname.'.php');		
					$switch_obj = new $classname($fqdn, $this->_password, $this->_userId);
					if ( $switch_obj->connect() )  {
					    if ( $data = $switch_obj->changeportvlan($portname, $newVlan, $mode) ) {
						    if (  $data = $switch_obj->showportvlan($portname)  ) {	
						    	$data = $this->_filter_vlan_list($data);								   
							    $this->_data = $data;
							    $switch_obj->disconnect();
							    return true;
							}
							else {
								$switch_obj->disconnect();				
								throw new Exception('Empty Data Set');
							}								
						}
					    else {
					    	$switch_obj->disconnect();
					    	throw new Exception('Failure');
					    }	
					    $switch_obj->disconnect();						    
					}
				    else  {
				       throw new Exception('Connection');
				    }
			 }
			catch (Exception $e) {
						$this->error_handler($e->getMessage());
					    return false;
			  }
		}

		public function changeduplex($fqdn, $hardwaremodel, $portname, $duplex_value)
		{		
		  $classname = $this->switchToClassName($hardwaremodel);
		   try {	
					include_once(APPPATH.'libraries/'.$classname.'.php');		
					$switch_obj = new $classname($fqdn, $this->_password, $this->_userId);
					if ( $switch_obj->connect() )  {
					    if ( $data = $switch_obj->changeduplex($portname, $duplex_value) ) {	
					     	$switch_obj->disconnect();					   
						    return $data;
						}
					    else {
					    	 $switch_obj->disconnect();
					    	throw new Exception('Failure');
					    }											   
					}
				    else  {
				       throw new Exception('Connection');
				    }
			 }
			catch (Exception $e) {
						$this->error_handler($e->getMessage());
					    return false;
			  }				
		}		
		
		//TODO public function chassisLightOn($ip)
		public function chassisLightOn($fqdn, $hardwaremodel)
		{
		  $classname = $this->switchToClassName($hardwaremodel);
		   try{	
					include_once(APPPATH.'libraries/'.$classname.'.php');		
					$switch_obj = new $classname($fqdn, $this->_password, $this->_userId);
					if ( $switch_obj->connect() )  {					  
					    if ( $data = $switch_obj->chassisLocateLightOn($fqdn) ) {	
					    	$switch_obj->disconnect();					    
						    return $data;
						}
						else {
							$switch_obj->disconnect();
							throw new Exception('Failure');
						}						
					}
				    else  {
				       throw new Exception('Connection');
				    }
			 }
			catch (Exception $e) {
						$this->error_handler($e->getMessage());
					    return false;
			  }
		
		}
		
		public function chassisLightOff($fqdn, $hardwaremodel)
		{		
		  $classname = $this->switchToClassName($hardwaremodel);
		   try{	
					include_once(APPPATH.'libraries/'.$classname.'.php');		
					$switch_obj = new $classname($fqdn, $this->_password, $this->_userId);
					if ($switch_obj->connect() )  {
					    if ( $data = $switch_obj->chassisLocateLightOff($fqdn) ) {	
					    	$switch_obj->disconnect();							   			  
						    return $data;
						}
						else {
							$switch_obj->disconnect();
							throw new Exception('Failure');
						}						
					}
				    else  {
				       throw new Exception('Connection');
				    }
			 }
			catch (Exception $e) {
						$this->error_handler($e->getMessage());
					    return false;
			  }
		
		}
		
		public function chassisLightBlink($fqdn, $hardwaremodel)
		{
		  $classname = $this->switchToClassName($hardwaremodel);
		   try{	
					include_once(APPPATH.'libraries/'.$classname.'.php');		
					$switch_obj = new $classname($fqdn, $this->_password, $this->_userId);
					if ($switch_obj->connect() )  {
					    if ( $data = $switch_obj->chassisLocateLightBlink($fqdn) ) {				
					    	$switch_obj->disconnect();		  
						    return $data;
						}
						else {
							$switch_obj->disconnect();
							throw new Exception('Failure');
						}
  						
					}
				    else  {
				       throw new Exception('Connection');
				    }
			 }
			catch (Exception $e) {
						$this->error_handler($e->getMessage());
					    return false;
			  }
		
		}
		
		public function showPoeStatus($fqdn, $hardwaremodel, $portname)
		{
		 //define('START',microtime(true));
		 //.echo START.'<br>';
		  $classname = $this->switchToClassName($hardwaremodel);
		   try{	
					include_once(APPPATH.'libraries/'.$classname.'.php');		
					$switch_obj = new $classname($fqdn, $this->_password, $this->_userId);
					
					if ($switch_obj->connect() )  {				
					    if (  $data = $switch_obj->poeStatus($portname)  ) {
						    $switch_obj->disconnect();							  
						    return $data;
						}
						else {
							$switch_obj->disconnect();	
							throw new Exception('Empty Data Set');
						}
					}
				    else  {
				       throw new Exception('Connection');
				    }
			 }
			catch (Exception $e) {
						$this->error_handler($e->getMessage());
					    return false;
			  }
			
		}//showPoeStatus
		
		public function poeOff($fqdn, $hardwaremodel, $portname)
		{
		  $classname = $this->switchToClassName($hardwaremodel);
		   try{	
					include_once(APPPATH.'libraries/'.$classname.'.php');		
					$switch_obj = new $classname($fqdn, $this->_password, $this->_userId);
					if ($switch_obj->connect() )  {
					    if ( $data = $switch_obj->poeOff($portname) ) { 
						    $switch_obj->disconnect();					   
						    return $data;	
						}
						else {
							$switch_obj->disconnect();	
							throw new Exception('Failure');
						}
					}
				    else  {
				       throw new Exception('Connection');
				    }
			 }
			catch (Exception $e) {
						$this->error_handler($e->getMessage());
					    return false;
			  }		
		}//poeOff
		
		public function poeOn($fqdn, $hardwaremodel, $portname)
		{
		  $classname = $this->switchToClassName($hardwaremodel);
		   try{	
					include_once(APPPATH.'libraries/'.$classname.'.php');		
					$switch_obj = new $classname($fqdn, $this->_password, $this->_userId);
					if ($switch_obj->connect() )  {
					    if ( $data = $switch_obj->poeOn($portname) ) {
						    $switch_obj->disconnect();					   
						    return $data;	
						}
						else {	
							$switch_obj->disconnect();						
							throw new Exception('Failure');
						}
					}
				    else  {
				       throw new Exception('Connection');
				    }
			 }
			catch (Exception $e) {
						$this->error_handler($e->getMessage());
					    return false;
			  }
		
		}//poeOn		
		
		public function macauthOff($fqdn, $hardwaremodel, $portname)
		{
		  $classname = $this->switchToClassName($hardwaremodel);
		   try{	
					include_once(APPPATH.'libraries/'.$classname.'.php');		
					$switch_obj = new $classname($fqdn, $this->_password, $this->_userId);
					if ($switch_obj->connect() )  {
					    if ( $data = $switch_obj->macauthOff($portname) ) { 
						    $switch_obj->disconnect();					   
			        		$retstring = 'Port Number: '.$data[0]['PortNumber'].'<BR>';
			        		$retstring = $retstring . 'Enabled: '.$data[0]['Enabled'].'<BR>';
			        		$retstring = $retstring . 'Client Limit: '.$data[0]['ClientLimit'].'<BR>';
						    
						    return $retstring; //array...
						}
						else {
							$switch_obj->disconnect();	
							throw new Exception('Failure');
						}
					}
				    else  {
				       throw new Exception('Connection');
				    }
			 }
			catch (Exception $e) {
						$this->error_handler($e->getMessage());
					    return false;
			  }		
		}//macauthOff
		
		public function macauthOn($fqdn, $hardwaremodel, $portname)
		{
		  $classname = $this->switchToClassName($hardwaremodel);
		   try{	
					include_once(APPPATH.'libraries/'.$classname.'.php');		
					$switch_obj = new $classname($fqdn, $this->_password, $this->_userId);
					if ($switch_obj->connect() )  {
					    if ( $data = $switch_obj->macauthOn($portname) ) {
						    $switch_obj->disconnect();			
			        		$retstring = 'Port Number: '.$data[0]['PortNumber'].'<BR>';
			        		$retstring = $retstring . 'Enabled: '.$data[0]['Enabled'].'<BR>';
			        		$retstring = $retstring . 'Client Limit: '.$data[0]['ClientLimit'].'<BR>';
						    
						    return $retstring; //array...						    		   
						}
						else {	
							$switch_obj->disconnect();						
							throw new Exception('Failure');
						}
					}
				    else  {
				       throw new Exception('Connection');
				    }
			 }
			catch (Exception $e) {
						$this->error_handler($e->getMessage());
					    return false;
			  }
		
		}//macauthOn	


		public function showMacAuthStatus($fqdn, $hardwaremodel, $portname)
		{
		  $classname = $this->switchToClassName($hardwaremodel);
		   try{	
					include_once(APPPATH.'libraries/'.$classname.'.php');		
					$switch_obj = new $classname($fqdn, $this->_password, $this->_userId);
					if ($switch_obj->connect() )  {				
					    if (  $data = $switch_obj->macAuthStatus($portname)  ) {
						    $switch_obj->disconnect();
						    //change data from array to string with HTML embedded in it.
			        		$retstring = 'Port Number: '.$data[0]['PortNumber'].'<BR>';
			        		$retstring = $retstring . 'Enabled: '.$data[0]['Enabled'].'<BR>';
			        		$retstring = $retstring . 'Client Limit: '.$data[0]['ClientLimit'].'<BR>';
						    
						    return $retstring; //array...
						}
						else {
							$switch_obj->disconnect();	
							throw new Exception('Empty Data Set');
						}
					}
				    else  {
				       throw new Exception('Connection');
				    }
			 }
			catch (Exception $e) {
						$this->error_handler($e->getMessage());
					    return false;
			  }
			
		}//showMacAuthStatus		

		
		public function decodejsondata($data) 
		{
		
		  $jsondata = json_decode($data, true);  	
		  switch (json_last_error()) {
		      case JSON_ERROR_DEPTH:
			  log_message('error', 'json_decode failed - Maximum stack depth exceeded'); 	
			  show_error('error', 'json_decode failed - Maximum stack depth exceeded');
			  return false;
			  break;
		      case JSON_ERROR_STATE_MISMATCH:
			  log_message('error', 'json_decode failed - Underflow or the modes mismatch'); 
			  show_error('error', 'json_decode failed - Underflow or the modes mismatch');
			  return false;
			  break;
		      case JSON_ERROR_CTRL_CHAR:
			  log_message('error', 'json_decode failed - Unexpected control character found');
			  show_error('error', 'json_decode failed - Unexpected control character found');
			  return false;
			  break;
		      case JSON_ERROR_SYNTAX:
			  log_message('error', 'json_decode failed - Syntax error, malformed JSON');
			  show_error('error', 'json_decode failed - Syntax error, malformed JSON');
			  return false;
			  break;
		      case JSON_ERROR_UTF8:
			  log_message('error', 'json_decode - Malformed UTF-8 characters, possibly incorrectly encoded');
			  show_error('error', 'json_decode - Malformed UTF-8 characters, possibly incorrectly encoded');
			  return false;
			  break;
		  }	
		  
		  return $jsondata;
		}

		//debug purposes only
		private function _writeDataToFile($data)
		{
			$fp = fopen('/var/log/lsm_switchmodel.log', 'a');
			fwrite($fp, date('l jS \of F Y h:i:s A').' - ' .$data);
			fwrite($fp, "\n=========\n");		
			fclose($fp);
		}		
	}


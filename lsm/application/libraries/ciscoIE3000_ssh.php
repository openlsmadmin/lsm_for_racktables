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
      /**
     * ciscoIE3000_ssh -  Basic Command set to manage a Cisco Industrial Ethernet 3000 (IE3000) switch.
     * PHPSECLIB SSH library required. (http://phpseclib.sourceforge.net/)
     * Modify path in the constructor to point to your phpseclib install.show_port_vlan
     * 
     * CISCO always uses "/" in naming their ports.  We parse them out before sending to front end
     * because it interferes with CI's URI segments. 
     * We replace them with our own character ~
     * 
     **/
    class CiscoIE3000_ssh
    {	 
	private $_hostname;
	private $_password;
	private $_username;
	private $_connection;
	private $_data;
	private $_timeout;
	private $_prompt;
	private $_emess;
	private $_countersLocation;
	private $_counterFile;	
	
	/**
	* Class Constructor
	* @param  string  $hostname Hostname or IP address of the device
	* @param  string  $password Password used to connect
	* @param  string  $username (Optional) Username used to connect
	* @param  integer $timeout  Connetion timeout (seconds)
	* @return object of type HP switch
	*/
	public function __construct($hostname, $password, $username = "", $timeout = 10) 

	{
	    set_include_path(get_include_path() . PATH_SEPARATOR . '/var/www/phpseclib');
	    include_once('Net/SSH2.php');
	    
	    //define('NET_SSH2_LOGGING', NET_SSH2_LOG_COMPLEX); 

	    $this->_hostname = $hostname;
	    $this->_password = $password;
	    $this->_username = $username;

	    //Jan 14.2013 class counter 
	    if ($bconstruct = $this->_checkClassCounter() ) {
             $this->_addCounter();
	    }
	    else {
	    	throw new Exception('Max Connections Reached');	    	
	    }		   
	} // __construct

	private function _checkClassCounter()
	{
		//1.  check the number of connections that currently exist. 
		//2.  if less than 4, allow connection.  
		//$counterslocation = BASEPATH; // contains var/www/lsm/system
		//$pattern='/(\/[0-9a-z]+\/)*(lsm\/)(system)/i'; 
		//$replacement = '$1$2application/counters';
		//$counterslocation =  preg_replace($pattern, $replacement, $counterslocation);
		$counterslocation =  '/var/log/lsmcounters/';
		$this->_countersLocation = $counterslocation;

		// /var/www/lsm/application/counters
		$filelist = glob($counterslocation.$this->_hostname.'*.tmp');

		if ( count($filelist)  < 4) {
			return true;
		}		
		else {
			return false;
		}
	} // end _checkClassCounter

	private function _addCounter()
	{
		try{
			$this->_counterFile = $this->_countersLocation.$this->_hostname.rand(1,10000).".tmp";
			$handle = fopen($this->_counterFile, "w");
		}
		catch (Exception $e) {
		    throw new Exception('Problems creating'. $this->_counterFile. $e->getMessage());
		} 
	} // end _addCounter

	private function _deleteCounter()
	{
		try  {
			unlink($this->_counterFile);
		}
		catch (Exception $e) {
		    throw new Exception('Problems deleting'. $this->_counterFile.$e.getMessage());
		}  
	} //end _deleteCounter

	/*
	* Function: connect
	* Description:  Attempts to connect to the switch.
	* @return false on failure.  sets error message property. 
	* @return true if pass.  sets global ssh object.
	*
	*/
	public function connect() 
	{
	    $ssh = new Net_SSH2($this->_hostname); 
	    
	    if (!$ssh->login($this->_username, $this->_password)) { //if you can't log on...
				$this->_emess = "Error: Authentication Failed for $this->_hostname";				
				return false;		
	    }
	    else  {				
				$this->_connection = $ssh;
				$prompt = $this->_connection->read('/([0-9A-Z\-])*(#)(\s*)/i', NET_SSH2_READ_REGEX);
				if (!$prompt) {
				    $this->_emess = "Error: Problem connecting for $this->_hostname";
				    return false;		   
				}  
				else {		  
				    $this->_connection = $ssh;				   
				    return true;		
				}
	    }
	} // connect
	
	/**
	* Send Command to switch
	*/
	private function _send($command) 
	{	
	  $commandresults = $this->_connection->write($command."\n");	 
	  if ($commandresults) {
	     $this->_data = $this->_connection->read('/([0-9A-Z\-])*(#)(\s*)/i', NET_SSH2_READ_REGEX);     	     
	  }
	  else {
		$this->_data = false;
	        $this->_emess = "Error: Problem executing command for $command";
	  }
	  return $this->_data;
	} // _send
	
	/**
	* Disconnect SSH session
	*/
	public function disconnect()
	{
	  $this->_connection->disconnect();
	  $ssh=NULL;
	}

	public function nopaging() 
	{
	  $this->_send("terminal length 0");
	}

	public function saveconfig()
	{    
	    $this->_send('wr mem');
	    return $this->_data;
	}	
      
	public function showMacAddressTable() 
	{
	    $this->nopaging();
	    $this->_send('show mac address-table');
	    $result = array();

	    if ($this->_data !== false) {    
				$this->_data  = str_replace(chr(10)," ",$this->_data ,$count);//strip LF
				$this->_data  = str_replace(chr(13)," ",$this->_data ,$count);//strip CR
				$this->_data  = str_replace(chr(27)," ",$this->_data ,$count);//strip out esc character
			
				$pattern='/([0-9a-z]+)(\s+)([0-9A-F]{4}).([0-9A-F]{4}).([0-9A-F]{4})(\s+)(STATIC|DYNAMIC)(\s+)([0-9a-z\/]+)/i'; 
				if (preg_match_all($pattern,$this->_data,$matches, PREG_PATTERN_ORDER))  {
				    $data = preg_replace('/\s+/',' ', $matches[0]);	 //remove extra spaces.
				    return $data;		    
				}	    
	    } 
	    else {
	    	return false;
	    }  
	} 

	//get switch logs.
	public function showLogging() 
	{
	    $this->nopaging();
	    $this->_send('show log');

	    if ($this->_data !== false) 
	    {        
				$this->_data  = str_replace(chr(27)," ",$this->_data ,$count);//strip out esc character
				$this->_data  = str_replace(chr(13),"",$this->_data ,$count);//strip LF	
				$this->_data  = str_replace(chr(10),'<BR>',$this->_data ,$count);//strip LF	
	    }
	    return $this->_data;
	} 
	

	/*
	Sample return data from switch:
	Port      Name               Status       Vlan       Duplex  Speed Type
	Fa1/1                        notconnect   33           auto   auto 10/100BaseTX
	Fa1/2                        notconnect   33           auto   auto 10/100BaseTX
	Fa1/3                        notconnect   33           auto   auto 10/100BaseTX
	*/
	public function showInterfaceAll() 
	{
	    $this->nopaging();
	    $this->_send('show interfaces status');
	    
	    if ($this->_data !== false)  {
			    $this->_data = explode("\r\n", $this->_data);
			    $portdetailsArray = array();
			    foreach ($this->_data as $portdetails) {	      
                  //pattern desc   port               status                         vlan             duplex       speed                type
				  $pattern = '/([0-9a-z\/]*)(\s+)(disabled|notconnect|connected|\s+)(\s+)([0-9|trunk]+)(\s+)([a-z\-]*)(\s+)([a-z\-0-9]*)(\s+)([0-9a-z\/\s]*)/i';		      
				  if (preg_match($pattern, $portdetails, $matches)) {

				  	    //$modPort = str_replace("/","~",$matches[1]);
					  	array_push($portdetailsArray, array(
						    'Port' => $matches[1],
						    'Status' => $matches[3],
						    'Vlan' => $matches[5],
						    'Duplex' => $matches[7],
						    'Speed' => $matches[9],
						    'Type' => $matches[11]));
		      		}//end if
	      		}//end for	 
	      		$this->_data = $portdetailsArray;		
	    } //end if
	    return $this->_data;
	} // showInterfaceAll

	/**
	* List Specific Trunk Interface
	* @return array|boolean On success returns an array, false on failure.
	*/
	public function showInterface($port_num) 
	{
	    $this->nopaging();
	    $this->_send('show interfaces '.$port_num);
	   
	    if ($this->_data !== false)
	    {

 			$this->_data  = str_replace(chr(10),"<BR>",$this->_data ,$count);//strip New line
 			$this->_data  = str_replace(chr(13),'',$this->_data ,$count);//strip carriage return		
			$this->_data  = str_replace(chr(9),chr(32),$this->_data ,$count);//strip vertical tabs. replace with spaces
 			$this->_data  = str_replace(chr(32),"&nbsp;",$this->_data ,$count);//remove all extra white space.
	    }
		
	    return $this->_data;
	} // end showPortStatus


	public function disablePort($port_num)
	{
	    $this->nopaging();
	    $this->_send('conf t');	  
	    $this->_send('int '.$port_num);	   
	    $this->_send('shutdown');	 
	    return $this->_data;
	}// end disablePort
	

	public function enablePort($port_num)
	{
	    $this->nopaging();
	    $this->_send('conf t');	  
	    $this->_send('int '.$port_num);	   
	    $this->_send('no shutdown');	 
	    return $this->_data; 
	}// end enablePort
    
	//** had to explicitly list each option , unlike HP.
    public function changeduplex($portname, $duplex_value)
	{
		$portname = str_replace('~','/',$portname);
	    switch ($duplex_value)  {
		case '10-half':
		      $this->nopaging();
		      $this->_send('conf t');
		      $this->_send('interface '.$portname);
		      $this->_send('speed 10');
		      $this->_send('duplex half');		    
		      break;
		case '100-half':
		      $this->nopaging();
		      $this->_send('conf t');
		      $this->_send('interface '.$portname);
		      $this->_send('speed 100');
		      $this->_send('duplex half');		    
		      break;		
		case '10-full':
		      $this->nopaging();
		      $this->_send('conf t');
		      $this->_send('interface '.$portname);
		      $this->_send('speed 10');
		      $this->_send('duplex full');		    
		      break;		
		case '100-full':
		      $this->nopaging();
		      $this->_send('conf t');
		      $this->_send('interface '.$portname);
		      $this->_send('speed 100');
		      $this->_send('duplex full');		    
		      break;		
		case 'auto':
		      $this->nopaging();
		      $this->_send('conf t');
		      $this->_send('interface '.$portname);
		      $this->_send('speed auto');
		      $this->_send('duplex auto');		    
		      break;		
		case 'auto-10':
		      $this->nopaging();
		      $this->_send('conf t');
		      $this->_send('interface '.$portname);
		      $this->_send('speed 10');
		      $this->_send('duplex auto');		    
		      break;		
		case 'auto-100':
		      $this->nopaging();
		      $this->_send('conf t');
		      $this->_send('interface '.$portname);
		      $this->_send('speed 100');
		      $this->_send('duplex auto');		    
		      break;		
		case 'auto-1000':
		      $this->nopaging();
		      $this->_send('conf t');
		      $this->_send('interface '.$portname);
		      $this->_send('speed 1000');
		      $this->_send('duplex auto');		    
		      break;		
	    }
	    return $this->_data; 
	}//changeduplex


	/*
	

	*/
	public function showportvlan($portname)
	{
	   $vlandetailsArray = array();
	   
	   $this->nopaging();
	   $this->_send('show interfaces '.$portname .' switchport');

	   if ($this->_data !== false)  {

	   		//	=============== ACCESS MODE ===========================================
			$pattern = "/(Administrative Mode:)(\s)(static)(\s)(access)/i";
			if (preg_match($pattern, $this->_data, $matches)) {
				
				  //find Access Mode VLAN: 67 (VLAN0067)
				$pattern = "/(Access Mode VLAN:)(\s)([0-9]*)(\s+)(\()([0-9A-Z\_]*)(\))/i";
				if (preg_match($pattern, $this->_data, $matches)) {						
						array_push($vlandetailsArray, array(		
											'VlanId' =>  $matches[3],
											'Name' => $matches[6],
											'Mode' => 'Untagged'));	
						  	$this->_data = $vlandetailsArray;
				}
			}
			else  { 
				//	=============== TRUNK MODE ===========================================
				
				$pattern = "/(Administrative Mode:)(\s)(trunk)/i";
				if (preg_match($pattern, $this->_data, $matches)) {

					//find out the UNTAGGED vlan value.  Look for : 'Trunking Native Mode VLAN:'
					$pattern = "/(Trunking Native Mode VLAN:)(\s+)([0-9]*)(\s+)(\()([0-9a-z]*)(\))/i";
				
					if (preg_match($pattern, $this->_data, $matches)) {
					 	array_push($vlandetailsArray, array(		
												'VlanId' => $matches[3],
												'Name' => $matches[6],
												'Mode' => 'Untagged'));	
					}// end preg_match for Untagged vlan.

					//Find all the TAGGED vlans =  Trunking VLANs Enabled: 1-5,33,69
					//because they can include values that are not actually in use, compare the list with
					//what you get back from running "show vlans" and only return the vlans that are in both lists. 
					$pattern = "/(Trunking VLANs Enabled:)(\s)([a-z0-9\,\-]*|ALL)/i";
					if (preg_match($pattern, $this->_data, $matches)) {						
						//grab list of known vlans.
						$knownVlans = $this->showknownvlans();
						if (strtoupper($matches[3]) == 'ALL') {

							//Add all results from show vlan command to the return data sets. 
							foreach ($knownVlans as $vlan){																									
								    array_push($vlandetailsArray, array(		
										'VlanId' => $vlan['VlanId'],
										'Name' => $vlan['Name'],
										'Mode' => 'Tagged'));	
							}
						}
						else{
							//Compare knownvlans list with "Trunking VLANS Enabled" list and only add known vlans that are in both lists
							foreach ($knownVlans as $vlan){							
								$vlangroups = explode(',', $matches[3]);
								foreach ($vlangroups as $trunkvlan) {
											$rangepos = strpos($trunkvlan, '-');
											//check if you're getting a range of vlans.
											if (! $rangepos ===false)  {			
												$lowend = substr($trunkvlan,0,$rangepos);
												$highend = substr($trunkvlan,$rangepos+1,strlen($trunkvlan));
												if (($lowend <= $vlan['VlanId']) && ($vlan['VlanId'] <= $highend) ) {
													//add it to list. 
												    array_push($vlandetailsArray, array(		
														'VlanId' => $vlan['VlanId'],
														'Name' => $vlan['Name'],
														'Mode' => 'Tagged'));														
												}																
											}
											else {
												if($trunkvlan==$vlan['VlanId']){
													array_push($vlandetailsArray, array(		
														'VlanId' => $vlan['VlanId'],
														'Name' => $vlan['Name'],
														'Mode' => 'Tagged'));	
												}//end if
											}//end else

															
								}//end loop through each vlan group								
							}//end outer loop through known vlans. 
						}

					}
					else {
						//	=============== DYNAMIC AUTO?? ===========================================
						// port mode is invalid.  Raise an error
						$this->_data = false;
						$this->_emess = "INVALID PORT MODE";
					}
				}//end pattern match for trunk.
			}//end if regex  
		$this->_data = $vlandetailsArray;	
	   return $this->_data;
	}
}

	/*
	1.  Determine Port Mode - Access or Trunk
	2.  If it's ACCESS and we're trying to add a TAGGED vlan, change the mode to Trunk. And then add new vlan. 
	3.  If it's ACCESS and we're trying to add an UNTAGGED vlan, replace the existing one
	*/
	public function changeportvlan($portname,$newVlan, $mode)
	{   

	 	//1. find out if port is in access or trunk mode. 
	   $this->nopaging();
	   $this->_send('show interfaces '.$portname .' switchport');
	   if ($this->_data !== false)  {
	 
	   		//	Check for Access mode
			$pattern = "/(Administrative Mode:)(\s)(static)(\s)(access)/i";
			if (preg_match($pattern, $this->_data, $matches)) {
					$this->_send('conf t');
					$this->_send('int '.$portname);

					switch (strtoupper($mode)) {
						case 'TAGGED':
							$this->_send('switchport mode trunk');
							$this->_send('switchport trunk allowed vlan add '.$newVlan);
							break;
						case 'UNTAGGED':
							$this->_send('switchport access vlan '.$newVlan);
							break;
					}
					$this->_send('switchport access vlan '.$newVlan);
				    $this->_send('exit');  //exit config interface
				    $this->_send('exit');  //exit config.					 
			}
			else  {

				//	Check for Trunk Mode
				$pattern = "/(Administrative Mode:)(\s)(trunk)/i";
				if (preg_match($pattern, $this->_data, $matches)) {
					$this->_send('conf t');
					$this->_send('int '.$portname);

					switch (strtoupper($mode)) {
						case 'TAGGED':							
							$this->_send('switchport trunk allowed vlan add '.$newVlan);
							break;
						case 'UNTAGGED':
							$this->_send('switchport trunk native vlan '.$newVlan);
							break;
					}					
				    $this->_send('exit');  //exit config interface
				    $this->_send('exit');  //exit config.					
				}
				else{
					//print 'regex match failed';
				}
			}
		}	    	     
	    return $this->_data;
	 }	

	public function deleteportvlan($portname,$deleteVlan, $mode)
	{    

       $this->nopaging();
	   $this->_send('show interfaces '.$portname .' switchport');
	   if ($this->_data !== false)  {
	   		//	Check for Access mode.  If it is exit with error.
			$pattern = "/(Administrative Mode:)(\s)(static)(\s)(access)/i";
			if (preg_match($pattern, $this->_data, $matches)) {
				$this->_data = false;		 
			}
			else  {
				//	Check for Trunk Mode
				$pattern = "/(Administrative Mode:)(\s)(trunk)/i";
				if (preg_match_all($pattern, $this->_data, $matches)) {
					//check mode of port.  
					//although it's legal to have no untagged port in trunk mode, DO NOT LET THEM DELETE UNTAGGED PORTS.
					If (strtoupper($mode)=='UNTAGGED') {
						$this->_data = false;
					}
					else{
						$this->_send('conf t');
						$this->_send('int '.$portname);
						$this->_send('switchport trunk allowed vlan remove '.$deleteVlan);
					    $this->_send('exit');  //exit config interface
					    $this->_send('exit');  //exit config.						
					}					
				}
			}
	    
	 	}
	 return $this->_data;
	 }

	  /*Note:  cisco sf300 has separate command to set duplex and speed.
    /*

		VLAN Name                             Status    Ports
		---- -------------------------------- --------- -------------------------------
		1    default                          active    Gi1/2
		3    MAN_3                            active    
		33   USR_33                           active    Fa1/2, Fa1/3, Fa1/4, Fa1/5, Fa1/6, Fa1/8
		67   VLAN0067                         active    Fa1/1
		70   VLAN0070                         active    Fa1/7
		1002 fddi-default                     act/unsup 
		1003 token-ring-default               act/unsup 
		1004 fddinet-default                  act/unsup 
		1005 trnet-default                    act/unsup 
		CAN-IE3000-TEMPLATE#


    */	 
	public function showknownvlans() 
	{
	    $this->nopaging();
	    $this->_send('show vlan brief');

	    //create emtpy array to hold
	    $vlandetailsArray = array();
	    
	    if ($this->_data !== false)  {
		      $result = array();
		      $this->_data = str_replace(chr(10),'',$this->_data ,$count);//strip LF
		      $this->_data= str_replace(chr(13),'<BR>',$this->_data,$count);//strip CR		      
		      $this->_data = explode("<BR>", $this->_data);	
			
		      foreach ($this->_data as $vlandetails) 
		      {		     
			    $pattern = '/([0-9]+)(\s*)([a-z0-9\-\_]*)(\s)+/i';
			    if (preg_match($pattern, $vlandetails, $matches)) {					
					array_push($vlandetailsArray, array(				      
				      'VlanId'=> $matches[1],				    
				      'Name'=> $matches[3]));
			    }//end if 			      
		      }// end for	    		
		      $this->_data = $vlandetailsArray;
		      //$teststring = print_r($vlandetailsArray, true);
	  }	     
	    return $this->_data;
	} // showknownvlans

	/**
	* Any error message that has been set can be accessed via this function. 	
	*/
	public function errormessage()
	{
	    return $this->_emess;
	}
		
	/**
	* Send Exit Command to switch
	*/
	public function close() 
	{
	    $this->_send('exit');
	    
	} // close


	function _writeDataToFile($data)
	{
		if ( isset($data) && !is_null($data) ) {
			$fp = fopen('/var/log/CiscoIE3000_ssh.log', 'a');
			fwrite($fp, date('l jS \of F Y h:i:s A'));
			$formattedData = print_r($data, true);
			fwrite($fp, $formattedData);
			fwrite($fp, "\n=========\n");		
			fclose($fp);
		}

	}

	function __destruct()
	{
	   	$this->_deleteCounter();
	}
	
} // end class

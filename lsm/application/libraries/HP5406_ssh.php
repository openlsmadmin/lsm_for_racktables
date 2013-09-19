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
     * HP5406_ssh -  Basic Command set to manage an HP Procurve 5400 switch.
     * PHPSECLIB SSH library required. (http://phpseclib.sourceforge.net)
     * Modify path in the constructor to point to your phpseclib install.
     * 
     **/
    class HP5406_ssh
    {	 
	private $_hostname;
	private $_password;
	private $_username;
	private $_connection;
	private $_ansi;
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
		include_once('File/ANSI.php'); //banner change
	    $this->_hostname = $hostname;
	    $this->_password = $password;
	    $this->_username = $username;
	    
	    //Jan 14.2013 class counter 
	    if ($bconstruct = $this->_checkClassCounter() ) {
             $this->_addCounter();
	    }
	    else{
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
		$counterslocation =  '/var/log/lsmcounters/';;
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

	/**
	* Function: connect
	* Description:  Attempts to connect to the switch.
	* @return false on failure.  sets error message property. 
	* @return true if pass.  sets global ssh object.
	**/
	public function connect() 
	{
	    $ssh = new Net_SSH2($this->_hostname); 
	    $ansi = new File_ANSI();
	    if (!$ssh->login($this->_username, $this->_password)) { //if you can't log on...
				$this->_emess = "Error: Authentication Failed for $this->_hostname";
				return false;		
	    }
	    else  {
				/*
				$output= $ssh->write("\n"); //press any key to continue prompt; ** ohly required for HPs
				$prompt=$ssh->read('/([0-9A-Z\-])*(#)(\s*)/i', NET_SSH2_READ_REGEX); 
				if (!$prompt) {
				    $this->_emess = "Error: Problem connecting for $this->_hostname";
				    return false;		   
				}  
				else {		  
				    $this->_connection = $ssh;				   
				    return true;		
				}
				*/
				//banner changes terminal mode to ansi vs. vt100
						
				$ansi->appendString($ssh->read('/([0-9A-Z\-\=])*(#)(\s*)/i', NET_SSH2_READ_REGEX) );
				$ssh->write("\n");	
				$ansi->appendString($ssh->read('/([0-9A-Z\-\=])*(#)(\s*)/i', NET_SSH2_READ_REGEX) );
				$prompt=htmlspecialchars_decode(strip_tags($ansi->getHistory()));

				if (!$prompt) {
				    $this->_emess = "Error: Problem communication with $this->_hostname";
				    return false;						
				}
				else { 
					$ssh->write("conf t\n");
					$ssh->setTimeout(5);
					$ansi->appendString($ssh->read('/([0-9A-Z\-\=])*(#)(\s*)/i', NET_SSH2_READ_REGEX) );
					$ssh->write("console local-terminal ansi\n");
					$ansi->appendString($ssh->read('/([0-9A-Z\-\=])*(#)(\s*)/i', NET_SSH2_READ_REGEX) );
					$ssh->write("exit\n");
					$ssh->setTimeout(5);
					$ansi->appendString($ssh->read('/([0-9A-Z\-\=])*(#)(\s*)/i', NET_SSH2_READ_REGEX) );
					$this->_connection = $ssh;
					$this->_ansi = $ansi;
					return true;	
				}
	    }
	} // connect
	
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
	
	
	/**
	* Disconnect SSH session
	*/
	public function disconnect()
	{
	  $this->_connection->disconnect();
	  $ssh=NULL;
	  $this->_ansi = null;
	  $_ansi = null;

	}
	
	/**
	* Send Command to switch
	*/
	private function _send($command) 
	{	

	  $commandresults = $this->_connection->write($command."\n");	 
	  $this->_connection->setTimeout(5);//do not remove!!! you need this in order for ssh term not to close too soon

	  if ($commandresults) {
	     	$this->_data = $this->_connection->read('/([0-9A-Z\-\=])*(#)(\s*)/i', NET_SSH2_READ_REGEX);     	     
	  }
	  else {
			$this->_data = false;
	        $this->_emess = "Error: Problem executing command for $command";
	  }
	  return $this->_data;
	} // _send

	/**
	* Turns off paging on switch so you don't have to hit a key to get "more" data.
	*/
	public function nopaging() 
	{
	  $this->_send('no page');
	}

	//get switch logs.
	public function showLogging() 
	{
	    $this->nopaging();
	    $this->_send('show logging -r');
	    if ($this->_data !== false) 
	    {        	   
				$this->_data  = str_replace(chr(27)," ",$this->_data ,$count);//strip out esc character
				$this->_data  = str_replace(chr(13),"",$this->_data ,$count);//strip LF	
				$this->_data  = str_replace(chr(10),'<BR>',$this->_data ,$count);//strip LF	
	    }
	    return $this->_data;
	} 
	
	//TODO:  later, you should accept a parm between 1 & 1440 that respresents duration of light.
	public function chassisLocateLightOn()
	{	  
	    $this->_send('chassislocate on');	    	   
	    return $this->_data;
	}
	
	public function chassisLocateLightOff()
	{	  
	    $this->_send('chassislocate off');	  
	    return $this->_data;
	}	

	public function chassisLocateLightBlink()
	{

	    $this->_send('chassislocate blink');	  
	    return $this->_data;
	}
	
	//TODO:  Add some error handling / checks.
	public function disablePort($port_num)
	{
	    $this->nopaging();
	    $this->_send('config');	  
	    $this->_send('interface '.$port_num);	   
	    $this->_send('disable');	 
	    return $this->_data;
	}
	
	//TODO:  Add some error handling / checks.
	public function enablePort($port_num)
	{
	    $this->nopaging();
	    $this->_send('config');
	    $this->_send('interface '.$port_num);
	    $this->_send('enable');
	    return $this->_data; 
	}
      
	public function showMacAddressTable() 
	{
	    $this->nopaging();
	    $this->_send('show mac-address');
	    $result = array();

	    if ($this->_data !== false) {    
				$this->_data  = str_replace(chr(10)," ",$this->_data ,$count);//strip LF
				$this->_data  = str_replace(chr(13)," ",$this->_data ,$count);//strip CR
				$this->_data  = str_replace(chr(27)," ",$this->_data ,$count);//strip out esc character
				$pattern='/([0-9A-F]{6})-([0-9A-F]{6}) ([0-9A-F]+)(\s+)([0-9]+)/i'; 

				if (preg_match_all($pattern,$this->_data,$matches, PREG_PATTERN_ORDER))  {
				    $this->_data = preg_replace('/\s+/',' ', $matches[0]);	 //remove extra spaces.
				}	    
	    } 
	    return $this->_data;  
	} 


	public function ping($host) 
	{
	    $this->_send("ping $host");
	    $this->_data = explode("\r\n", $this->_data);
	    $this->_data  = str_replace(chr(10)," ",$this->_data ,$count);//strip LF
	    $this->_data  = str_replace(chr(13)," ",$this->_data ,$count);//strip CR
	    $this->_data  = str_replace(chr(27)," ",$this->_data ,$count);//strip out esc character
	    return $this->_data;
	} // ping

	
	//TODO:  This is an interesting one.  Currently doesn't return all the data back from 
	//interactive sessinon.  only the last line. 
	public function traceroute($host) 
	{
	    $this->_send("traceroute $host");
	    $this->_data = explode("\r\n", $this->_data);
	    for ($i = 0; $i < 3; $i++) array_shift($this->_data);
	    array_pop($this->_data);
	    $this->_data = implode("\n", $this->_data);        
	    return $this->_data;
	} // ping


	public function showInterfaceAll() 
	{
	    $this->nopaging();
	    $this->_send('show interfaces brief');
	    
	    if ($this->_data !== false)  {
			    $this->_data = str_replace(chr(27)," ",$this->_data,$count);//strip out esc character
			    $this->_data = explode("\r\n", $this->_data);
			    
			    $portdetailsArray = array();
			    foreach ($this->_data as $portdetails) {	      
					    //   1        2      3               4               5   6   7    8    9    10   11   12   13      14        15    16   17     18    19   20
				  //$pattern = '/(\s+)([0-9a-z]*)(\s+)(100\/1000T|10|\s+)(\s*)(\|)(\s+)(\w+)(\s+)(\w+)(\s+)(\w+)(\s+)(1000FDx|\s+)(\s*)(\w+)(\s*)(\w+|\s+)(\s*)(0)/i';		      
				  $pattern = '/(\s+)([0-9a-z]*)(\s+)(100\/1000T|10|1000SX|\s+)(\s*)(\|)(\s+)(\w+)(\s+)(\w+)(\s+)(\w+)(\s+)(1000FDx|10HDx|100HDx|10FDx|100FDx|\s+)(\s*)(\w+)(\s*)(\w+|\s+)(\s*)(0)/i';		      
				  if (preg_match($pattern, $portdetails, $matches)) {
				  
					  array_push($portdetailsArray, array(
					    'Port' => $matches[2],
					    'Type' => $matches[4],
					    'Alert' => $matches[8],
					    'Enabled' => $matches[10],
					    'Status' => $matches[12],
					    'Mode' => $matches[14],
					    'MDIMode' => $matches[16],
					    'FlowCtrl' => $matches[18],
					    'BcastLimit' => $matches[20]));
		      		}//end if
	      		}//end for	 
	      		$this->_data = $portdetailsArray;		
	    } //end if
	    return $this->_data;
	} // showInterfaceAll
	//Array ( [0] => A1 ~100/1000T ~ No ~Yes ~Down ~1000FDx ~Auto ~off ~0 [1] => A2 ~100/1000T ~ No ~Yes ~Down ~1000FDx ~Auto ~off ~0 [2] => A3 ~100/1000T ~ No ~Yes ~Down ~1000FDx ~Auto ~off ~0 [3] => A4 ~100/1000T ~ No ~Yes ~Down 
	
	  /*
	// List Specific Trunk Interface

	*/

	public function showInterface($port_num) 
	{
	    $this->nopaging();
	    $this->_send('show interface '.$port_num);   
	    if ($this->_data !== false)  {
	    	
	    		$this->_data  = str_replace(chr(27),'',$this->_data ,$count);//strip out esc character
			    $this->_data  = str_replace(chr(10),"<BR>",$this->_data ,$count);//strip New line
			    $this->_data  = str_replace(chr(9),'',$this->_data ,$count);//strip vertical tabs 
			    $this->_data  = str_replace(chr(13),'',$this->_data ,$count);//strip carriage return
			    $this->_data  = str_replace(chr(32).chr(32),chr(32),$this->_data ,$count);//remove all extra white space.	    
			    //pattern for a port that is legit. 
			    $pattern='/([0-9a-z\[\;]\s)*(Status and Counters - Port Counters for port)(\s)+([0-9a-z])*(\s)+([0-9a-z\:\/\s\)\,\(\%\-\.\<\>])+/i';	    
			    if (preg_match_all($pattern,$this->_data,$matches, PREG_PATTERN_ORDER)) {		
			      $this->_data = $matches[0];	      
			    }
			    else {
					//check for error message that port doesn't exist.  it's possible that racktables list is no longer in sync. 
					//Module not present for port or invalid port: D6
					//$pattern='/([0-9a-z\[\;\?]\s)*(Module not present for port or invalid port:)(\s)+([0-9a-z])*(\s)+([0-9a-z\:\/\s\)\(\%\-\.\<\>\?])+/i';
					$pattern='/(Module not present for port or invalid port:)(\s*)([0-9a-z])*/i';
					if (preg_match_all($pattern,$this->_data,$matches, PREG_PATTERN_ORDER)) {		
					    $this->_data = $matches[0];	      
					} //end if
			    }//end if
			  
	    }//end if			
	    return $this->_data;
	} // showPortStatus
	
	/**
	* List VLANs known to the current end device.  
	* @return array|boolean On success returns an array, false on failure.
	*/
	public function showknownvlans() 
	{
	    $this->nopaging();
	    $this->_send('show vlans');
	    
	    if ($this->_data !== false)  {
			$result = array();
			$this->_data  = str_replace(chr(27)," ",$this->_data ,$count);//strip out esc character
			$this->_data = explode("\r\n", $this->_data);
		    	
		    //create emtpy array to hold
		    $vlandetailsArray = array();
		    foreach ($this->_data as $vlandetails) 
		    {
			//		  1    2       3       4        5    6   7      8          9    10   11   12
		      $pattern = '/(\s+)([0-9]+)(\s+)([a-z_0-9]*)(\s*)(\|)(\s+)([a-z0-9_-]*)(\s*)(\w*)(\s*)(\w*)/i';
		      if (preg_match($pattern, $vlandetails, $matches)) {
				    array_push($vlandetailsArray, array(
					'VlanId' => $matches[2],
					'Name' => $matches[4],
					'Status' => $matches[8],
					'Voice' => $matches[10],
					'Jumbo' => $matches[12]));		    		     
		      }// end if
		    }// end for	    
		 	$this->_data = $vlandetailsArray;
	    }	    
	    return $this->_data;
	} // availableVlans
	
	public function showportvlan($portname)
	{
	    $this->_send('show vlan port '.$portname.' detail');

	    if ($this->_data !== false)  {
		    	$this->_data  = str_replace(chr(27)," ",$this->_data ,$count);//strip out esc character
			    $this->_data = explode("\r\n", $this->_data);
			   
			    $vlandetailsArray = array();
			    
			    foreach ($this->_data as $vlandetails) 
			    {
				//		             1    2       3       4        5    6   7      8          9    10   11   12  13    14
				      $pattern = '/(\s+)([0-9]+)(\s+)([a-z_0-9]*)(\s*)(\|)(\s+)([a-z0-9_-]*)(\s*)(\w*)(\s*)(\w*)(\s*)(\w*)/i';
				      if (preg_match($pattern, $vlandetails, $matches)) 
				      {

						    array_push($vlandetailsArray, array(		
							'VlanId' => $matches[2],
							'Name' => $matches[4],
							'Status' => $matches[8],
							'Voice' => $matches[10],
							'Jumbo' => $matches[12],
							'Mode' => $matches[14]));		    		     
				      }// end if			      
			    }	//end for

			    $this->_data = $vlandetailsArray;
	    }//end if	
	    return $this->_data;
	}
	
	public function changeportvlan($portname,$newVlan, $mode)
	{    
	    $this->_send('config');
	    
	    $this->_send('vlan '.$newVlan);
	    $this->_send($mode.' '.$portname);	    
	    return $this->_data;
	 }	

	public function deleteportvlan($portname,$deleteVlan, $mode)
	{    
	    $this->_send('config');
	    $this->_send('no vlan '.$deleteVlan.' '.$mode.' '.$portname); //no vlan 96 Tagged c4 
	    return $this->_data;
	 }		    

	public function saveconfig()
	{    
	    $this->_send('wr mem');
	    return $this->_data;
	 }	

	public function changeduplex($portname, $duplex_value)
	{
	    $this->nopaging();
	    $this->_send('config');
	    $this->_send('interface '.$portname.' speed-duplex '.$duplex_value);
	    $this->_send('enable');
	    return $this->_data; 
	}//changeduplex
	
	/*
	Function poeOff:  just returns prompt.
	*/
	public function poeOn($portname)	
	{
	    $this->nopaging();
	    $this->_send('config');
	    $this->_send('interface '.$portname.' power-over-ethernet');
	    $this->_send('show power-over-ethernet '.$portname);

	    if ($this->_data !== false) {
    		 $this->formatPOEStatusData();
	    }	    
	    return $this->_data;	    
	}//poeOn	
	
	/*
	Function poeOff:  just returns prompt.
	*/
	public function poeOff($portname)	
	{
	    $this->nopaging();
	    $this->_send('config');
	    $this->_send('no interface '.$portname.' power-over-ethernet');
	    $this->_send('show power-over-ethernet '.$portname);

	    if ($this->_data !== false)  {
    		 $this->formatPOEStatusData();
	    }	   
	    return $this->_data;	    
	}
	
	
	  /*
	  Function poeStatus: Returns POE status for a specific port.
	  Returns array with one element. 
	      Result set for No POE:
	      ======================
	      Array ( [0] => Status and Counters - Port Power Status for port A1
		Power Enable : No
		    )
		    
	      Result set for POE Enabled: 
	      ===========================
	      Array ( [0] => Status and Counters - Port Power Status for port A1

	      Power Enable : Yes
	      LLDP Detect : enabled
	      Priority : low Configured Type :
	      AllocateBy : usage Value : 17 W
	      Detection Status : Searching Power Class : 0

	      Over Current Cnt : 0 MPS Absent Cnt : 0
	      Power Denied Cnt : 0 Short Cnt : 0

	      Voltage : 0.0 V Current : 0 mA
	      Power : 0.0 W

	      ) 	      
	      
	  */
	/** 
	*this function is called by all POE command functions.
	**/      
	public function poeStatus($portname)
	{
	    $this->_send('show power-over-ethernet '.$portname);	   
	   
	    if ($this->_data !==false)
	    {
	    	$this->_data  = str_replace(chr(27),'',$this->_data ,$count);//strip out esc character
	    	$this->_data  = str_replace(chr(9),'',$this->_data ,$count);//strip vertical tabs 
	    	$this->_data  = str_replace(chr(13),'',$this->_data ,$count);//strip carriage return
	        $this->formatPOEStatusData();
	    }
	 
	    return $this->_data;	        
	}
	
	/** 
	*this function is called by all POE command functions.
	**/
	function formatPOEStatusData()
	{
	    $pattern='/(Status and Counters - Port Power Status for port)(\s)+([0-9a-z])*(\r\n)*([\w\d\:\s0-9a-z\.]*)/i';	
	    if (preg_match_all($pattern,$this->_data,$matches, PREG_PATTERN_ORDER)) {	
		//strip out CRLF
		$matches[0] = str_replace(chr(10),'<BR>',$matches[0] ,$count);//strip New line
		$this->_data = implode(',',$matches[0]);	//don't return array.  return as string.  
	    }   
	}

	function formatmacAuthStatusData()
	{
  			$pattern='/\s+([0-9a-z]*)\s+(Yes|No)\s+([0-9])/i';	    
	        if (preg_match($pattern, $this->_data, $matches)) {
	        	  	$macAuthStatusDetails = array();

				    array_push(  $macAuthStatusDetails, array(
							'PortNumber' => $matches[1],
							'Enabled' => $matches[2],
							'ClientLimit' => $matches[3]));

				   $this->_data = $macAuthStatusDetails;

		      }// end if	
		      else
		      {
		      	$this->_data = false;
		      }		
	}

	public function macauthOn($portname)	
	{		
	    $this->nopaging();
	    $this->_send('config');
	    $this->_send('aaa port-access mac-based '.$portname);  
	    $this->_send('show port-access ethernet '.$portname.' mac-based config');	   

	    if ($this->_data !== false) {
    		 $this->formatmacAuthStatusData();
	    }	    
	    return $this->_data;	    
	}//macauthOn	
	
	/*
	Function poeOff:  just returns prompt.
	*/
	public function macauthOff($portname)	
	{
	    $this->nopaging();
	    $this->_send('config');
	    $this->_send('no aaa port-access mac-based '.$portname);  
	    $this->_send('show port-access ethernet '.$portname.' mac-based config');	   

	    if ($this->_data !== false)  {
    		 $this->formatmacAuthStatusData();
	    }	   
	    return $this->_data;	    
	}// end macauthOff

	public function macAuthStatus($portname)
	{
	    $this->_send('show port-access ethernet '.$portname.' mac-based config');	   
	   
	    if ($this->_data !==false)
	    {
	    	$this->_data  = str_replace(chr(27),'',$this->_data ,$count);//strip out esc character
	    	$this->_data  = str_replace(chr(9),'',$this->_data ,$count);//strip vertical tabs 
	    	$this->_data  = str_replace(chr(13),'<BR>',$this->_data ,$count);//strip carriage return

    		$this->formatmacAuthStatusData();
	  
	 	}

	    return $this->_data;  
	    //array(1) { [0]=> array(3) { ["PortNumber"]=> string(2) "A1" ["Enabled"]=> string(2) "No" ["ClientLimit"]=> string(1) "1" } }
	}
	
	function logdatafromswitch($datatolog, $methodname)
	{
		$myFile = "/var/log/lsmcounters/".$methodname.".html";
		$fh = fopen($myFile, 'w') or die("can't open file");
		$stringData = $datatolog;
		fwrite($fh, print_r($stringData, true));
		fclose($fh); 	
	}	
	
	function _writeDataToFile($data)
	{
		$fp = fopen('/var/log/HP5406.log', 'w');
		fwrite($fp, $data);
		fwrite($fp, "\n=========\n");		
		fclose($fp);
	}

	function __destruct()
	{
	   $this->_deleteCounter();
	}

} // end class

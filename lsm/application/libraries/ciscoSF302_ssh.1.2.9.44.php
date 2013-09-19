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
     * ciscoSF302v12776_ssh -  Basic Command set to manage a CISCO SF 300 switch.
     * firmware version 1.2.9.44
     * PHPSECLIB SSH library required. (http://phpseclib.sourceforge.net/) 
     * Modify path in the constructor to point to your phpseclib install.
     * 
     **/
    class CiscoSF302_ssh
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
		echo("inside the constructor");
	    set_include_path(get_include_path() . PATH_SEPARATOR . '/var/www/phpseclib');
	    include_once('Net/SSH2.php');
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
		$counterslocation = '/var/log/lsmcounters/'; 
		$this->_countersLocation = $counterslocation;

		// /var/www/lsm/application/counters
		$filelist = glob($counterslocation.$this->_hostname.'*.tmp');

		if ( count($filelist)  < 4) {
			return true;
		}		
		else {
			return false;
		}
	}

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
		try
		{
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
	      try
	      {
			$ssh = new Net_SSH2($this->_hostname); 
		
	      }
	      catch (Exception $ex)
	      {
			$this->_emess = "Error: Problem connecting for $this->_hostname";
			$this->_data = false;		      
	      }
	      try 
	      {
			if (!$ssh->login($this->_username, $this->_password)) { 
					$this->_emess =  "Error: Authentication Failed for $this->_hostname";				
					$this->_data = false;	
			}
					
			$prompt=$ssh->read('/([0-9A-Z\-])*(#)(\s*)/i', NET_SSH2_READ_REGEX); //prompt contains ************ CAN-A2-144-SW-1#

			if (!$prompt) {
				    $this->_emess = "Error: Problem connecting for $this->_hostname";
				    $this->_data = false;		   
			
			}  			
			else
			{			    
			        $pattern='/(\*)*([0-9A-Z\-])*(#)(\s*)/i';					      
				if (preg_match($pattern, $prompt, $matches)) {					
				    $this->_prompt = trim($matches[0]);//prompt contains CAN-A2-144-SW-1#
				}
				//echo 'the prompt i extracted is:'.$this->_prompt;
				$this->_connection = $ssh;				   
				$this->_data = true;		
			 }
	      }
	      catch (Execption $ex)	 
	      {
			$this->_emess = "Error: Problem authenticating user $this->_username for $this->_hostname";
			$this->_data = false;		 
	      }
	      
	    return $this->_data;
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
	}
	
	/**
	* Send Command to switch
	*/
	private function _send($command, $wait=false) 
	{	
	  $commandresults = $this->_connection->write($command."\n");
	  if ($wait){
	  	$this->_connection->setTimeout(3);
	  }
	  $this->_connection->read($command);// this is just to clear the buffer of the command you just send.  only need the results. not the command itself.
	  
	  if ($commandresults) 
	  {	  
	      $this->_data = $this->_connection->read('/([0-9A-Z\-])*(#)(\s*)/i', NET_SSH2_READ_REGEX);     
	      //strip out the prompt from result set.  
	      $pos = strpos($this->_data, $this->_prompt);	      
	      
	      if(!$pos === false)
	      {
		  //found the prompt embedded in results!  Strip it out.
		  $this->_data = substr($this->_data,0,$pos);
	      }	     
	  }
	  else 
	  {
		$this->_data = false;
	        $this->_emess = "Error: Problem executing command for $command";
	  }
	  return $this->_data;
	} // _send

	
	/**
	* 
	*/
	public function nopaging() 
	{
	  $this->_send('terminal datadump');
	  //$this->_readTo($this->_prompt);      
	}
	//get switch logs.
	public function showLogging() 
	{
	    $this->nopaging();
	    $this->_send('show logging');
	    if ($this->_data !== false) {	   
		  $this->_data  = str_replace(chr(27)," ",$this->_data ,$count);//strip out esc character
		  $this->_data  = str_replace(chr(13),"",$this->_data ,$count);//strip LF	
		  $this->_data  = str_replace(chr(10),'<BR>',$this->_data ,$count);//strip LF	
	    }
	    return $this->_data;
	} 
	
	public function saveconfig()
	{    
		
	    $this->_send('wr mem',true);	   
	    $this->_send('Yes', true);

	    return $this->_data;
	}

	//TODO:  Add some error handling / checks.
	public function disablePort($port_num)
	{
	    $this->nopaging();
	    $this->_send('config');	  
	    $this->_send('interface '.$port_num);	   
	    $this->_send('shutdown');	 
	    return $this->_data;
	}
	
	//TODO:  Add some error handling / checks.
	public function enablePort($port_num)
	{
	    $this->nopaging();
	    $this->_send('config');
	    $this->_send('interface '.$port_num);
	    $this->_send('no shutdown');
	    return $this->_data; 
	}
/*
CAN-A2-144-SW-1#show mac address-table
Aging time is 300 sec

  Vlan        Mac Address         Port       Type    
-------- --------------------- ---------- ---------- 
   1       2c:36:f8:4d:02:ad       0         self    
   1       b4:39:d6:b9:d7:00      gi2      dynamic   
   1       b4:39:d6:b9:d7:17      gi2      dynamic   
   3       00:00:5e:00:00:03      gi2      dynamic   
   3       00:17:08:da:cc:01      gi2      dynamic   
   3       00:25:61:e3:21:40      gi2      dynamic   
   3       00:30:18:a5:53:04      gi2      dynamic   
   3       10:bd:18:85:cd:04      gi2      dynamic   
   3       20:3a:07:86:34:01      gi2      dynamic   
   3       68:ef:bd:2c:cd:7f      gi2      dynamic   
   3       88:43:e1:4f:19:ff      gi2      dynamic   
   3       b4:39:d6:b9:48:00      gi2      dynamic   


   	public function showMacAddressTable() 
	{
	    $this->nopaging();
	    $this->_send('show arp');
	    $macaddyarray = array();
	    
	    if ($this->_data !== false) {    
		    $pattern = '/([a-z]{4}\s\d)(\s*)([0-9a\.]*)(\s*)([a-z0-9\:]*)(\s*)([a-z]*)/i';	    
		    if (preg_match_all($pattern, $this->_data, $matches, PREG_PATTERN_ORDER))
		    {	
				$this->_data = preg_replace('/\s+/',' ', $matches[0]);	 //remove extra spaces.
				$this->_data = preg_replace('/vlan /','',$this->_data);	//remove the word "vlan" from vlan column.
		    }	 
	    }   
	    return $this->_data;
	} 	
    */ 
	public function showMacAddressTable() 
	{
	    $this->nopaging();
	    $this->_send('show mac address-table');
	    
	    if ($this->_data !== false) {  
				$this->_data  = str_replace(chr(10)," ",$this->_data ,$count);//strip LF
				$this->_data  = str_replace(chr(13)," ",$this->_data ,$count);//strip CR
				$this->_data  = str_replace(chr(27)," ",$this->_data ,$count);//strip out esc character
				$pattern='/([0-9]+)(\s+)([0-9A-F]{2}):([0-9A-F]{2}):([0-9A-F]{2}):([0-9A-F]{2}):([0-9A-F]{2}):([0-9A-F]{2})(\s+)([0-9A-Z]+)(\s+)([A-Z]+)/i'; 

				if (preg_match_all($pattern,$this->_data,$matches, PREG_PATTERN_ORDER))  {
				    $this->_data = preg_replace('/\s+/',' ', $matches[0]);	 //remove extra spaces.
				}	    
	    } 
	    return $this->_data;  
	} 	
	
	/*
	* Note:  Because these small business cisco switches are so amazing ( = P )... we have to run  
	*	two different commands to get the same info we'd get on the HP with a "show interfaces brief".  
		The output from the configuration command is what we really need, plus the "link state" field 
		from the status command. 
		
		
		Sample Output From "show interfaces configuration"

							      Flow    Admin     Back   Mdix
		Port     Type         Duplex  Speed  Neg      control  State   Pressure Mode
		-------- ------------ ------  -----  -------- -------  -----   -------- ----
		fa1      100M-Copper  Full    100    Enabled  Off      Up      Disabled Auto
		fa2      100M-Copper  Full    100    Enabled  Off      Up      Disabled Auto
		fa3      100M-Copper  Full    100    Enabled  Off      Up      Disabled Auto
		fa4      100M-Copper  Full    100    Enabled  Off      Up      Disabled Auto
		fa5      100M-Copper  Full    100    Enabled  Off      Up      Disabled Auto
		fa6      100M-Copper  Full    100    Enabled  Off      Up      Disabled Auto
		fa7      100M-Copper  Full    100    Enabled  Off      Up      Disabled Auto
		fa8      100M-Copper  Full    100    Enabled  Off      Up      Disabled Auto
		gi1      1G-Combo-C   Full    1000   Enabled  Off      Up      Disabled Auto
		gi2      1G-Combo-C   Full    1000   Enabled  Off      Up      Disabled Auto

						  Flow    Admin 
		Ch       Type    Speed  Neg      control  State 
		-------- ------- -----  -------- -------  ----- 
		Po1         --     --   Enabled  Off      Up      
		Po2         --     --   Enabled  Off      Up      
		Po3         --     --   Enabled  Off      Up      
		Po4         --     --   Enabled  Off      Up      
		Po5         --     --   Enabled  Off      Up      
		Po6         --     --   Enabled  Off      Up      
		Po7         --     --   Enabled  Off      Up      
		Po8         --     --   Enabled  Off      Up      
	
	
	
	
	      Sample output from "show interfaces status"	      

							  Flow Link          Back   Mdix
	      Port     Type         Duplex  Speed Neg      ctrl State       Pressure Mode
	      -------- ------------ ------  ----- -------- ---- ----------- -------- -------
	      fa1      100M-Copper    --      --     --     --  Down           --     --    
	      fa2      100M-Copper    --      --     --     --  Down           --     --    
	      fa3      100M-Copper    --      --     --     --  Down           --     --    
	      fa4      100M-Copper    --      --     --     --  Down           --     --    
	      fa5      100M-Copper    --      --     --     --  Down           --     --    
	      fa6      100M-Copper    --      --     --     --  Down           --     --    
	      fa7      100M-Copper    --      --     --     --  Down           --     --    
	      fa8      100M-Copper    --      --     --     --  Down           --     --    
	      gi1      1G-Combo-C     --      --     --     --  Down           --     --    
	      gi2      1G-Combo-C   Full    100   Enabled  Off  Up          Disabled Off    

							Flow    Link        
	      Ch       Type    Duplex  Speed  Neg      control  State       
	      -------- ------- ------  -----  -------- -------  ----------- 
	      Po1         --     --      --      --       --    Not Present 
	      Po2         --     --      --      --       --    Not Present 
	      Po3         --     --      --      --       --    Not Present 
	      Po4         --     --      --      --       --    Not Present 
	      Po5         --     --      --      --       --    Not Present 
	      Po6         --     --      --      --       --    Not Present 
	      Po7         --     --      --      --       --    Not Present 
	      Po8         --     --      --      --       --    Not Present 
	      
	*/
	public function showInterfaceAll() 
	{
	    $this->nopaging();
	    $this->_send('show interfaces status');
	    
	    if ($this->_data !== false)
	    {
	      //save the data.  now grab the next set of data
		    $interfacesStatusData = $this->_data;
	      	$this->nopaging();
		    $this->_send('show interfaces configuration');
		    if ($this->_data !== false)
		    {
		      $interfacesConfigData = $this->_data;
		    }
		   $continue = true;
	    }
	    
	    if ($continue) 
	    {	        				
			//it takes longer, but just in case we need this code later on, we will capture each individual field returned by each command. 
			//Only a combined subset from the two commands will be consumed by the port status function for now.
			
			$portdetailsArray = array();	//combined data returned to front end.
			$portStatusArray = array();	//contains parsed data from status command. 
			$portConfigArray = array();	//contains parsed data from the config command. 
			
			//parse the status data
			$pattern = '/([a-z]{2}\d)(\s*)([0-9a-z\-]*)(\s*)([a-z\-]*)(\s*)([0-9\-]*)(\s*)([a-z\-]*)(\s*)([a-z\-]*)(\s*)([a-z\-]*)(\s*)([a-z\-]*)(\s*)([a-z\-]*)/i';
			preg_match_all($pattern, $interfacesStatusData, $matches, PREG_SET_ORDER);			
			foreach ($matches as $match) {
				array_push($portStatusArray, array(
				     'Port' => $match[1],
				     'Type' => $match[3],
				     'Duplex' => $match[5],
				     'Speed' => $match[7],
				     'Neg' => $match[9],
				     'FlowCtrl' =>$match[11],
				     'LinkState' => $match[13],
				     'Back Pressure' => $match[15],
				     'Mdix Mode' => $match[17]   ));
			    //echo '<pre>';
			    //var_dump($match);
			    //echo '</pre>';
			}	    	
			
			//parse the config data
			$pattern = '/([a-z]{2}\d)(\s*)([0-9a-z\-]*)(\s*)([a-z\-]*)(\s*)([0-9\-]*)(\s*)([a-z\-]*)(\s*)([a-z\-]*)(\s*)([a-z\-]*)(\s*)([a-z\-]*)(\s*)([a-z\-]*)/i';
			preg_match_all($pattern, $interfacesConfigData, $matches, PREG_SET_ORDER);	
			
			foreach ($matches as $match) {
			
				//filter results.  Don't add any items where the port name starts with Po. If it does start with Po, strpos will return 0. aka 1st position in the
				//port name field. 											
				
				If ( strpos(strtoupper($match[1]), "PO") !== 0)  {
					array_push($portConfigArray, array(
					    'Port' => $match[1],
					    'Type' => $match[3],
					    'Duplex' => $match[5],
					    'Speed' => $match[7],
					    'Neg' => $match[9],
					    'FlowCtrl' =>$match[11],
					    'AdminState' => $match[13],
					    'LinkState' =>'',	//save a spot for link state.
					    'Back Pressure' => $match[15],
					    'Mdix Mode' => $match[17]   ));
				     }
				     
			}
			
			// at this point, we have 2 arrays, hopefully with matching indexes.  loop through config array again, and add in status data. 
			$i=0;
			foreach ($portConfigArray as &$configentry)
			{			
				$configentry['LinkState'] = $portStatusArray[$i]['LinkState'];
				$i = $i + 1; 				
			    
			}
	    } //end if
	    $this->_data = $portConfigArray;
	    return  $this->_data;
	} // showInterfaceAll
		  
	/**
	* List Specific Interface
	* @return array|boolean On success returns an array, false on failure.
	*/
	public function showInterface($port_num) 
	{
	    $this->nopaging();
	    $this->_send('show interfaces counters '.$port_num);
	   
	    if ($this->_data !== false)  {
 			$this->_data  = str_replace(chr(10),"<BR>",$this->_data ,$count);//strip New line
 			$this->_data  = str_replace(chr(13),'',$this->_data ,$count);//strip carriage return		
			$this->_data  = str_replace(chr(9),chr(32),$this->_data ,$count);//strip vertical tabs. replace with spaces
 			$this->_data  = str_replace(chr(32),"&nbsp;",$this->_data ,$count);//remove all extra white space.
	    }
		
	    return $this->_data;
	} // showPortStatus
	
	/*
	* List VLANs known to the current end device.  
	* @return array|boolean On success returns an array, false on failure.
	
	Sample data from switch: 
	
	  CAN-A2-144-SW-1#show vlan

	  Vlan       Name                   Ports                Type     Authorization 
	  ---- ----------------- --------------------------- ------------ ------------- 
	  1           1              fa5-8,gi1-2,Po1-8        Default      Required    
	  3           3                     gi2                static      Required    
	  59         59                  fa1-4,gi2             static      Required    
	  70         70                     gi2                static      Required    
	  80         80                   fa2,gi2              static      Required    	
	*/
	public function showknownvlans() 
	{
	    $this->nopaging();
	    $this->_send('show vlan');

	    //create emtpy array to hold
	    $vlandetailsArray = array();
	    
	    if ($this->_data !== false)  {
		      $result = array();
		      $this->_data = str_replace(chr(10),'',$this->_data ,$count);//strip LF
		      $this->_data= str_replace(chr(13),'<BR>',$this->_data,$count);//strip CR		      
		      $this->_data = explode("<BR>", $this->_data);	
			
		      foreach ($this->_data as $vlandetails) 
		      {		     
			    //              1      2     3      4        5         6      7        8       9
			    $pattern = '/([0-9]+)(\s*)([0-9]*)(\s*)([a-z0-9\,\-]*)(\s*)([a-z0-9]*)(\s*)([a-z0-9]*)(\s*)/i';
			    if (preg_match($pattern, $vlandetails, $matches)) {						
					array_push($vlandetailsArray, array(				      
				      'VlanId'=> $matches[1],				    
				      'Name'=> $matches[3],				      
				      'Ports'=> $matches[5],				      
				      'Type'=> $matches[7],				    
				      'Authorization'=> $matches[9]));
			    }//end if 			      
		      }// end for	    		
		      $this->_data = $vlandetailsArray;
	  }	    
	    return $this->_data;
	} // showknownvlans

	
	/*
	  sample data returned by "show interface switchport PORT#"
	  
		Port : fa2
		Port Mode: Trunk
		Gvrp Status: disabled
		Ingress Filtering: true
		Acceptable Frame Type: admitAll
		Ingress UnTagged VLAN ( NATIVE ): 80
		 
		Port is member in: 
		 
		Vlan               Name               Egress rule Port Membership Type 
		---- -------------------------------- ----------- -------------------- 
		 59                 59                  Tagged           Static        
		 80                 80                 Untagged          Static        

		 
		Forbidden VLANS: 
		Vlan               Name               
		---- -------------------------------- 

		 
		Classification rules: 
		 
		Mac based VLANs:                                      
		  Group ID   Vlan ID 
		------------ -------       
			  
	*/
	public function showportvlan($portname)
	{
	   $vlandetailsArray = array();
	   
	   $this->nopaging();
	   $this->_send('show interfaces switchport '.$portname);

	   if ($this->_data !== false)
	   {
			$pattern = "/([0-9]+)(\s+)([0-9]+)(\s+)(Tagged|Untagged)+(\s+)(Static|System)+(\s+)/i";
			preg_match_all($pattern, $this->_data, $matches, PREG_SET_ORDER);
			foreach ($matches as $match) {
				array_push($vlandetailsArray, array(		
								'VlanId' =>  $match[1],
								'Name' => $match[3],
								'Mode'=> $match[5],
								'Type' => $match[7]  ) );				  	
			}//end for
			$this->_data = $vlandetailsArray;
		}//end if
	   return $this->_data;
	}

	public function changeportvlan($portname,$newVlan, $mode)
	{    

		$this->nopaging();
	    $this->_send('show interfaces switchport '.$portname);  	    
	    if ($this->_data !== false) {

	    	//1.  get port mode - access or trunk. 
	    	$pattern = "/(Port Mode\:)(\s+)(Trunk|General)/i";
	    	if (preg_match($pattern, $this->_data,$matches)) {

	    		switch (strtoupper( $matches[3]) ){

	    			case 'TRUNK':	    				
	    				//what is mode?  Untagged or tagged? 
	    			    if (strtoupper($mode) == 'UNTAGGED'){
	    			    	$this->_send('conf t');  
	   						$this->_send('interface '.$portname);
	    			    	$this->_send('switchport trunk native vlan '.$newVlan);  	    
	    			    }
	    			    else {
	    			    	$this->_send('conf t');  
	   						$this->_send('interface '.$portname);	    			    	
	    			    	$this->_send('switchport trunk allowed vlan add '.$newVlan);  
	    			    }
	    				break;

	    			case 'GENERAL':
	    		
	    			    if (strtoupper($mode) == 'UNTAGGED'){
	    			    	$this->_send('conf t');  
	   						$this->_send('interface '.$portname);	    			    	
	    			    	$this->_send('switchport general allowed vlan add '.$newVlan. ' untagged');  	  //this will add an additional UNTAGGED vlan 
	    			    }
	    			    else {
		    			    if (strtoupper($mode) == 'TAGGED'){		    			   
		    			    	$this->_send('conf t');  
		   						$this->_send('interface '.$portname);
		    			    	$this->_send('switchport general allowed vlan add '.$newVlan. ' tagged');  	  //this will add an additional UNTAGGED vlan 
		    			    }	    			    	 
	    			    }	    			
	    				break;
	    		}//end switch
	    	}//end if preg_match
    		else{
    			$this->_data = false;
    		}
	    }//end if data check
	    $this->_send('exit');  //exit config interface
	    $this->_send('exit');  //exit config.
	    return $this->_data;
	 }	

	public function deleteportvlan($portname,$deleteVlan, $mode)
	{    
		$this->nopaging();
	    $this->_send('show interfaces switchport '.$portname);  	    
	    if ($this->_data !== false) {

	    	//1.  get port mode - access or trunk. 
	    	$pattern = "/(Port Mode\:)(\s+)(Trunk|General)/i";
	    	if (preg_match($pattern, $this->_data,$matches)) {
	    		switch (strtoupper( $matches[3]) ){
	    			case 'TRUNK':	
	    				If (strtoupper($mode)=='UNTAGGED') {
							$this->_data = false;
						}
						else {
							print 'trying to delete tagged vlan<BR>';
	    			    	$this->_send('conf t');  
	   						$this->_send('interface '.$portname);	    			    	
	    			    	$this->_send('switchport trunk allowed vlan remove '.$deleteVlan);  
	    			    	$this->_send('exit');
      						$this->_send('exit');	    			    	
						}    					    			    
	    				break;

	    			case 'GENERAL':
	    			    	$this->_send('conf t');  
	   						$this->_send('interface '.$portname);	    			    	
	    			    	$this->_send('switchport general allowed vlan remove '.$deleteVlan);  
							$this->_send('exit');
      						$this->_send('exit');	 
	    			 		break;    	    			     	    			
	    			default:  
	    				$this->_data = false; 			
	    				break;
	    			}//end switch
	    	}//end if for regex
	    }
      	//end if data check

	    return $this->_data;
	 }		    	 	 
	  /*Note:  cisco sf300 has separate command to set duplex and speed.

	  */	 
	public function changeduplex($portname, $duplex_value)
	{
	    switch ($duplex_value)  {
		case '10-half':
		      $this->nopaging();
		      $this->_send('config');
		      $this->_send('interface '.$portname);
		      $this->_send('speed 10');
		      $this->_send('duplex half');		    
		      break;
		case '100-half':
		      $this->nopaging();
		      $this->_send('config');
		      $this->_send('interface '.$portname);
		      $this->_send('speed 100');
		      $this->_send('duplex half');		    
		      break;		
		case '10-full':
		      $this->nopaging();
		      $this->_send('config');
		      $this->_send('interface '.$portname);
		      $this->_send('speed 10');
		      $this->_send('duplex full');		    
		      break;		
		case '100-full':
		      $this->nopaging();
		      $this->_send('config');
		      $this->_send('interface '.$portname);
		      $this->_send('speed 100');
		      $this->_send('duplex full');		    
		      break;		
		case 'auto':
		      $this->nopaging();
		      $this->_send('config');
		      $this->_send('interface '.$portname);
		      $this->_send('no speed');
		      $this->_send('no duplex');		    
		      break;		
		case 'auto-10':
		      $this->nopaging();
		      $this->_send('config');
		      $this->_send('interface '.$portname);
		      $this->_send('speed 10');
		      $this->_send('no duplex');		    
		      break;		
		case 'auto-100':
		      $this->nopaging();
		      $this->_send('config');
		      $this->_send('interface '.$portname);
		      $this->_send('speed 100');
		      $this->_send('no duplex');		    
		      break;		
		case 'auto-1000':
		      $this->nopaging();
		      $this->_send('config');
		      $this->_send('interface '.$portname);
		      $this->_send('speed 1000');
		      $this->_send('no duplex');		    
		      break;		
	    }
	    return $this->_data; 
	}//changeduplex
	
	/*
	Function poeOff:  just returns prompt.
	*/
	public function poeOn($portname)	
	{
	    $this->nopaging();
	    $this->_send('config');
	    $this->_send('interface '.$portname);
	    $this->_send('power inline auto');
	    //exit interface config mode
	    $this->_send('exit');
	    //exit config mdoe
	    $this->_send('exit');
	    $this->_send('show power inline '.$portname);

	    if ($this->_data !== false)  {
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
	    $this->_send('interface '.$portname);
	    $this->_send('power inline never');
	    //exit interface config mode
	    $this->_send('exit');
	    //exit config mdoe
	    $this->_send('exit');
	    $this->_send('show power inline '.$portname);
	    
	    if ($this->_data !== false)  {
    		 $this->formatPOEStatusData();
	    }	   
	    return $this->_data;	    
	}
	
	
	/** 
	*this function is called by all POE command functions.
	**/      
	public function poeStatus($portname)
	{
	    $this->nopaging();
	    $this->_send('show power inline '.$portname);   
	    
	    //9.25.2012 Add Global Logging. 
	    if ($this->_data !==false) {
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
	    $this->_data = str_replace(chr(10).chr(10),chr(10),$this->_data ,$count);//strip New line
	    $this->_data = str_replace(chr(10),'<BR>',$this->_data ,$count);//strip New line
	    $this->_data = $this->_data. "<BR><BR>";
	}

	//debug purposes only
	function _writeDataToFile($data)
	{
		$fp = fopen('/var/log/CiscoSF302_ssh.log', 'w');
		fwrite($fp, $data);
		fwrite($fp, "\n=========\n");		
		fclose($fp);
	}

	function __destruct()
	{
	   	$this->_deleteCounter();
	}
	
    } // end class
class hp5406 
{
    
    private $_hostname;
    private $_password;
    private $_username;
    private $_connection;
    private $_data;
    private $_timeout;
    private $_prompt;

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
 
        $this->_hostname = $hostname;
        $this->_password = $password;
        $this->_username = $username;
        $this->_timeout = $timeout;
    } // __construct

    /**
     * Establish a connection to the device
     */
    public function connect() 
    {
        $this->_connection = fsockopen($this->_hostname, 23, $errno, $errstr, $this->_timeout);
        if ($this->_connection === false) {
            die("Error: Connection Failed for $this->_hostname\n");
        } // if
        stream_set_timeout($this->_connection, $this->_timeout);
        $this->_readTo(':');
        $this->_send($this->_password);
        $this->_prompt = '#';
        $this->_readTo($this->_prompt);
        if (strpos($this->_data, $this->_prompt) === false) {
            fclose($this->_connection);
            die("Error: Authentication Failed for $this->_hostname\n");
        } // if
    } // connect

    /**
     * Close an active connection
     */
    public function close() 
    {
        $this->_send('quit');
        fclose($this->_connection);
    } // close

    /**
     * Issue a command to the device
     */
    private function _send($command) 
    {
	//fputs is an alias of fwrite
	//fwrite() writes the contents of string to the file stream pointed to by handle. 
        fputs($this->_connection, $command . "\r\n");
    } // _send

    /**
     * Read from socket until $char
     * @param string $char Single character (only the first character of the string is read)
     */
    private function _readTo($char) 
    {
        // Reset $_data
        $this->_data = "";
        while (($c = fgetc($this->_connection)) !== false) {
            $this->_data .= $c;
            if ($c == $char[0]) break;
            if ($c == '-') {
                // Continue at --More-- prompt
                if (substr($this->_data, -8) == '--More--') fputs($this->_connection, ' ');
            } // if
        } // while
        // Remove --More-- and backspace
        $this->_data = str_replace('--More--', "", $this->_data);
        $this->_data = str_replace(chr(8), "", $this->_data);
        // Set $_data as false if previous command failed.
        if (strpos($this->_data, '% Invalid input') !== false) $this->_data = false;
    } // _readTo

    /**
     * Enable (enter privileged user mode)
     * @param  string  $password Enable password
     * @return boolean True on success  
     */
    public function enable($password) 
    {
        $result = false;
        if ($this->_prompt != '#') {
            $this->_send('enable');
            $this->_readTo(':');
            $this->_send($password);
            if ($this->_data !== false) {
                $this->_prompt = '#';
                $result = true;
            } // if
            $this->_readTo($this->_prompt);
            return $result;
        } // if
    } // enable

    /**
     * Disable (exit privileged user mode if enabled)
     */
    public function disable() 
    {
	/*TODO:  modify so that you check results. 
	If successful, this is the data you get back.
	[24;1H[24;25H[24;1H[?25h[24;25H[24;25Henable[24;25H[?25h[24;31H[24;0HE[24;1H[24;31H[24;1H[2K[24;1H[?25h[24;1H[1;24r[24;1H[1;24r[24;1H[24;1H[2K[24;1H[?25h[24;1H[24;1HCAESA2-JLEE-1(eth-D24)#
	*/
        if ($this->_prompt == '#') {
            $this->_send('disable');
            $this->_prompt = '>';
            $this->_readTo($this->_prompt);
            
        } // if
    } // disable

    
      /**
     * Turn of paging
     */
    
    public function nopaging() 
    {
       $this->_send('no page');
       $this->_readTo($this->_prompt);
  
    }
 
    public function showLogging() 
    {
	$this->nopaging();
        $this->_send('show logging');
        $this->_readTo($this->_prompt);
        if ($this->_data !== false) {        
	    $this->_data  = str_replace(chr(27)," ",$this->_data ,$count);//strip out esc character
	    $this->_data  = str_replace(chr(10),'<br>',$this->_data ,$count);//strip LF
	    $this->_data  = str_replace(chr(13)," ",$this->_data ,$count);//strip LF
	 }
        return $this->_data;
    } 

    //TODO:  Add some error handling / checks.
    public function disablePort($port_num)
    {
	$this->nopaging();
        $this->_send('config');
        $this->_readTo($this->_prompt);
        $this->_send('interface '.$port_num);
        $this->_readTo($this->_prompt);
        $this->_send('disable');
        $this->_readTo($this->_prompt);
        return $this->_data;
    }
    
    //TODO:  Add some error handling / checks.
    public function enablePort($port_num)
    {
   	$this->nopaging();
        $this->_send('config');
        $this->_readTo($this->_prompt);
        $this->_send('interface '.$port_num);
        $this->_readTo($this->_prompt);
        $this->_send('enable');
        $this->_readTo($this->_prompt);
        return $this->_data; 
    }
    public function showArp() 
    {	
	$this->nopaging();
        $this->_send('show arp');
        $this->_readTo($this->_prompt);
        $result = array();
        $this->_data = explode("\r\n", $this->_data);
        
        for ($i = 0; $i < 2; $i++) array_shift($this->_data);
        array_pop($this->_data);
        foreach ($this->_data as $entry) {
            $temp = sscanf($entry, "%s %s %s %s %s %s");
            $entry = array();
            $entry['ip'] = $temp[1];
            $entry['mac_address'] = $temp[3];
            if ($temp[2] == '-') $temp[2] = '0';
            $entry['age'] = $temp[2];
            if ($entry['ip'] != 'Address' && $entry['mac_address'] != 'Incomplete') {
                array_push($result, $entry);
            } // if
        } // foreach
        $this->_data = $result;
        return $this->_data;
    } // showArp

   
    public function showMacAddressTable() 
    {
	$this->nopaging();
        $this->_send('show mac-address');
        $this->_readTo($this->_prompt);
        $result = array();
        if ($this->_data !== false) {        
            $this->_data  = str_replace(chr(10)," ",$this->_data ,$count);//strip LF
	    $this->_data  = str_replace(chr(13)," ",$this->_data ,$count);//strip CR
	    $this->_data  = str_replace(chr(27)," ",$this->_data ,$count);//strip out esc character
	    //$pattern='/([0-9A-F]{6})-([0-9A-F]{6}) [0-9]/i'; //HP 2824
	     $pattern='/([0-9A-F]{6})-([0-9A-F]{6}) ([0-9A-F]{3})\s{1,}([0-9]{1,})/i'; //HP Procurve 5406zl
	     
	    //echo "<BR><BR>the pattern we are using is: ".$pattern."<BR>";

	    if (preg_match_all($pattern,$this->_data,$matches, PREG_PATTERN_ORDER))
	    {
	         //print_r($matches[0]);		
		return $matches[0];
		
		//TODO: loop through matches and only return all items in matches[0].
	    }	    
	}   
    } 
   /*
   
   NB:  Because we are using PREG_PATTER_ORDER, the first element in the array contains all the matches. 
  
   Array ( [0] => 00000c-07ac21 1 [1] => 0001e6-aff383 1 [2] => 0004f2-32ceb9 1 [3] => 000ffe-fcc799 1 [4] => 000ffe-fcc811 1 [5] => 000ffe-fcc91f 1 [6] => 000ffe-fcc92d 1 [7] => 000ffe-fcc9df 1 [8] => 000ffe-fcc9f4 1 [9] => 000ffe-fcca01 1 [10] => 000ffe-fcca15 1 [11] => 000ffe-fcca25 1 [12] => 000ffe-fcca85 1 [13] => 000ffe-fccabe 1 [14] => 000ffe-fccaf7 1 [15] => 000ffe-fccafb 1 [16] => 000ffe-fccb0d 1 [17] => 000ffe-fccb10 1 [18] => 000ffe-fccb14 1 [19] => 000ffe-fccbc9 1 [20] => 000ffe-fd0261 1 [21] => 001279-477a13 1 [22] => 0019db-54a247 1 [23] => 00206b-7a81fe 1 [24] => 00206b-7d05dd 1 [25] => 002324-1f7c9e 1 [26] => 002481-93da21 1 [27] => 002481-f461af 1 [28] => 002481-f6320d 1 [29] => 002481-f6abaa 1 [30] => 002481-f6ac2b 1 [31] => 00c0b7-5c9b2b 1 [32] => 080027-86132a 1 [33] => 080027-921eb2 1 [34] => 080027-fa1f64 1 [35] => 0e0811-3851cc 1 [36] => 2c4138-0a660d 1 [37] => 68efbd-2ccd7f 1 [38] => 78acc0-a27952 1 [39] => 78acc0-a2795e 1 [40] => 78acc0-a659cc 1 [41] => 80c16e-f0ed31 1 [42] => 80c16e-
f0ed64 1 [43] => 80c16e-f2ed3e 1 [44] => 80c16e-f2ed3f 1 [45] => 80c16e-f2ed41 1 [46] => 80c16e-f7961b 1 [47] => 80c16e-f7961d 1 [48] => 80c16e-f7962b 1 [49] => 80c16e-f7966d 1 [50] => 8843e1-4f19ff 1 [51] => c09134-b69c9a 1 ) 
   */

    /**
     * Ping (execute an IOS "ping $host" command)
     * @param  string         $host The hostname or IP address to ping.
     * @return string|boolean On success returns the string output of the command, false on failure.
     */
    public function ping($host) 
    {
        $this->_send("ping $host");
        $this->_readTo($this->_prompt);
        $this->_data = explode("\r\n", $this->_data);
        for ($i = 0; $i < 3; $i++) array_shift($this->_data);
        array_pop($this->_data);
        $this->_data = implode("\n", $this->_data);        
        return $this->_data;
    } // ping

    /**
     * Traceroute (execute an IOS "traceroute $host" command)
     * @param  string         $host The hostname or IP address to trace to.
     * @return string|boolean On success returns the string output of the command, false on failure.
     */
    public function traceroute($host) 
    {
        $this->_send("traceroute $host");
        $this->_readTo($this->_prompt);
        $this->_data = explode("\r\n", $this->_data);
        for ($i = 0; $i < 3; $i++) array_shift($this->_data);
        array_pop($this->_data);
        $this->_data = implode("\n", $this->_data);        
        return $this->_data;
    } // ping

    /**
     * List Trunk Interfaces
     * @return array|boolean On success returns an array, false on failure.
     */
    public function showInterfaceAll() 
    {
        $this->nopaging();
        $this->_send('show interfaces brief');
        $this->_readTo($this->_prompt);
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
		  }

	  }
	
	//print_r($portdetailsArray);    
	  
	  return $portdetailsArray;
    } // showPortStatus
    //Array ( [0] => A1 ~100/1000T ~ No ~Yes ~Down ~1000FDx ~Auto ~off ~0 [1] => A2 ~100/1000T ~ No ~Yes ~Down ~1000FDx ~Auto ~off ~0 [2] => A3 ~100/1000T ~ No ~Yes ~Down ~1000FDx ~Auto ~off ~0 [3] => A4 ~100/1000T ~ No ~Yes ~Down 
    
      /**
     * List Specific Trunk Interface
     * @return array|boolean On success returns an array, false on failure.
     */
    public function showInterface($port_num) 
    {
        $this->nopaging();
        $this->_send('show interface '.$port_num);
        $this->_readTo($this->_prompt);
	$this->_data  = str_replace(chr(27)," ",$this->_data ,$count);//strip out esc character
	$this->_data  = str_replace(chr(10),'<br>',$this->_data ,$count);//strip LF
	$this->_data  = str_replace(chr(13)," ",$this->_data ,$count);//strip LF
	
			    
	$pattern='/(\[24;[0-9]h|\[\?25)(\s*|\x0B)/i';
	//$replace = {
	if (preg_match_all($pattern,$this->_data,$matches, PREG_PATTERN_ORDER)) {
	    //print_r($matches[0]);
	}
		    
        return $this->_data;
    } // showPortStatus
    
    /**
     * List VLANs known to the current end device.  
     * @return array|boolean On success returns an array, false on failure.
     */
    public function showknownvlans() 
    {
        $this->_send('show vlans');
        $this->_readTo($this->_prompt);
        $result = array();
        $this->_data  = str_replace(chr(27)," ",$this->_data ,$count);//strip out esc character
        $this->_data = explode("\r\n", $this->_data);
	
	//create emtpy array to hold
	$vlandetailsArray = array();
       
	foreach ($this->_data as $vlandetails) {
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
        return $vlandetailsArray;
    } // availableVlans

    
    public function showportvlan($portname)
    {
	if (trim($portname)==''){
	
	    return false;
	}
	
        $this->_send('show vlan port '.$portname);
        $this->_readTo($this->_prompt);
        $this->_data  = str_replace(chr(27)," ",$this->_data ,$count);//strip out esc character
        $this->_data = explode("\r\n", $this->_data);
	
	$vlandetailsArray = array();
	
	foreach ($this->_data as $vlandetails) {
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
	   
        }	
	return $vlandetailsArray;
    }
    
    public function changeduplex($portname, $duplex_value)
    {
    	if (trim($portname)==''){
	
	    return false;
	}
	
   	$this->nopaging();
        $this->_send('config');
        $this->_readTo($this->_prompt);
        $this->_send('interface '.$portname.' speed-duplex '.$duplex_value);
        $this->_readTo($this->_prompt);
        $this->_send('enable');
        $this->_readTo($this->_prompt);
        return $this->_data; 
    
    }
    
} // end class
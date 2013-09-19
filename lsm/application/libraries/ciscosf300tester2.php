<?php

error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
print 'SF-300 Tests  -  testing connection to can branch...<BR>';
set_include_path(get_include_path() . PATH_SEPARATOR . '/var/www/phpseclib');
include('Net/SSH2.php');
define('NET_SSH2_LOGGING', NET_SSH2_LOG_COMPLEX); 
//$password = 'roguebwlch669968';
//$username="jbilyk";
$password = 'd0459ab5b25f346e';
$username="lsm";

//$hostname = 'B-25-6R207-SW-1.usa.jwm2.net';
$hostname = 'can-a2-152-SW-3.can.jwm2.net';
//$hostname = 'can-r1-f108-sw-1.can.jwm2.net'; //1.2.7.76
//$password = 'fe8CC+ad4aA9'; 
//$username="cannetworkinfra";



/*Connecting to 1.2.7.76 firmware

$ssh = new Net_SSH2($hostname); 

$ssh->login('user');
$ssh->read('User Name:'); //by reading, clearing the input buffer
$ssh->write($username."\n");
$ssh->read('Password:');//by reading, clearing the input buffer
$ssh->write($password."\n");
			
	$prompt=$ssh->read('/([0-9A-Z\-])*(#)(\s*)/i', NET_SSH2_READ_REGEX); //prompt contains ************ CAN-A2-144-SW-1#

	if (!$prompt) {
		    echo "Error: Problem getting prompt for $hostname";
			    $log = $ssh->getLog();
			    $ssh->disconnect();
			    echo "<pre>".$log."</pre>"; 
	
	}
	else
	{
		$ssh->write("terminal datadump \n");
		$ssh->read('/([0-9A-Z\-])*(#)(\s*)/i', NET_SSH2_READ_REGEX);
                echo 'data returned: <BR>';
		$ssh->write("sh interfaces switchport fa1 \n");
		$output = $ssh->read('/([0-9A-Z\-])*(#)(\s*)/i', NET_SSH2_READ_REGEX);
                echo 'data returned: '.$output ."<BR>";
		$ssh->disconnect();
	}

		

// logic to connect to firmware  SW version    1.2.9.44 ( date  30-Sep-2012 time  01:33:07 )
//Boot version    1.0.0.4 ( date  08-Apr-2010 time  16:37:57 )

$ssh = new Net_SSH2($hostname); 	

	if (!$ssh->login($username, $password)) { //if you can't log on...

		echo "Error: Authentication Failed for $this->_hostname";				
			    $log = $ssh->getLog();
		    $ssh->disconnect();
		    echo "<pre>".$log."</pre>"; 

		return false;		
}
	else
	{			    
		 echo $prompt . "<BR>";	  
		 $ssh->setTimeout(0);
		 echo "sending terminal datadump command ...<BR>";
		  $ssh->write("terminal datadump\n");
		 $ssh->read('/([0-9A-Z\-])*(#)(\s*)/i', NET_SSH2_READ_REGEX); 
		 $ssh->write("show interfaces configuration\n");

	   	 $ssh->write("show interfaces configuration\n");
	   	 $ssh->read('/([0-9A-Z\-])*(#)(\s*)/i', NET_SSH2_READ_REGEX);
	   	 $output = $ssh->read('/([0-9A-Z\-])*(#)(\s*)/i', NET_SSH2_READ_REGEX);
	   	 echo 'data returned: <pre>'.$output ."<pre>";
	    //$log = $ssh->getLog();
	    $ssh->disconnect();
	    //echo "<pre>".$log."</pre>"; 
	}

*/
	print(1);
	$ssh = new Net_SSH2($hostname); 	
	print(2);
	if (!$ssh->login($username, $password)) { //if you can't log on...
	print(3);
		echo "Error: Authentication Failed for $this->_hostname";				
			    $log = $ssh->getLog();
		    $ssh->disconnect();
		    echo "<pre>".$log."</pre>"; 

		return false;		
}
	else
	{			    		 
		/*
		  $commandresults = $ssh->write("terminal datadump\n");
		  $ssh->read("terminal datadump"); //clear buffer
		  if ($commandresults) {

		  	 $data = $ssh->read('/([0-9A-Z\-])*(#)(\s*)/i', NET_SSH2_READ_REGEX);
		  	 echo "<pre>".$data."</pre>";
		  }
		 
		 //$ssh->setTimeout(0);
		 echo "sending show interface status...<BR>";
		 $commandresults = $ssh->write("show interface status\n");
		  $ssh->read("show interface status"); //clear buffer
		  if ($commandresults) {
		  	 $data = $ssh->read('/([0-9A-Z\-])*(#)(\s*)/i', NET_SSH2_READ_REGEX);
		  	 echo "<pre>".$data."</pre>";
		  }


		   echo "<BR>sending show interface configuration...<BR>";
		  $commandresults = $ssh->write("show interface configuration\n");
		  $ssh->read("show interface configuration"); //clear buffer
		  if ($commandresults) {

		  	 $data = $ssh->read('/([0-9A-Z\-])*(#)(\s*)/i', NET_SSH2_READ_REGEX);
		  	 echo "<pre>".$data."</pre>";
		  }
			$log = $ssh->getLog();
			//$ssh->disconnect();
			echo "<pre>".$log."</pre>"; 
		  
*/
		  
		   //$ssh->setTimeout(10);

		  $commandresults = $ssh->write("terminal datadump\n");
		  $ssh->read("terminal datadump"); //clear buffer
		  if ($commandresults) {
		  	echo "<BR>The command was a success<BR>";
		  	 $data = $ssh->read('/([0-9A-Z\-])*(#)(\s*)/i', NET_SSH2_READ_REGEX);
		  	 echo "<pre>THIS IS THE DATA".$data."</pre>";
		  }

		  $ssh->setTimeout(10);

  		   $command = "show interface switchport fa3";
		   //$command = "show interfaces status"; //works
		   //$command = "show logging";
		   echo "<BR>sending ".$command."...<BR>";		 

		   $commandresults = $ssh->write($command."\n");
		   var_dump($commandresults);
//sleep(5);

		   $ssh->read($command); //clear buffer
echo 1;		   
 		   $data = $ssh->read('/([0-9A-Z\-])*(#)(\s*)/i', NET_SSH2_READ_REGEX);
		   echo "<pre>".$data."</pre>";		  	
			$ssh->disconnect();

	}
<?php

error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
print 'SF-300 Tests  -  testing connection to usa branch...<BR>';
set_include_path(get_include_path() . PATH_SEPARATOR . '/var/www/phpseclib');
include('Net/SSH2.php');
define('NET_SSH2_LOGGING', NET_SSH2_LOG_COMPLEX); 
//$password = 'roguebwlch669968';
//$username="jbilyk";
$password = 'd0459ab5b25f346e';
$username="lsm";

//$hostname = 'B-25-6R207-SW-1.usa.jwm2.net';
//$hostname = 'can-a2-152-SW-3.can.jwm2.net';
$hostname = 'can-r1-f108-sw-1.can.jwm2.net';
//$password = 'fe8CC+ad4aA9';
//$username="cannetworkinfra";

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
			 echo $prompt . "<BR>";	  
			 echo "sending terminal datadump command ...<BR>";
		   	 $ssh->write("terminal datadump\n");
		   	 $ssh->read('/([0-9A-Z\-])*(#)(\s*)/i', NET_SSH2_READ_REGEX);
		   	 echo 'prompt after terminal length cmd: '.$prompt ."<BR>";
		   	 echo "sending mac address command ...<BR>";
		   	 $ssh->write("show mac address-table\n");
		   	 //$ssh->write("show ver\n");
		   	 $output = $ssh->read('/([0-9A-Z\-])*(#)(\s*)/i', NET_SSH2_READ_REGEX);
		   	 echo 'data returned: '.$output ."<BR>";
			    $log = $ssh->getLog();
			    $ssh->disconnect();
			    echo "<pre>".$log."</pre>"; 
		}





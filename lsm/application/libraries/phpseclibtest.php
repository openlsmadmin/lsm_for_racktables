<?php
/*
echo "CONNECTING TO MY TEST HP SWITCH<BR>";
echo "===================================<BR>";
set_include_path(get_include_path() . PATH_SEPARATOR . '/var/www/phpseclib');
 
//include('Net/SSH2.php');

include('/var/www/phpseclib/Net/SSH2.php');
 //add near include lines
 


$hp = new Net_SSH2('10.14.3.44');
if (!$hp->login('CANNetworkInfra', 'fe8CC+ad4aA9')) {
    exit('Login Failed');
}

echo $hp->write('\n');

echo $hp->write('show mac-address');
echo $hp->read('/([0-9A-Z\-])*(#)(\s*)/i', NET_SSH2_READ_REGEX);  
echo $hp->data;
echo $hp->disconnect();

echo "<br><br>";




<?php
echo "CONNECTING TO MY TEST CISCO SWITCH<BR>";
echo "===================================<BR>";
set_include_path(get_include_path() . PATH_SEPARATOR . '/var/www/phpseclib');
include('/var/www/phpseclib/Net/SSH2.php');
define('NET_SSH2_LOGGING', true);

$cisco = new Net_SSH2('10.14.3.45');
if (!$cisco->login('CANNetworkInfra', 'fe8CC+ad4aA9')) {
	
    exit('Login Failed');
$log = $cisco->getLog(NET_SSH2_LOG_COMPLEX);
}

//echo $cisco->write('help');
//echo $cisco->read('/([0-9A-Z\-])*(#)(\s*)/i', NET_SSH2_READ_REGEX);  
//$log = $cisco->getLog(NET_SSH2_LOG_COMPLEX);
//echo $cisco->disconnect();

foreach ($log as $logitem)  {
echo $logitem.'<br>';
}
*/
?>
<?php
		set_include_path(get_include_path() . PATH_SEPARATOR . '/var/www/phpseclib');
		include('Net/SSH2.php');
		define('NET_SSH2_LOGGING', NET_SSH2_LOG_COMPLEX); //turn on logging.

		$ssh = new Net_SSH2('10.14.3.45'); //starting the ssh connection to localhost
		if (!$ssh->login('CANNetworkInfra', 'fe8CC+ad4aA9')) { //if you can't log on...
		   echo('Login Failed');
			echo 'Error message is: <br>';
			$log = $ssh->getLog();

			print_r($log);
			//foreach ($log as $logitem)  {
			//echo $logitem.'<br>';
			//}  
		}
?>

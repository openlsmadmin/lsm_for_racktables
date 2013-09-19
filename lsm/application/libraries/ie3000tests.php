<?php
/*
This section of the code tests to see if we can connect using the open source phpseclib tool
*/

set_include_path(get_include_path() . PATH_SEPARATOR . '/var/www/phpseclib');
include('Net/SSH2.php');
define('NET_SSH2_LOGGING', NET_SSH2_LOG_COMPLEX); 

$hostname = 'CAN-A2-144-SW-3.can.jwm2.net';
$password = 'scotchis!Cheap';
$username = 'julee2';
$ssh = new Net_SSH2($hostname);


//this works====================================

if (!$ssh->login($username, $password)) { //if you can't log on...
         echo("Error: Authentication Failed for $this->_hostname");
         return false;      
}
else
{
	echo 'Connected<BR>';
	echo 'prompt is: '.$ssh->read('/([0-9A-Z\-])*(#)(\s*)/i', NET_SSH2_READ_REGEX).'<BR>';
}

echo "<BR>sending terminal length 0\n<BR>";
$ssh->write("terminal length 0\n");
$data = $ssh->read('/([0-9A-Z\-])*(#)(\s*)/i', NET_SSH2_READ_REGEX); 
echo "reading :".$data;

echo "<BR>sending show mac address-table\n<BR>";
If ($commandresults = $ssh->write("show mac address-table\n") )
{
	echo 'Ran command fine';
	$data = $ssh->read('/([0-9A-Z\-])*(#)(\s*)/i', NET_SSH2_READ_REGEX); 
	echo "<BR>The data returned : <pre>".$data."</pre>";
    $log = $ssh->getLog();
    echo "<pre>Starting Logs</pre>";
  	echo '<pre>'.$log.'</pre>';

} 
else {
    $log = $ssh->getLog();
    echo '<pre>'.$log.'</pre>';
} 
exit;

//this DOES NOT works=============================================

$ssh = new Net_SSH2($hostname);
if (!$ssh->login($username, $password)) { //if you can't log on...
			echo("Error: Authentication Failed for $this->_hostname");
			return false;		
}
else  {
	echo 'prompt is: '.$ssh->read('/([0-9A-Z\-])*(#)(\s*)/i', NET_SSH2_READ_REGEX);
	$commandresults = $ssh->write("show mac address-table\n");	
}
if ($commandresults) {	
 	$data = $ssh->read('/([0-9A-Z\-])*(#)(\s*)/i', NET_SSH2_READ_REGEX);   
 	print 'attempting to print data:';
 	print_r($data);  	     
}

else {
    $log = $ssh->getLog();
 	echo '<pre>'.$log.'</pre>';
}
exit;

//this works=============================================
$ssh = new Net_SSH2('10.14.3.60');

if (!$ssh->login('julee2', 'scotchis!Cheap')) {	
    echo 'Login Failed';
}
else
{
	echo "<BR>success";
	echo $ssh->exec('show mac address-table');
	$ssh->disconnect();
}
exit;

//=====================================================================
/*
This section of code checks which types of authentication is supported by the switch... helps us to know
which authencation function in php ssh to call.
*/

echo '<br>';
echo '<br>';
echo '===============================<br>';

$connection = ssh2_connect('10.14.3.60', 22);

$auth_methods = ssh2_auth_none($connection, 'user');

var_dump($auth_methods);
if (in_array('password', $auth_methods)) {
  echo "Server supports password based authentication\n";
}


//****************************************************************
//exmaple showing how to connect using the ssh2_auth_password method.
//this code was tested against the cisco ie 3000 and it works . 


$connection = ssh2_connect('10.14.3.60', 22);
if (ssh2_auth_password($connection, 'julee2', 'scotchis!Cheap')) {
  echo "Authentication Successful!\n";
} else {
  die('Authentication Failed...');
}
print_r($connection);

exit;
//*******************************************
//testing how to send commands to the cisco ie 3000 using php ssh2 library vs. phpseclib.

echo 'sending show bonjour command:<br>';

$stream = ssh2_exec($connection, 'show mac address-table');
var_dump($stream);
sleep(1);
echo "<br>Output: " . stream_get_contents($stream);  
echo '<br>';


if ( $con=ssh2_connect('10.14.3.60', 22) )
{
	If (  ssh2_auth_password($con, 'julee2', 'scotchis!Cheap')  )
	{
		print 'authenticated';
	}
	$stream = ssh2_exec($con, 'show log\n');

	echo "<br>Output: " . stream_get_contents($stream);  	
}
else
{
	print 'unable to connect';
}

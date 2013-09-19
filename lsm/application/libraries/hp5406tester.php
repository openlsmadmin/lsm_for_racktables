<?php
/*
This section of the code tests to see if we can connect using the open source phpseclib tool
*/
error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
print 'testing connection to can branch...<BR>';
set_include_path(get_include_path() . PATH_SEPARATOR . '/var/www/phpseclib');
include('Net/SSH2.php');
define('NET_SSH2_LOGGING', NET_SSH2_LOG_COMPLEX); 

//$hostname = 'B-25-6CRLAB-SW-2.usa.jwm2.net';
//$password = 'roguebwlch669968';
//$username="jbilyk";
//$hostname = 'can-a2-152-sw-1.can.jwm2.net';

$hostname = 'can-a2-165-sw-1.can.jwm2.net';
$password = 'fe8CC+ad4aA9';
$username="cannetworkinfra";

//------------------------------------ use ANSI Class - encoding in base64 for email purposes. ----------------------------------------------------
include('File/ANSI.php');

$ssh = new Net_SSH2($hostname);
if (!$ssh->login($username, $password)) { //if you can't log on...
         echo("Error: Authentication Failed for $this->_hostname");
         return false;      
}
else
{	
		$ansi = new File_ANSI();
		$ansi->appendString($ssh->read('/([0-9A-Z\-\=])*(#)(\s*)/i', NET_SSH2_READ_REGEX) );
		$ssh->write("\n");		
		$ansi->appendString($ssh->read('/([0-9A-Z\-\=])*(#)(\s*)/i', NET_SSH2_READ_REGEX) );
		echo '<BR>==========prompt is '. $prompt=htmlspecialchars_decode(strip_tags($ansi->getHistory())) .'<BR>===========<BR>';
		if (!$prompt){
			echo 'problems communcating with terminal';
			$ssh->disconnect();
		}
		else
		{
			$ssh->write("conf t\n");
			$ssh->setTimeout(5);
			$ansi->appendString($ssh->read('/([0-9A-Z\-\=])*(#)(\s*)/i', NET_SSH2_READ_REGEX) );
			$ssh->write("console local-terminal ansi\n");
			$ansi->appendString($ssh->read('/([0-9A-Z\-\=])*(#)(\s*)/i', NET_SSH2_READ_REGEX) );
			
			$ssh->write("exit\n");
			$ansi->appendString($ssh->read('/([0-9A-Z\-\=])*(#)(\s*)/i', NET_SSH2_READ_REGEX) );			
			echo '<BR>==========prompt is '.$prompt= htmlspecialchars_decode(strip_tags($ansi->getHistory())) .'<BR>===========<BR>';
			$ssh->write("no page\n");
			$ssh->setTimeout(5);
			$data = $ssh->read('/([0-9A-Z\-\=])*(#)(\s*)/i', NET_SSH2_READ_REGEX);		
			$ssh->write("show mac-address\n");
			$ssh->setTimeout(5);
			$data = $ssh->read('/([0-9A-Z\-\=])*(#)(\s*)/i', NET_SSH2_READ_REGEX);

			echo 'disconnecting....<BR>';
		   	$log = $ssh->getlog();
		    $ssh->disconnect();	

		    echo '<BR>=============== this is the data =================== <BR>';
		    echo "<pre>$data</pre>";
		    echo "<pre>".$log."</pre>";		

			echo 'goodbye...';	
		}

}
exit;


//-----------------------------------------works. Changing the terminal console type from Vt100 to ANSI

include('File/ANSI.php');

$ssh = new Net_SSH2($hostname);
if (!$ssh->login($username, $password)) { //if you can't log on...
         echo("Error: Authentication Failed for $this->_hostname");
         return false;      
}
else
{	
		$ansi = new File_ANSI();
		$ansi->appendString($ssh->read('/([0-9A-Z\-\=])*(#)(\s*)/i', NET_SSH2_READ_REGEX) );
		$ssh->write("\n");		
		$ansi->appendString($ssh->read('/([0-9A-Z\-\=])*(#)(\s*)/i', NET_SSH2_READ_REGEX) );
		echo '<BR>==========prompt is '. $prompt=htmlspecialchars_decode(strip_tags($ansi->getHistory())) .'<BR>===========<BR>';
		if (!$prompt){
			echo 'problems communcating with terminal';
			$ssh->disconnect();
		}
		else
		{
			$ssh->write("conf t\n");
			$ssh->setTimeout(5);
			$ansi->appendString($ssh->read('/([0-9A-Z\-\=])*(#)(\s*)/i', NET_SSH2_READ_REGEX) );
			$ssh->write("console local-terminal ansi\n");
			$ansi->appendString($ssh->read('/([0-9A-Z\-\=])*(#)(\s*)/i', NET_SSH2_READ_REGEX) );
			
			$ssh->write("exit\n");
			$ansi->appendString($ssh->read('/([0-9A-Z\-\=])*(#)(\s*)/i', NET_SSH2_READ_REGEX) );			
			echo '<BR>==========prompt is '.$prompt= htmlspecialchars_decode(strip_tags($ansi->getHistory())) .'<BR>===========<BR>';
			$ssh->write("no page\n");
			$ssh->setTimeout(5);
			$data = $ssh->read('/([0-9A-Z\-\=])*(#)(\s*)/i', NET_SSH2_READ_REGEX);		
			$ssh->write("show  logging -r\n");
			$ssh->setTimeout(5);
			$data = $ssh->read('/([0-9A-Z\-\=])*(#)(\s*)/i', NET_SSH2_READ_REGEX);

			echo 'disconnecting....<BR>';
		   	$log = $ssh->getlog();
		    $ssh->disconnect();	

		    echo '<BR>=============== this is the data =================== <BR>';
		    echo "<pre>$data</pre>";
		    echo "<pre>".$log."</pre>";		

			echo 'goodbye...';	
		}

}
exit;




//---------------------this works - keep banner, but change the encoding from vt100 to ansi for the current session. ----------------------------///
include('File/ANSI.php');

$ssh = new Net_SSH2($hostname);
if (!$ssh->login($username, $password)) { //if you can't log on...
         echo("Error: Authentication Failed for $this->_hostname");
         return false;      
}
else
{	
		$ansi = new File_ANSI();
		$ansi->appendString($ssh->read('/([0-9A-Z\-\=])*(#)(\s*)/i', NET_SSH2_READ_REGEX) );
		$ssh->write("\n");		
		$prompt = $ansi->appendString($ssh->read('/([0-9A-Z\-\=])*(#)(\s*)/i', NET_SSH2_READ_REGEX) );
		$ssh->write("conf t\n");
		$ssh->setTimeout(5);
		$ansi->appendString($ssh->read('/([0-9A-Z\-\=])*(#)(\s*)/i', NET_SSH2_READ_REGEX) );
		$ssh->write("console local-terminal ansi\n");
		$ansi->appendString($ssh->read('/([0-9A-Z\-\=])*(#)(\s*)/i', NET_SSH2_READ_REGEX) );
		$ssh->write("no page\n");
		$ssh->setTimeout(5);
		$data = $ssh->read('/([0-9A-Z\-\=])*(#)(\s*)/i', NET_SSH2_READ_REGEX);		
		$ssh->write("show  logging -r\n");
		$ssh->setTimeout(5);
		$data = $ssh->read('/([0-9A-Z\-\=])*(#)(\s*)/i', NET_SSH2_READ_REGEX);

		echo 'disconnecting....<BR>';
	   	$log = $ssh->getlog();
	    $ssh->disconnect();	

	    echo '<BR>=============== this is the data =================== <BR>';
	    echo "<pre>$data</pre>";
	    echo "<pre>".$log."</pre>";		

		echo 'goodbye...';
}
exit;

//---------------------works - disable banner ----------------------------///
include('File/ANSI.php');

$ssh = new Net_SSH2($hostname);
if (!$ssh->login($username, $password)) { //if you can't log on...
         echo("Error: Authentication Failed for $this->_hostname");
         return false;      
}
else
{	
		$ansi = new File_ANSI();
		$ansi->appendString($ssh->read('/([0-9A-Z\-])*(#)(\s*)/i', NET_SSH2_READ_REGEX) );
		$ssh->write("\n");		
		$ansi->appendString($ssh->read('/([0-9A-Z\-])*(#)(\s*)/i', NET_SSH2_READ_REGEX) );
		$ssh->write("conf t\n");
		$ssh->setTimeout(5);
		$ansi->appendString($ssh->read('/([0-9A-Z\-])*(#)(\s*)/i', NET_SSH2_READ_REGEX) );
		$ssh->write("no page\n");
		$ansi->appendString($ssh->read('/([0-9A-Z\-])*(#)(\s*)/i', NET_SSH2_READ_REGEX) );
		$ssh->write("no banner motd\n");
		$ssh->setTimeout(5);
		$ssh->read('/([0-9A-Z\-])*(#)(\s*)/i', NET_SSH2_READ_REGEX);
		$ssh->write("show  logging -r\n");
		$ssh->setTimeout(5);
		
		$data = $ssh->read('/([0-9A-Z\-])*(#)(\s*)/i', NET_SSH2_READ_REGEX);

		echo 'disconnecting....<BR>';
	   	$log = $ssh->getlog();
	    $ssh->disconnect();	

	    echo '<BR>=============== this is the data =================== <BR>';
	    echo "<pre>$data</pre>";
	    echo "<pre>".$log."</pre>";		

		echo 'goodbye...';
}
exit;
//----------ANSI TESTS  THIS WORKS.  BUt all formatted differently... so hp class would need to completely change.AND only get 200 lines of history or current screen.--------------
include('File/ANSI.php');

$ssh = new Net_SSH2($hostname);
if (!$ssh->login($username, $password)) { //if you can't log on...
         echo("Error: Authentication Failed for $this->_hostname");
         return false;      
}
else
{	
		$ansi = new File_ANSI();
		$ansi->appendString($ssh->read('/([0-9A-Z\-])*(#)(\s*)/i', NET_SSH2_READ_REGEX) );
		$ssh->write("\n");
		$ssh->setTimeout(5);
		$ansi->appendString($ssh->read('/([0-9A-Z\-])*(#)(\s*)/i', NET_SSH2_READ_REGEX) );
		$ssh->write("no page\n");
		$ssh->setTimeout(5);
		$ansi->appendString($ssh->read('/([0-9A-Z\-])*(#)(\s*)/i', NET_SSH2_READ_REGEX) );
		$ssh->write("show  logging\n");
		$ssh->setTimeout(5);
		
		$ansi->appendString($ssh->read('/([0-9A-Z\-])*(#)(\s*)/i', NET_SSH2_READ_REGEX) );
		//echo "base 64 encoded ===============================================<BR>";
		//echo $ansi->getHistory(); // or $ansi->getHistory()
 		//echo base64_encode(htmlspecialchars_decode(strip_tags($ansi->getScreen())) ) ;

 		echo htmlspecialchars_decode(strip_tags($ansi->getHistory()));

		echo 'disconnecting....<BR>';

	   	$log = $ssh->getlog();
	    $ssh->disconnect();	
	    echo "<pre>".$log."</pre>";		
		$ssh->disconnect();
		echo 'goodbye...';
}

exit;


//----------this works.
$ssh = new Net_SSH2($hostname);
if (!$ssh->login($username, $password)) { //if you can't log on...
         echo("Error: Authentication Failed for $this->_hostname");
         return false;      
}
else
{	
		$ssh->write("\n");	 //press any key to continue prompt.
		$output = $ssh->read('/([0-9A-Z\-])*(#)(\s*)/i', NET_SSH2_READ_REGEX); 
	   	$ssh->write("no page\n");	
	   	$output = $ssh->read('/([0-9A-Z\-])*(#)(\s*)/i', NET_SSH2_READ_REGEX); 
	   	$ssh->write("show logging -r\n");	
		$output = $ssh->read('/([0-9A-Z\-])*(#)(\s*)/i', NET_SSH2_READ_REGEX); 
	    if ($output !== false) 
	    {        
	    	var_dump($output);
	    }
	   	$log = $ssh->getlog();
	    $ssh->disconnect();	
	    echo "<pre>".$log."</pre>";
}

exit;


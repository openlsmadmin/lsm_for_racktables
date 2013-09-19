<?php
$dnsName='CAN-A2-144-SW-3.can.jwm2.net';
$password = 'scotchis!Cheap';
$userId = 'julee2@bethel.jw.org';

try{	
		include_once('ciscoIE3000_ssh2.php');		
		$switch_obj = new ciscoIE3000_ssh2($dnsName, $password, $userId);
		if ($switch_obj->connect() )  {
		    if ( $data = $switch_obj->showknownvlans('Gi1/1') ) {		
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
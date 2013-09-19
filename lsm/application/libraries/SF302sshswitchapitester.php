f<?php
      // sample code to demo how to use the switch API
      require_once 'ciscoIE3000_ssh.php';
      $cisco = new ciscoIE3000_ssh('10.14.3.60', 'scotchis!Cheap', 'julee2');
      try {
	      	$cisco->connect();		 
    	  	if ($cisco->_data = true){
    	  	
		  $cisco->disconnect();
		  print_r($data);
		  }
      }

      catch (Exception $e)
      {
      	echo 'failed on connect with : '. $e;
      }
       		   
      //
      
   
      //return $data;	
?>

// <?php
//       // sample code to demo how to use the switch API
//        require_once 'ciscoSF302-08P_ssh.php';
//       $cisco = new ciscoSF30208P_ssh('10.14.3.44', 'fe8CC+ad4aA9', 'CANNetworkInfra');
//       try {
// 	      	$cisco->connect();	
//     	  	echo 'connected';	 
//     	  	$data = $cisco->showknownvlans();
//       		$cisco->disconnect();
//       		print_r($data);
//       }
// 
//       catch (Exception $e)
//       {
// //       	echo 'failed on connect with : '. $e;
//       }
//        		   
//       //
//       
//    
//       //return $data;	
// ?>
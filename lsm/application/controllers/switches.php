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
class switches extends CI_Controller {
	
	public function __construct()
	{
		parent::__construct();
		$this->load->model('switches_model');
		$this->load->model('racktables_model');
		$this->load->helper('url');
		$this->load->helper('form');
		$_locations;
	}
	
	
	//TODO:  jlee - move this curl routine into a new CI helper 
	public function curl($url){
	  //echo 'in the routine';
	    $ch=curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    $data=curl_exec($ch);
	    curl_close($ch);
	    return $data;
	}	

	public function saveconfiguration()
	{
		//grab URI data
		$seg4 = $this->uri->segment(4); //fqdn
		$seg3 = $this->uri->segment(3); //hardwaremodel

	      try{

		      $result = $this->switches_model->save_config($seg4, $seg3);	
		      if ($result !== false)  {
			    	$resultdata = array(
			      	'status' => true,
			      	'error_msg' =>'',
			      	'data' => true
		            );
	     	  }
	      }
	      catch (Exception $e) {
	      	//ajax method ... so don't just do a show_error. 
		    	$resultdata = array(
		      	'status' => false,
		      	'error_msg' =>'Unable to save configuration. Contact Administrator',
		      	'data' => false
		      	);		      	

	      }
 
	      header ('Content-Type: application/json; charset=UTF-8');
	      echo json_encode($resultdata); 	    	
	}

	public function details()
	{	
		$seg3=$this->uri->segment(3);		
		$data['switchDetails']=$this->racktables_model->get_switch_details($seg3);		
		$data['dnsName'] = $data['switchDetails']['main'][0]['name'];  //extract DNS name
		//extract FQDN Name		
		if ( isset( $data['switchDetails']['FQDN'][0]['string_value'] ) ) {
		    $data['FQDN'] = $data['switchDetails']['FQDN'][0]['string_value'];
		}				
        //do we have hardware model
		if (isset($data['switchDetails']['hardwaredetails'][0]['hardwaremodel'])) {
		    $hardwaremodel = $data['switchDetails']['hardwaredetails'][0]['hardwaremodel'];
		} 
		else {
		    $hardwaremodel = NULL;		    
		}
		//do we have all data we need to connect to this switch?
		$data['connectable']=false;	//assume we don't

		if ( isset($hardwaremodel) and $this->switches_model->isthissupported($hardwaremodel) and isset($data['FQDN']) ) {		
		  $data['connectable']=true; 

		}
				
		$portsToLock = array();	
		if (count($data['switchDetails']['ports'])>0 ){	
			foreach ($data['switchDetails']['ports'] as $port)  {
			      if (  (isset($port['label'])) and ((strlen($port['porta']) > 0) || (strlen($port['portb']) > 0))  )  {
				      //build array
				      array_push($portsToLock, $port['name']);
			      }//end if
		    } //end foreach
		}
	    $data['objectid'] = $seg3;
		$data['portsToLock'] = $portsToLock;
	    $data['hardwaremodel'] = $hardwaremodel;
 		$viewnamePrefix = $this->checkhardwarename($data['hardwaremodel']);
		$data['main_content'] = $viewnamePrefix.'objectdetails';
		$this->load->view('includes/template', $data);		
	    
	}//end details
	      	
	public function macaddresses()
	{
		//grab URI data
		$seg6 = $this->uri->segment(6);
		$seg3 = $this->uri->segment(3);
		try {				
			  $data['macaddresses'] = $this->switches_model->get_macaddresses( $seg6,$seg3 );
		      $data['objectid'] = $this->uri->segment(4);  
		      $data['dnsName'] = $this->uri->segment(5); 
		      $data['hardwaremodel'] = $seg3;
		      $data['FQDN'] = $seg6; 
		      
		      //which view do we load?
		      $viewnamePrefix = $this->checkhardwarename($data['hardwaremodel']);
			  $data['main_content']=$viewnamePrefix.'macaddresses';
		      $this->load->view('includes/template', $data);			  
		}
		catch (Exception $e) {
		    show_error($e->getMessage());
		}
	
	}//end macaddresses
	

	//return value from this function will be used as a prefix for the view name.
	private function checkhardwarename($hardwarename)
	{ 		 
			$hardwarename = str_replace (' ', '-', $hardwarename);
			$hardwarename = str_replace ('%20', '-', $hardwarename);
		      if (strpos(strtoupper($hardwarename),"PROCURVE") !== false)  {
			  	  return 'hp';
		      }
		      elseif (strpos(strtoupper($hardwarename),"CISCO-IE-3000") !== false)  {			 
				  return 'cisco_ie3000';
		      }
		      elseif (strpos(strtoupper($hardwarename),"CISCO-SF") !== false)  {		      	
		      	  return 'cisco_sf';
		      }//end if
	}// end checkhardwarename

	public function logs()
	{
	    try {
	    	$data['logcontents'] = $this->switches_model->get_logs($this->uri->segment(6), $this->uri->segment(3));
			$data['dnsName'] = $this->uri->segment(5);
			$data['FQDN'] = $this->uri->segment(6); 
			$data['objectid'] = $this->uri->segment(4);  
			$data['main_content']='logs';
			$this->load->view('includes/template', $data);
	    }
		catch (Exception $e) {
		    show_error($e->getMessage());
		}	
	}// end logs
	
	public function allportsstatus()
	{
		try  {		

			  $seg3 = $this->uri->segment(3); //hardware model
			  $seg4 = $this->uri->segment(4); //object id
			  $seg6 = $this->uri->segment(6); //fqdn

		      $data['listofports'] = $this->switches_model->get_portstatusall($seg6,$seg3 );	     
		      $data['portsToLock'] = $this->racktables_model->get_locked_ports($seg4); ///get locked ports using object ID
		      $data['hardwaremodel'] = $seg3;		     
		      $data['objectid'] = $seg4; 
		      $data['dnsName'] = $this->uri->segment(5); 
		      $data['FQDN'] = $seg6;  
		      // save objectid for hyperlink back to object details page.

			  $viewnamePrefix = $this->checkhardwarename($data['hardwaremodel']);
			  $data['main_content'] = $viewnamePrefix.'allportsstatus';
		      $this->load->view('includes/template', $data);	
	      }
		catch (Exception $e) {
		    show_error($e->getMessage());
		}		      
	}// end allportsstatus

	
	public function portstatus()
	{
		try  {
		$seg7 = $this->uri->segment(7);
		$seg3 = $this->uri->segment(3); 
		$seg4 = $this->uri->segment(4);
		$seg4 = str_replace('~','/',$seg4);
			$data['portstatus']= $this->switches_model->get_portstatus($seg7,$seg3,$seg4);
			    $data['hardwaremodel'] = $seg3; 
			    $data['portnumber'] = $seg4;
			    $data['objectid'] = $this->uri->segment(5);
			    $data['dnsName'] = $this->uri->segment(6);
			    $data['FQDN'] = $seg7;
			    
			    //which view do we load?
		  		$viewnamePrefix = $this->checkhardwarename($data['hardwaremodel']);
		  		$data['main_content'] = $viewnamePrefix.'portstatus';
			    $this->load->view('includes/template', $data);	
		}
		catch (Exception $e) {
		    show_error($e->getMessage());
		}			
		
	}//end portstatus
	
	
	/* toggles the port from enabled to disabled and visa versa.  Return the port interface details 
	    as a result.
	*/
	public function changeHPportstatus()
	{
		$seg3 = $this->uri->segment(3);
		$seg4 = $this->uri->segment(4);
		$seg5 = $this->uri->segment(5);
		$seg6 = $this->uri->segment(6);
		$seg8 = $this->uri->segment(8);
		$data['hardwaremodel'] = $seg3; 
		$data['portnumber'] = $seg4;
		$data['objectid'] = $seg6;
		$data['dnsName'] = $this->uri->segment(7);
	    $data['FQDN'] = $seg8; 

		if (strtoupper($seg5) === 'YES') {
		    //disable the port
		    $data['portstatus']=$this->switches_model->disable_port($seg8,$seg3,$seg4);	
		}
		elseif (strtoupper($seg5) === 'NO') {
		    //enable the port
		    $data['portstatus']=$this->switches_model->enable_port($seg8,$seg3,$seg4);	
		}
		//requery and get fresh list of all port status. Cannot reuse existing allportsstatus method becuase the URL looks different at this point.
		$data['listofports'] = $this->switches_model->get_portstatusall($seg8,$seg3);	     
	    $data['portsToLock'] = $this->racktables_model->get_locked_ports($seg6); //get locked ports using object ID
		$viewnamePrefix = $this->checkhardwarename($data['hardwaremodel']);
		$data['main_content'] = $viewnamePrefix.'allportsstatus';
	    $this->load->view('includes/template', $data);	
	    
	}// end changeportstatus
		
	public function changeCiscoPortStatus()
	{
		$seg3 = $this->uri->segment(3);
		$seg4 = $this->uri->segment(4);
		$seg5 = $this->uri->segment(5);
		$seg6 = $this->uri->segment(6);
		$seg8 = $this->uri->segment(8);
		//replace ~ in port name with '/'
		$seg4 = str_replace('~','/',$seg4);

		$data['hardwaremodel'] = $seg3; 
		$data['portnumber'] = $seg4;
		$data['objectid'] = $seg6;
		$data['dnsName'] = $this->uri->segment(7);
	    $data['FQDN'] = $seg8; 


		if (strtoupper($seg5) === 'NOTCONNECT') {
		    //disable the port
		    $data['portstatus']=$this->switches_model->disable_port($seg8,$seg3,$seg4);	
		}
		elseif (strtoupper($seg5) === 'DISABLED') {
		    //enable the port
		    $data['portstatus']=$this->switches_model->enable_port($seg8,$seg3,$seg4);	
		}
		//requery and get fresh list of all port status. Cannot reuse existing allportsstatus method becuase the URL looks different at this point.
		$data['listofports'] = $this->switches_model->get_portstatusall($seg8,$seg3);	     
	    $data['portsToLock'] = $this->racktables_model->get_locked_ports($seg6); //get locked ports using object ID
		$viewnamePrefix = $this->checkhardwarename($data['hardwaremodel']);
		$data['main_content'] = $viewnamePrefix.'allportsstatus';
	    $this->load->view('includes/template', $data);	
	}
	/*
	*used by cisco sf300 only - same as enabling / disabling port on HP switch
	*/
	public function changeportadminstate()
	{
	    $seg3 = $this->uri->segment(3);
	    $seg4 = $this->uri->segment(4);
		$seg5 = $this->uri->segment(5);
		$seg6 = $this->uri->segment(6);
		$seg8 = $this->uri->segment(8);
				
		$data['hardwaremodel'] = $seg3; 
		$data['portnumber'] = $seg4;
		$data['objectid'] = $seg6;
		$data['dnsName'] = $this->uri->segment(7);
	    $data['FQDN'] = $seg8; 

		if (strtoupper($seg5) === 'UP') {
		    //disable the port
		    $data['portstatus']=$this->switches_model->disable_port($seg8,$seg3,$seg4);	
		}
		elseif (strtoupper($seg5) === 'DOWN') {
		    //enable the port
		    $data['portstatus']=$this->switches_model->enable_port($seg8,$seg3,$seg4);	
		}
		//requery and get fresh list of all port status. Cannot reuse existing allportsstatus method becuase the URL looks different at this point.
		$data['listofports'] = $this->switches_model->get_portstatusall($seg8,$seg3 );	     
		$data['portsToLock'] = $this->racktables_model->get_locked_ports($seg6); //get locked ports using object ID
		$data['main_content']='cisco_sfallportsstatus';
		$this->load->view('includes/template', $data);	    
	}//end changeportadminstate	
	
	
	/* change vlan 	    
	      query swtich for list of vlans it currently knows about. 
	      display on screen for selection. 	    
	*/
	public function changeportvlan()
	{  
		$seg4 = $this->uri->segment(4); 
		$seg5 = $this->uri->segment(5);
		$seg8 = $this->uri->segment(8);
		
		$data['dnsname'] = $this->uri->segment(3); 
		$newVlan = $this->uri->segment(6);
		$mode = $this->uri->segment(7); 
		$data['hardwaremodel'] = $seg4; 
		$data['portname'] = $seg5; 
		$data['FQDN'] = $seg8; 	
	    	
	    $seg5 = str_replace('~','/',$seg5);
	    	
	    $result = $this->switches_model->change_port_vlan($seg8, $seg4, $seg5, $newVlan, $mode);
	    	
		if ($result !== false ){
			$resultdata = array(
			'status' => true,
			'error_msg'=>'',
			'data' => $result
			);
		}		
		header ('Content-Type: application/json; charset=UTF-8');		
		echo json_encode($resultdata);			
	}// end changeportvlan

	public function deleteportvlan()
	{
	      $seg4 = $this->uri->segment(4);
	      $seg5 = $this->uri->segment(5);
	      $seg6 = $this->uri->segment(6);  
	      $seg7 = $this->uri->segment(7);
	      $seg8 = $this->uri->segment(8);
	      
	      $seg5 = str_replace('~','/',$seg5);

	      $data['hardwaremodel'] = $seg4;
	      $data['portname'] = $seg5; 
	      $data['vlanid'] = $seg6; 
	      $data['mode'] = $seg7;
	      $data['FQDN'] = $seg8;

	      try{

		      $result = $this->switches_model->delete_port_vlan($seg8, $seg4, $seg5,  $seg6,  $seg7);	
		      if ($result !== false)  {
			    	$resultdata = array(
			      	'status' => true,
			      	'error_msg' =>'',
			      	'data' => $result
		            );
	     	  }
	      }
	      catch (Exception $e) {
	      	//ajax method ... so don't just do a show_error. 
		    	$resultdata = array(
		      	'status' => false,
		      	'error_msg' =>'Unable to delete port vlan. Contact Administrator.',
		      	'data' => false
		      	);		      	

	      }
 
	      header ('Content-Type: application/json; charset=UTF-8');
	      echo json_encode($resultdata); 	      
	}  //end deleteportvlan
	
	public function showportvlan()
	{
	      $seg3 = $this->uri->segment(3);
	      $seg4 = $this->uri->segment(4);
	      $seg7 = $this->uri->segment(7);	
		  $seg4 = str_replace('~','/',$seg4);
	    
	      $data['objectid'] = $this->uri->segment(5);
	      $data['dnsName'] = $this->uri->segment(6); 
	      $data['hardwaremodel'] = $seg3; 
	      $data['portname'] = $seg4; 
	      $data['FQDN'] = $seg7; 
	      try {
	      		 $data['portvlan'] = $this->switches_model->show_port_vlan($seg7,$seg3,$seg4);	 	      	      
				  //which view do we load?
		  		$viewnamePrefix = $this->checkhardwarename($data['hardwaremodel']);
		  		$data['main_content'] = $viewnamePrefix.'changeportvlan';
				$this->load->view('includes/template', $data);
	      }		
		  catch (Exception $e) {
		    show_error($e->getMessage());
		  }
	} //end showportvlan
	
	public function showknownvlans()
	{
	      $vlans=$this->switches_model->show_known_vlans($this->uri->segment(5), $this->uri->segment(4));
	      header ('Content-Type: application/json; charset=UTF-8');
	      echo json_encode($vlans);  
	   
	} // end showknownvlans	
	
	
	public function changeduplexsettings()
	{	
	      $data['hardwaremodel'] = $this->uri->segment(3); 
	      $data['portname'] = $this->uri->segment(4); 

	      $data['portname'] = str_replace('~', '/', $data['portname']);
	      $data['objectid'] = $this->uri->segment(5); 
	      $data['dnsName'] = $this->uri->segment(6);
	      $data['FQDN'] = $this->uri->segment(7);

	      //which view do we load?
		  $viewnamePrefix = $this->checkhardwarename($data['hardwaremodel']);
		  $data['main_content'] = $viewnamePrefix.'newduplexsetting';	 
	      $this->load->view('includes/template', $data);	      
	} //end changeduplexsettings
	
	
	public function assignduplex()
	{

		//echo form_open('switches/assignduplex/'.$hardwaremodel.'/'.$portname.'/'.$dnsName.'/'.$FQDN.'/'.$objectid, $attributes);
	    $seg3 = $this->uri->segment(3); 
	    $seg4 = $this->uri->segment(4);
	    $seg6 = $this->uri->segment(6);
	    $seg7 = $this->uri->segment(7);
	    
	    $data['dnsName'] = $this->uri->segment(5);   
	    $data['hardwaremodel'] = $seg3; 
	    $data['portname'] = $seg4; 
	    $data['FQDN'] = $seg6;
	    $data['objectid'] = $seg7; 

	    $data['duplexresults'] = $this->switches_model->changeduplex($seg6, $seg3, $seg4, $this->input->post('duplex'));		
	    $data['listofports'] = $this->switches_model->get_portstatusall($seg6,$seg3);	     

	    $data['portsToLock'] = $this->racktables_model->get_locked_ports($seg7); 

	      //which view do we load?
		  $viewnamePrefix = $this->checkhardwarename($data['hardwaremodel']);
		  $data['main_content'] = $viewnamePrefix.'allportsstatus';	 
	      $this->load->view('includes/template', $data);	 	
	    	
	}  //end assignduplex

	public function indicatorlight()
	{	     
	      $data['hardwaremodel'] = $this->uri->segment(4); 
	      
	      $functionName = 'chassisLight';
	      switch(trim($this->uri->segment(4)))  {
				  case 1: $functionName .= 'On'; break;
				  case 0: $functionName .= 'Off'; break;
				  case 2: $functionName .= 'Blink'; break;
	      }
	      try  {
	      		$data['light'] = $this->switches_model->$functionName($this->uri->segment(7), $this->uri->segment(3));
		  		redirect(base_url().'index.php/switches/details/'.$this->uri->segment(5));
			}
			catch (Exception $e) {
			    show_error($e->getMessage());
			}
	} //end indicatorlight
	
	public function poeOn()
	{
		$seg4 = $this->uri->segment(4); 
		$seg5 = $this->uri->segment(5);
		$seg6 = $this->uri->segment(6);
		
		$data['dnsName'] = $this->uri->segment(3); 
		$data['hardwaremodel'] = $seg4; 
		$data['portname'] = $seg5; 
		$data['FQDN'] = $seg6; 
		try  {

			$poeStatus = $this->switches_model->poeOn($seg6,$seg4, $seg5);
		    echo $poeStatus;
		}
		catch (Exception $e) {
		    show_error($e->getMessage());
		}         
	}//poeOn
	
	public function poeOff()
	{
		$seg4 = $this->uri->segment(4); 
		$seg5 = $this->uri->segment(5);
		$seg6 = $this->uri->segment(6);
		
		$data['dnsName'] = $this->uri->segment(3); 
		$data['hardwaremodel'] = $seg4; 
		$data['portname'] = $seg5; 
		$data['FQDN'] = $seg6; 
		try  {
			$poeStatus = $this->switches_model->poeOff($seg6,$seg4, $seg5);
		    echo $poeStatus;
		}		
		catch (Exception $e) {
		    show_error($e->getMessage());
		}   				
	} // end poeOff
	
	public function showPoeStatus()
	{
		$seg3 = $this->uri->segment(3); 
		$seg4 = $this->uri->segment(4);
		$seg7 = $this->uri->segment(7);	

		$data['objectid'] = $this->uri->segment(5); 
		$data['dnsName'] = $this->uri->segment(6); 
		
		$data['hardwaremodel'] = $seg3; 
		$data['portname'] = $seg4; 		
		$data['FQDN'] = $seg7; 
		try  {
			$data['poeStatus'] = $this->switches_model->showPoeStatus($seg7,$seg3, $seg4);	   
		    $data['main_content']='poestatus';
		    $this->load->view('includes/template', $data);	      	
		}
		catch (Exception $e) {
		    show_error($e->getMessage());
		}   
	} //end showPoeStatus


	public function macauthOn()
	{
		$seg4 = $this->uri->segment(4); 
		$seg5 = $this->uri->segment(5);
		$seg6 = $this->uri->segment(6);
		
		$data['dnsName'] = $this->uri->segment(3); 
		$data['hardwaremodel'] = $seg4; 
		$data['portname'] = $seg5; 
		$data['FQDN'] = $seg6; 
		try  {

			$macStatus = $this->switches_model->macauthOn($seg6,$seg4, $seg5);
		    echo $macStatus;
		}
		catch (Exception $e) {
		    show_error($e->getMessage());
		}         
	}//macauthOn
	
	public function macauthOff()
	{
		$seg4 = $this->uri->segment(4); 
		$seg5 = $this->uri->segment(5);
		$seg6 = $this->uri->segment(6);
		
		$data['dnsName'] = $this->uri->segment(3); 
		$data['hardwaremodel'] = $seg4; 
		$data['portname'] = $seg5; 
		$data['FQDN'] = $seg6; 
		try  {
			$macStatus = $this->switches_model->macauthOff($seg6,$seg4, $seg5);
		    echo $macStatus;
		}		
		catch (Exception $e) {
		    show_error($e->getMessage());
		}   				
	} // end macauthOff

	public function showMacAuthStatus()
	{
		$seg3 = $this->uri->segment(3); 
		$seg4 = $this->uri->segment(4);
		$seg7 = $this->uri->segment(7);	

		$data['objectid'] = $this->uri->segment(5); 
		$data['dnsName'] = $this->uri->segment(6); 
		
		$data['hardwaremodel'] = $seg3; 
		$data['portname'] = $seg4; 		
		$data['FQDN'] = $seg7; 

		try  {
			$data['macAuthStatus'] = $this->switches_model->showMacAuthStatus($seg7,$seg3, $seg4);	   
		    $data['main_content']='macauthstatus';
		    $this->load->view('includes/template', $data);	      	
		}
		catch (Exception $e) {
			print("exception handler");
		    show_error($e->getMessage());
		}   
	} //end showMacAuthStatus

	public function getbranches()
	{
		//this method will return all locations; branches, buildings and rooms.  Just parse out
		//the branches.
		$branches=array();
		$_locations = $this->racktables_model->get_locations();	
		foreach ($_locations as $location)  {
			if ((isset($location['L1ID'])) && (!array_key_exists($location['L1ID'],$branches))) {
			
				$branches[$location['L1ID']] = $location['L1Location'];			
			}
		}//end foreach
		$data['branches'] = $branches;
		$data['title'] = "Show Location:";
		$data['main_content']='switchesbylocation';
		$this->load->view('includes/template', $data);
	}  //end getbranches

	public function getbuildings()
	{	
		$buildings = array();
		$branchID = $this->uri->segment(3);
		$buildingforbranch = array();
		$_locations = $this->racktables_model->get_locations();	

		foreach ($_locations as $location)  {
			if ((isset($location['L2FullID'])) && (!array_key_exists($location['L2FullID'],$buildings))) {
				$buildings[$location['L2FullID']] = $location['L2Location'];			
			}
		}  //end foreach

		foreach ($buildings as $key => $value)  {			
			$pattern = "/^(".$branchID."\.\d)/i";			
			if (preg_match($pattern,$key))  {
				$buildingforbranch[(string)$key] = $value;
			}
		} //end foreach	

		header ('Content-Type: application/json; charset=UTF-8');
      	echo json_encode($buildingforbranch);  
	}

	public function getbuildings_htmlout()
	{	
		$buildings = array();
		$branchID = $this->uri->segment(3);
		$buildingforbranch = array();
		$_locations = $this->racktables_model->get_locations();	
		print "<pre>";
		var_dump($_locations);
		print "</pre>";
		//all buildings
		foreach ($_locations as $location)  {
			if ((isset($location['L2FullID'])) && (!array_key_exists($location['L2FullID'],$buildings))) {
				$buildings[$location['L2FullID']] = $location['L2Location'];		
			}
		}  //end foreach
		print "<BR>================Building for Branch looks like ===================<BR>";
		print "<pre>";
		var_dump($buildingforbranch);
		print "</pre>";		

		foreach ($buildings as $key => $value)  {			
			$pattern = "/^(".$branchID."\.\d)/i";			
			if (preg_match($pattern,$key))  {
				$buildingforbranch[(string)$key] = $value;
			}
		} //end foreach	
		return $buildingforbranch;
	}


	public function getrooms()
	{ 
		
		$rooms = array();
		$roomsinbuilding=array();

		$locationDetails = $this->uri->segment(3); //has both branch and buildilng id built in.  Eg) 2.5
		$locationDetails = explode('.',(string)$locationDetails);
		$_locations = $this->racktables_model->get_locations();	

		foreach ($_locations as $location)  {
			if ((isset($location['L3FullID'])) && (!array_key_exists($location['L3FullID'],$rooms))) {
				$rooms[$location['L3FullID']] = $location['L3Location'];			
			}
		}	

		foreach ($rooms as $key => $value)   {
			$pattern = "/(".$locationDetails[0].".".$locationDetails[1]."."."\d)/i";
			if (preg_match($pattern,$key))   {
				$roomsinbuilding[(string)$key] = $value;
			}

		}	
		header ('Content-Type: application/json; charset=UTF-8');	
  		echo json_encode($roomsinbuilding);  


	}//end getrooms.

    public function getallswitches()
	{
		$switches = $this->racktables_model->get_all_switches();	
		header ('Content-Type: application/json; charset=UTF-8');	
  		echo($switches);  

	}		

	public function getswitchesbylocation()
	{
		
	    $branch_id = $this->uri->segment(3); // returns false if not set.
	    if(!$branch_id){
        	redirect('error_page');//$this->load->helper('url') must be loaded in order for redirect to work. 
	    }

	    //TODO:  Check if we want to even allow this feature to retrieve all. Or force to always have a branch.
	    if ($branch_id == 9999)
	    {
		    $data = $this->getallswitches();
	    }
	    else
	    {
			$building_id = $this->uri->segment(4); //Ok if false. 
		    $room_id = $this->uri->segment(5);
			//call get_switches by location
			$data=$this->racktables_model->get_switches_by_location($branch_id, $building_id, $room_id);	
	    }	    
	    header ('Content-Type: application/json; charset=UTF-8');
	    echo ($data);  //data will already be encoded as json.  just echo it!
	}

	/*how to call this method: http://jllinuxdev/lsm/index.php/switches/test_showknownvlans/CAN-A2-152-SW-1/HP%20ProCurve%205406zl-48G%20J8699A/CAN-A2-152-SW-1.can.jwm2.net
	*/
	public function test_showknownvlans()
	{		
		  print '<B><U>Purpose of Test:</U></B><p>';
		  print 'After querying a switch for a list of known vlans, system should remove VLAN 1 as an option from the array<BR>';
		  print '<B><U>How to Use This Routine: </U></B><p>';
		  print 'You must pass the following parameters: <BR>';
		  print 'DNS Name of switch<BR>';
		  print 'Hardware Model of switch<BR>';
		  print 'Fully Qualified Domain Name for switch<BR>';
		  print 'Sample URL:<BR> ';
		  print 'https://http://localhost/lsm/index.php/switches/test_showknownvlans/switch_name/Cisco%20IE-3000-8TC/fully_qualified_switch_name<BR>';
	      $vlans=$this->switches_model->show_known_vlans($this->uri->segment(5), $this->uri->segment(4));
	      print '<pre>';
	      var_dump ($vlans);
 		  print '</pre>';
	}

	//http://jllinuxdev/lsm/index.php/switches/test_changeportvlan/CAN-A2-152-SW-3/Cisco SF 300-48/fa1/80/tagged/can-a2-152-sw-3.can.jwm2.net
	public function test_changeportvlan()
	{
		print '<B><U>Purpose of Test:</U></B><p>';
		print 'Add vlans to a port<p>';
		print 'If adding a TAGGED vlan to an <i> ACCESS </i>port - This routine will convert the port from Access mode to Trunk, preserve the existing Untagged vlan, and add the new vlan as taggged. <BR>';
		print '<B><U>How to Use This Routine: </U></B><p>';
		print 'You must pass the following parameters: <BR>';
		print 'DNS Name of switch<BR>';
		print 'Hardware Model of switch<BR>';
		print 'Port you want to add vlan to<BR>';
		print 'New Vlan Number<BR>';
		print 'Vlan mode - Tagged / Untagged<BR>';
		print 'Fully Qualified Domain Name for switch<BR>';
	  	print 'Sample URL:<BR> ';
	  	print 'https://localhost/lsm/index.php/switches/test_changeportvlan/switch_name/Cisco SF 300-48/fa1/80/tagged/fully_qualified_switch_name<BR>';
	  	print 'or<BR>';
	  	print 'https://localhost/lsm/index.php/switches/test_changeportvlan/switch_name/Cisco%20IE-3000-8TC/fa1~1/59/tagged/fully_qualified_switch_name<BR>';
		$seg4 = $this->uri->segment(4); 
		$seg5 = $this->uri->segment(5);
		$seg8 = $this->uri->segment(8);
		
		$seg5 = str_replace('~', '/', $seg5);
		$data['dnsname'] = $this->uri->segment(3); 
		$newVlan = $this->uri->segment(6);
		$mode = $this->uri->segment(7); 
		$data['hardwaremodel'] = $seg4; 
		$data['portname'] = $seg5; 
		$data['FQDN'] = $seg8; 	

	    print 'received following data: '. $seg8.' '.$seg4.' '.$seg5.' '.$newVlan.' '.$mode;
	    $result = $this->switches_model->change_port_vlan($seg8, $seg4, $seg5, $newVlan, $mode);
		print '<pre>';
		var_dump($result);
		print '</pre>';
		// +  $('#dnsName').val() + '/' + $('#hardwaremodel').val() + '/' + $('#port').val() + '/' + IdforVlanToAdd + '/' + modeForVlanToAdd + '/' + $('#FQDN').val();
		
	}
	//http://jllinuxdev/lsm/index.php/switches/test_changeportvlan/CAN-A2-152-SW-3/Cisco SF 300-48/fa1/80/tagged/can-a2-152-sw-3.can.jwm2.net
	public function test_deleteportvlan()
	{
		print '<B><U>Purpose of Test:</U></B><p>';
		print 'Delete port vlans<p>';
		print '<B><U>How to Use This Routine: </U></B><p>';
		print 'You must pass the following parameters: <BR>';
		print 'DNS Name of switch<BR>';
		print 'Hardware Model of switch<BR>';
		print 'Port you want to delete vlan from<BR>';
		print 'Vlan Number<BR>';
		print 'Vlan mode - Tagged / Untagged<BR>';
		print 'Fully Qualified Domain Name for switch<BR>';
	  	print 'Sample URL:<BR> ';
	  	print 'https://localhost/lsm/index.php/switches/test_deleteportvlan/switch_name/Cisco SF 300-48/fa5/70/tagged/fully_qualified_switch_name<BR>';
		
	    $seg4 = $this->uri->segment(4);
	    $seg5 = $this->uri->segment(5);
	    $seg6 = $this->uri->segment(6);  
	    $seg7 = $this->uri->segment(7);
	    $seg8 = $this->uri->segment(8);
	    
	    $seg5 = str_replace('~','/',$seg5);

	   
	    $data['hardwaremodel'] = $seg4;
	    $data['portname'] = $seg5; 
	    $data['vlanid'] = $seg6; 
	    $data['mode'] = $seg7;
	    $data['FQDN'] = $seg8;

	    $result = $this->switches_model->delete_port_vlan($seg8, $seg4, $seg5,  $seg6,  $seg7);		
		print '<pre>';
		var_dump($result);
		print '</pre>';					
	}

	public function test_filterknownvlan()
	{
		print '<B><U>Purpose of Test:</U></B><p>';
		print 'Ensure that we never return VLAN 1, or any of "default / out of box" CISCO vlans including:<p>';
		print 'fddi-default, token-ring-default,fddinet-default,trnet-default<p>';
		print '<B><U>How to Use This Routine: </U></B><p>';
		print 'You must pass the following parameters: <BR>';
		print 'Fully Qualified Domain Name for switch<BR>';		
		print 'Hardware Model of switch<BR>';
	  	print 'Sample URL:<BR> ';
	  	print 'https://localhost/lsm/index.php/switches/test_filterknownvlan/fully_qualified_switch_name/Cisco IE-3000-8TC/<BR>';

	    $seg4 = $this->uri->segment(4);
	    $seg3 = $this->uri->segment(3);	    

	    $data['hardwaremodel'] = $seg4;
	    $data['FQDN'] = $seg3;

	    $result = $this->switches_model->show_known_vlans($seg3, $seg4);		
		print '<pre>';
		print_r($result);
		print '</pre>';
					

	}	

	public function test_deleteportvlanCISCOIE3000()
	{
		print '<B><U>Purpose of Test:</U></B><p>';
		print 'Test deleting vlans on CISCO IE3000 ports.<p>';
		print '<B><U>How to Use This Routine: </U></B><p>';
		print 'You must pass the following parameters: <BR>';
		print 'DNS Name of switch<BR>';
		print 'Hardware Model of switch<BR>';
		print 'Port Number <BR>';
		print 'Vlan ID to delete <BR>';	
		print 'Vlan Mode<BR>';	
		print 'Fully Qualified Domain Name for switch<BR>';		
	
	  	print 'Sample URL:<BR> ';
	  	print 'https://localhost/lsm/index.php/switches/test_deleteportvlanCISCOIE3000/switch_name/Cisco%20IE-3000-8TC/fa1~1/59/tagged/fully_qualified_switch_name<BR>';		
		//http://jllinuxdev/lsm/index.php/switches/deleteportvlan/' +  $('#dnsName').val() + '/' + $('#hardwaremodel').val() + '/' + $('#port').val() + '/' + userSelectionVlandId + '/' + modeForVlanToDelete + '/' + $('#FQDN').val();
	      $seg4 = $this->uri->segment(4);
	      $seg5 = $this->uri->segment(5);
	      $seg6 = $this->uri->segment(6);  
	      $seg7 = $this->uri->segment(7);
	      $seg8 = $this->uri->segment(8);
	      
	      $seg5 = str_replace('~','/',$seg5);

	      $data['hardwaremodel'] = $seg4;
	      $data['portname'] = $seg5; 
	      $data['vlanid'] = $seg6; 
	      $data['mode'] = $seg7;
	      $data['FQDN'] = $seg8;

	      print '...attempting to connect to:<BR>'. $data['FQDN']. '<BR>';

	      print 'passing data: FQDN = '.$data['FQDN'].' , Hardware Model = '.$data['hardwaremodel']. ' PortName = '. $data['portname'].' VlanID = '. $data['vlanid'].' Mode = '.$data['mode']. '<BR>';    
	      $result = $this->switches_model->delete_port_vlan($data['FQDN'], $data['hardwaremodel'], $data['portname'],  $data['vlanid'],  $data['mode']);		
	    
	      if ($result !== false)  {
		    	$resultdata = array(
		      	'status' => true,
		      	'error_msg' =>'',
		      	'data' => $result
		      	);
	      }
	      else  {
		    	$resultdata = array(
		      	'status' => false,
		      	'error_msg' =>'Unable to delete port vlan. Contact Administrator.',
		      	'data' => false
		      	);		  
	      }//end if
	echo $resultdata;    
	}
	public function test_deleteportvlanCISCOSF300()
	{
		print '<B><U>Purpose of Test:</U></B><p>';
		print 'Test deleting vlans on CISCO IE3000 ports.<p>';
		print '<B><U>How to Use This Routine: </U></B><p>';
		print 'You must pass the following parameters: <BR>';
		print 'DNS Name of switch<BR>';
		print 'Hardware Model of switch<BR>';
		print 'Port Number <BR>';
		print 'Vlan ID to delete <BR>';	
		print 'Vlan Mode<BR>';	
		print 'Fully Qualified Domain Name for switch<BR>';		
	
	  	print 'Sample URL:<BR> ';
	  	print 'https://localhost/lsm/index.php/switches/test_deleteportvlanCISCOSF300/switch_name/Cisco%20SF%20300-48/fa1/70/tagged/fully_qualified_switch_name<BR>';				
	      $seg4 = $this->uri->segment(4);
	      $seg5 = $this->uri->segment(5);
	      $seg6 = $this->uri->segment(6);  
	      $seg7 = $this->uri->segment(7);
	      $seg8 = $this->uri->segment(8);
	      
	      $seg5 = str_replace('~','/',$seg5);

	      $data['hardwaremodel'] = $seg4;
	      $data['portname'] = $seg5; 
	      $data['vlanid'] = $seg6; 
	      $data['mode'] = $seg7;
	      $data['FQDN'] = $seg8;

	      print '...attempting to connect to:<BR>'. $data['FQDN']. '<BR>';

	      print 'passing data: FQDN = '.$data['FQDN'].' , Hardware Model = '.$data['hardwaremodel']. ' PortName = '. $data['portname'].' VlanID = '. $data['vlanid'].' Mode = '.$data['mode']. '<BR>';    
	      $result = $this->switches_model->delete_port_vlan($data['FQDN'], $data['hardwaremodel'], $data['portname'],  $data['vlanid'],  $data['mode']);		
	    
	      if ($result !== false)  {
		    	$resultdata = array(
		      	'status' => true,
		      	'error_msg' =>'',
		      	'data' => $result
		      	);
	      }
	      else  {
		    	$resultdata = array(
		      	'status' => false,
		      	'error_msg' =>'Unable to delete port vlan. Contact Administrator.',
		      	'data' => false
		      	);		  
	      }//end if
	print_r($resultdata);    
	}
	public function test_addportvlanCISCOSF300()
	{
		print '<B><U>Purpose of Test:</U></B><p>';
		print 'Test adding both tagged / untagged vlans on CISCO IE3000 ports.<p>';
		print '<B><U>How to Use This Routine: </U></B><p>';
		print 'You must pass the following parameters: <BR>';
		print 'DNS Name of switch<BR>';
		print 'Hardware Model of switch<BR>';
		print 'Port Number <BR>';
		print 'Vlan ID to delete <BR>';	
		print 'Vlan Mode<BR>';	
		print 'Fully Qualified Domain Name for switch<BR>';		
	
	  	print 'Sample URL:<BR> ';
	  	print 'https://localhost/lsm/index.php/switches/test_addportvlanCISCOSF300/switch_name/Cisco%20SF%20300-48/fa1/70/tagged/fully_qualified_switch_name<BR>';				
	      $seg4 = $this->uri->segment(4);
	      $seg5 = $this->uri->segment(5);
	      $seg6 = $this->uri->segment(6);  
	      $seg7 = $this->uri->segment(7);
	      $seg8 = $this->uri->segment(8);
	      
	      $seg5 = str_replace('~','/',$seg5);

	      $data['hardwaremodel'] = $seg4;
	      $data['portname'] = $seg5; 
	      $data['vlanid'] = $seg6; 
	      $data['mode'] = $seg7;
	      $data['FQDN'] = $seg8;

	      print '...attempting to connect to:<BR>'. $data['FQDN']. '<BR>';

	      print 'passing data: FQDN = '.$data['FQDN'].' , Hardware Model = '.$data['hardwaremodel']. ' PortName = '. $data['portname'].' VlanID = '. $data['vlanid'].' Mode = '.$data['mode']. '<BR>';    
	      $result = $this->switches_model->change_port_vlan($data['FQDN'], $data['hardwaremodel'], $data['portname'],  $data['vlanid'],  $data['mode']);		
	    
	      if ($result !== false)  {
		    	$resultdata = array(
		      	'status' => true,
		      	'error_msg' =>'',
		      	'data' => $result
		      	);
	      }
	      else  {
		    	$resultdata = array(
		      	'status' => false,
		      	'error_msg' =>'Unable to delete port vlan. Contact Administrator.',
		      	'data' => false
		      	);		  
	      }//end if
	print_r($resultdata);    
	}
	public function test_saveconfigCISCOSF300()
	{
		print '<B><U>Purpose of Test:</U></B><p>';
		print 'Test saving configuration on CISCO SF300<p>';
		print '<B><U>How to Use This Routine: </U></B><p>';
		print 'You must pass the following parameters: <BR>';
		print 'Hardware Model of switch<BR>';	
		print 'Fully Qualified Domain Name for switch<BR>';		
	
	  	print 'Sample URL:<BR> ';
	  	print 'https://localhost/lsm/index.php/switches/test_saveconfigCISCOSF300/Cisco%20SF%20300-48/CAN-A2-152-SW-3.can.jwm2.net<BR>';				
	      $seg3 = $this->uri->segment(3);
	      $seg4 = $this->uri->segment(4);

	      $result = $this->switches_model->save_config($seg4, $seg3);		
	    
	      if ($result !== false)  {
		    	$resultdata = array(
		      	'status' => true,
		      	'error_msg' =>'',
		      	'data' => true
		      	);
	      }
	      else  {
		    	$resultdata = array(
		      	'status' => false,
		      	'error_msg' =>'Unable to delete port vlan. Contact Administrator.',
		      	'data' => false
		      	);		  
	      }//end if
	var_dump($result);    
	}	

	public function test_getbuildingsinLocation()
	{
		print '<B><U>Purpose of Test:</U></B><p>';
		print 'Test logic that extracts buildings from branch location tags in racktables<p>';
		print '<B><U>How to Use This Routine: </U></B><p>';
	  	print 'Sample URL:<BR> ';
	  	print 'https://localhost/lsm/index.php/switches/test_getbuildingsinLocation/9<BR>';		
	  
	    $branchlocation = $this->uri->segment(3);
	    print "branch location is set to ". $branchlocation ."<BR>";
 		print "retrieving all locations from racktables first...<BR>";
	    $data = $this->getbuildings_htmlout();
	    echo "<pre>";
	    var_dump($data);
	    echo "</pre>";
	}		
	public function test_sqlite_db_connection()
	{
		$this->load->model('lsm_model');
		//echo CI_VERSION;
		//$this->lsm_model->add_device();
		$data = $this->lsm_model->get_all_devices();
		echo "<PRE>";
		var_dump($data);
		echo "</PRE>";
}

	public function get_version() {

    echo CI_VERSION; // echoes something like 1.7.1

	}
}

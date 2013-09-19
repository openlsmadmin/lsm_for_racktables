<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Devices extends CI_Controller {


	public function __construct()
	{
		parent::__construct();
		$this->load->model('rackhack_model');
		$this->load->helper('url');
	}//__construct

	 /* 
	    Returns string representing hardware model.  eg)  Cisco Aironet 1140
	    To invoke this method:  http://localhost/rackAPI/index.php/switches/hardwaremodelname/971
	
	public function hardwaremodelname()
	{
		$model= $this->rackhack_model->get_switch_model_name($this->uri->segment(3));
		$model= str_replace("%GPASS%"," ",$model);
		//header ('Content-Type: application/json; charset=UTF-8');
		//echo json_encode($model);
		

	}//hardwaremodelname
	*/
	
	public function getallswitches()
	{
		$data= $this->rackhack_model->get_all_switches();	
		header ('Content-Type: application/json; charset=UTF-8');
		echo json_encode($data);
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

		    if(!$building_id){
	        	$building_id=0;
	    	}
		    if(!$room_id){
	        	$room_id=0;
	    	}
			//call get_switches by location
			$data=$this->rackhack_model->get_switches_by_location($branch_id, $building_id, $room_id);	
    	}
	
		header ('Content-Type: application/json; charset=UTF-8');
		echo json_encode($data);
		return $data;

	}//getswitches	
	
	public function getwaps()
	{
		$location_id = $this->uri->segment(3, 0); // returns 0 if uri segment 3 is not set
		if ($location_id>0)
		{
			//call get_switches by location
			$data=$this->rackhack_model->get_waps_by_location($location_id);
		}
		else 
		{
			$data= $this->rackhack_model->get_waps();	
		}
	
		header ('Content-Type: application/json; charset=UTF-8');
		echo json_encode($data);
		

	}//getswitches	

	public function getswitchdetails()
	{
	    $data = $this->rackhack_model->get_switch_details($this->uri->segment(3));
	    //$this->output->enable_profiler(TRUE);
	    header ('Content-Type: application/json; charset=UTF-8');
	    echo json_encode($data);	    
	}////switchdetails
	

	public function getlocations()
	{
		$data = $this->rackhack_model->get_locations();
	    header ('Content-Type: application/json; charset=UTF-8');
	    echo json_encode($data);	
	    //$this->output->enable_profiler(TRUE);
	}//getlocations

	public function getlockedports($object_id)
	{
		$data = $this->rackhack_model->get_locked_ports($this->uri->segment(3));
	    if ($data)
	    {
	    header ('Content-Type: application/json; charset=UTF-8');
	    echo json_encode($data);	
	    }
	    else
	    {
	      return false;
	    }
	    
	}//getlockedports

	//this is just a demo of how to use the HTML Table Class. 
	//It is a codeigniter library, which you can extend. 
	//creates an HTML table based on data from database.  Really fast.  Simple. 
	//But you can't specify css class for the table without modifying the library. 

	public function autotabletest()
	{
		$this->load->library('table');

		$data = $this->rackhack_model->get_locations();

		echo $this->table->generate($data);
		$this->output->enable_profiler(TRUE);
	}
}
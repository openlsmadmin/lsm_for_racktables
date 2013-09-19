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

class Objects extends CI_Controller {
	public function __construct()
	{
		parent::__construct();
		$this->load->model('networkswitch_model');
		$this->load->helper('url');
	}
	
	public function getallobjects()
	{
		
		$data['switchdetails'] = $this->networkswitch_model->get_switch_details($this->uri->segment(3));

		$data['main_content']='objects';
		$this->load->view('includes/template', $data);
	}
}
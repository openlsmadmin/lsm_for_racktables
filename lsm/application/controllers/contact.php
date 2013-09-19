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
	
class Contact extends CI_Controller {
	function index()
	{
		$this->load->helper('url');
		$data['main_content'] = 'contact_form';
		$this->load->view('includes/template',$data);
	}
	
	function submit()
	{
		$name = $this->input->post('name');
		$is_ajax = $this->input->post('ajax');
		
		$data['main_content'] = 'contact_submitted';
		
		if($is_ajax){
			$this->load->view($data['main_content']);
		}
		else {
			$this->load->view('includes/template',$data);
			
		}
	}
	

}

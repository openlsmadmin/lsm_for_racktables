 <!--
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
-->
<div class="container-fluid">
  <div class="row-fluid">		
			<div class="span12">
			
			<h2>Assign New Duplex Value - <?php echo $dnsName; ?></h2>
			
			<h3>Port: <?php echo $portname;?></h3> 
			<p><p>
			<?php
			
			$attributes = array('class' => 'well', 'id' => 'duplex');
			$newportname = str_replace('/','~',$portname);
			echo form_open('switches/assignduplex/'.$hardwaremodel.'/'.$newportname.'/'.$dnsName.'/'.$FQDN.'/'.$objectid, $attributes);
			
			$options = array(
					  '10-half'  => '10-half',
					  '100-half'    => '100-half',
					  '10-full'   => '10-full',
					  '100-full' => '100-full',					 
					   'auto' => 'auto',
					   'auto-10' => 'auto-10',
					   'auto-100' => 'auto-100',
					   'auto-1000' => 'auto-1000',					  
					);

			//$duplexsetting = array('small', 'large');
			
			echo form_dropdown('duplex', $options, '10-half');
			echo "&nbsp;&nbsp;";
			echo form_submit('submit', 'save changes');
			echo form_close()
			?>

			</div>
          </div>
 </div>         
   
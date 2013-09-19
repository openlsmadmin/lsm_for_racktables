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
		<h2><?php echo $dnsName.'  ( '.$hardwaremodel. ' )'; ?></h2>

			<div class="span11">
			
			<?php if ($connectable) {?>

				  
				  <?php 	
				  		//Sample Data:
						//Array ( [0] => Array ( [oif] => [type] => regular [IP4Address] => 10.14.2.1 ) [1] => Array ( [osif] => vlan3 [type] => regular [IP4Address] => 10.14.3.44 ) )  <div class="row-fluid">  
				  		foreach ($switchDetails['IPV4'] as $item => $ipdata): 
				  			?>
				  			
				     	<div class="row show-grid">
							<div class="span2"><h3><?php echo $ipdata['IP4Address']; ?></H3></div>	
						</div>
						<div class="row show-grid">					 
								  <div class="span2"><div id="macaddy" class="qtipbasic"><?php echo anchor("switches/macaddresses/".$ipdata['IP4Address'].'/'.$hardwaremodel.'/'.$objectid.'/'.$dnsName.'/'.$FQDN,'mac-addresses'); ?></div></div>		
								  <div class="span2"><div id="logs" class="qtipbasic"><?php echo anchor("switches/logs/".$ipdata['IP4Address'].'/'.$hardwaremodel.'/'.$objectid.'/'.$dnsName.'/'.$FQDN,'logs'); ?></div></div>	
								  <div class="span2"><div id="portstatus" class="qtipbasic"><?php echo anchor("switches/allportsstatus/".$ipdata['IP4Address'].'/'.$hardwaremodel.'/'.$objectid.'/'.$dnsName.'/'.$FQDN,'port status'); ?></div></div>							
					  			  
					  			  <div class="span2"><div id="indicatorlight" class="qtipbasic"><?php echo anchor("switches/indicatorlight/".$ipdata['IP4Address'].'/'.$hardwaremodel.'/'.'1'.'/'.$objectid.'/'.$dnsName.'/'.$FQDN,'On'); ?>
								   &nbsp;/&nbsp;<?php echo anchor("switches/indicatorlight/".$ipdata['IP4Address'].'/'.$hardwaremodel.'/'.'0'.'/'.$objectid.'/'.$dnsName.'/'.$FQDN,'Off'); ?>	
								  &nbsp;/&nbsp;<?php echo anchor("switches/indicatorlight/".$ipdata['IP4Address'].'/'.$hardwaremodel.'/'.'2'.'/'.$objectid.'/'.$dnsName.'/'.$FQDN,'Blink'); ?></div></div>

								  <div class="span2"><div id="switchlist" class="qtipbasic"><?php echo anchor("switches/getbranches/","Switch List")?></div></div>
					   </div>  	

				  <?php endforeach ?>
				  
			  
			  <?php   }//close If statement ?>
			 
				<table class="table table-bordered">
				<thead>
					<tr>
						<th>Port Name</th>
						<th>Label</th>
						<!--<th>Interface</th> -->
						<!--<th>L2 Address</th> -->
						<th>Reservation Comment</th>
						<th>VLAN</th>
						<th>POE</th>
						
					</tr>
				</thead>
				<tbody>
					
					<?php 
					if (count($switchDetails['ports']) > 0) {
						foreach ($switchDetails['ports'] as $port): 
							?>
						    <tr>				
									<td>												
									    <?php 
										      If (!in_array($port['name'],$portsToLock) and $connectable ) 
										      {
											    echo anchor("switches/portstatus/".$switchDetails['IPV4'][0]['IP4Address'].'/'.$hardwaremodel.'/'.$port['name'].'/'.$objectid.'/'.$dnsName.'/'.$FQDN,$port['name']);
										      }	
										      else 
										      {
										      	echo ($port['name']);
										      }
									    ?>&nbsp;						
									</td>
									<td><?php echo $port['label']?>&nbsp;</td>
									<!--<td><?php echo $port['oif_name'] ?>&nbsp;</td>-->
									<!--<td><?php echo $port['l2address'] ?>&nbsp;</td>-->
									<td><?php echo $port['reservation_comment'] ?></td>	
									<td>&nbsp;
									      <?php 						
										     If (!in_array($port['name'],$portsToLock) and $connectable) 
										    {
											  echo anchor("switches/showportvlan/".$switchDetails['IPV4'][0]['IP4Address'].'/'.$hardwaremodel.'/'.$port['name'].'/'.$objectid.'/'.$dnsName.'/'.$FQDN,'<img src="'.base_url().'assets/img/yellowrouter.png" class="objectdetails">'); 
										    }						      
									      ?>
									 </td>	
									 
									<td>&nbsp;
									      <?php 						
										     If (!in_array($port['name'],$portsToLock) and $connectable ) 
										    {
											  echo anchor("switches/showPoeStatus/".$switchDetails['IPV4'][0]['IP4Address'].'/'.$hardwaremodel.'/'.$port['name'].'/'.$objectid.'/'.$dnsName.'/'.$FQDN,'<img src="'.base_url().'assets/img/poe.jpg" class="objectdetails">'); 
										    }						      
									      ?>
									 </td>							 
						  </tr>						 							
					<?php 
						endforeach;
					}//
					?>
					</p>
				</tbody>
				</table>
			</div>

          </div><!--/row-->
          
         
    <script>
    //console.log('test');
    $('#macaddy a[href]').qtip(
	{
	  content: 'show mac addresses',
	  style: 'blue'
	});
	
      $('#logs a[href]').qtip(
	{
	  content: 'show log',
	  style: 'blue'
	});	
      $('#portstatus a[href]').qtip(
	{
	  content: 'show all port status',
	  style: 'blue'
	});	
	
       $('#indicatorlight a[href]').qtip(
	{
	  content: 'locator light', 	
	  style: 'blue' // Give it some style
	});

       $('#switchlist a[href]').qtip(
	{
	  content: 'Search for switches', 	
	  style: 'blue' // Give it some style
	});       
    </script>

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
   <h2>Interfaces</h2> 
   <h3><?php echo $dnsName; ?></h3>
   <?php echo anchor("switches/details/".$objectid,"Switch Details Page")?>
          <div class="row-fluid">
			<div class="span12">
				<table class="table table-bordered">
				<thead>
					<tr>
						<th>Port</th>
						<th class="lsm-hidden-phone">Type</th>
						<th class="lsm-hidden-phone">Intrusion Alert</th>
						<th>Enabled</th>
						<th>Status</th>
						<th>Mode</th>
						<th class="lsm-hidden-phone">MDI Mode</th>
						<th class="lsm-hidden-phone">Flow Ctrl</th>
						<th class="lsm-hidden-phone">Bcast Limit</th>
					</tr>
				</thead>
				<tbody>
					<p>
					<?php foreach ($listofports as $port): ?>

					    <tr>				
						<td><?php echo $port['Port'] ?></td>
						<td class="lsm-hidden-phone"><?php echo $port['Type'] ?>&nbsp;</td>	
						<td class="lsm-hidden-phone"><?php echo $port['Alert'] ?></td>					
						<td>
							<?php 
									if (!$portsToLock || !in_array($port['Port'],$portsToLock) ) 
									{
										echo anchor("switches/changeHPportstatus/".$hardwaremodel.'/'.$port['Port'].'/'.$port['Enabled'].'/'.$objectid.'/'.$dnsName.'/'.$FQDN, $port['Enabled']);	 
									}
									else {
										echo "Port Locked";
									}									
							?>

						</td>
						<td><?php echo $port['Status'] ?></td>
						<td>
						    <?php 
						    
									if (!$portsToLock || !in_array($port['Port'],$portsToLock) ) 
									{
						    			echo anchor("switches/changeduplexsettings/".$hardwaremodel.'/'.$port['Port'].'/'.$objectid.'/'.$dnsName.'/'.$FQDN,$port['Mode']);
						    		}
						    		else
						    		{
						    			echo "Port Locked";
						    		}
						    ?>
						
						
						
						</td>
						<td class="lsm-hidden-phone"><?php echo $port['MDIMode'] ?></td>
						<td class="lsm-hidden-phone"><?php echo $port['FlowCtrl'] ?></td>
						<td class="lsm-hidden-phone"><?php echo $port['BcastLimit'] ?></td>
					<?php endforeach ?>
					    </tr>
					</p>
				</tbody>
				</table>
			</div>

          </div>
 </div>

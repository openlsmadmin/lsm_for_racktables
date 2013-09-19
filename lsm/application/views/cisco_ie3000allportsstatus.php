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
				<table class="table table-bordered table-striped">
				<thead>
					<tr>
						<th>Port</th>
						<th>Status</th>
						<th class="lsm-hidden-phone">Vlan</th>
						<th>Duplex</th>
						<th class="lsm-hidden-phone">Speed</th>
						<th class="lsm-hidden-phone">Type</th>
					</tr>
				</thead>
				<tbody>
					<p>
					<?php foreach ($listofports as $port): 
							$modifiedPortName = str_replace('/', '~',$port['Port']);
					?>

					    <tr>				
						<td><?php echo $port['Port'] ?></td>
						<td>
							<?php 

									if (!$portsToLock || !in_array($port['Port'],$portsToLock) ) {
										echo anchor("switches/changeCiscoPortStatus/".$hardwaremodel.'/'.$modifiedPortName.'/'.$port['Status'].'/'.$objectid.'/'.$dnsName.'/'.$FQDN, $port['Status']);	 
									}
									else {
										echo "Port Locked";
									}									
							?>							
						</td>	
						<td class="lsm-hidden-phone"><?php echo $port['Vlan'] ?></td>					
						<td>
						    <?php 						    
									if (!$portsToLock || !in_array($port['Port'],$portsToLock) )  {
						    			echo anchor("switches/changeduplexsettings/".$hardwaremodel.'/'.$modifiedPortName.'/'.$objectid.'/'.$dnsName.'/'.$FQDN,$port['Duplex']);
						    		}
						    		else  {
						    			echo "Port Locked";
						    		}
						    ?>

						</td>
						<td class="lsm-hidden-phone"><?php echo $port['Speed'] ?></td>
						<td class="lsm-hidden-phone"><?php echo $port['Type'] ?></td>
					<?php endforeach ?>
					    </tr>
					</p>
				</tbody>
				</table>
			</div>

          </div>
 </div>
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
   <h2><?php echo $dnsName; ?> Interfaces</h2> <?php echo anchor("switches/details/".$objectid,"Switch Details Page")?>
          <div class="row-fluid">
			<div class="span12">									
				<table class="table table-bordered">
				<thead>
					<tr>
						<th>Port</th>
						<th class="lsm-hidden-phone">Type</th>
						<th>Duplex</th>
						<th class="lsm-hidden-phone">Speed</th>
						<th class="lsm-hidden-phone">Neg</th>
						<th class="lsm-hidden-phone">Flow Ctrl</th>
						<th>Admin State</th>
						<th class="lsm-hidden-phone">Link State</th>
						<th class="lsm-hidden-phone">Back Pressure</th>
						<th class="lsm-hidden-phone">Mdix Mode</th>
					</tr>
				</thead>
				<tbody>
					<p>
					<?php foreach ($listofports as $port): ?>

					    <tr>				
						<td><?php echo $port['Port'] ?></td>
						<td class="lsm-hidden-phone"><?php echo $port['Type'] ?>&nbsp;</td>	
						<td>
						    <?php 
											    
							if (!$portsToLock || !in_array($port['Port'],$portsToLock) ) 
							{
								echo anchor("switches/changeduplexsettings/".$hardwaremodel.'/'.$port['Port'].'/'.$objectid.'/'.$dnsName.'/'.$FQDN,$port['Duplex']);									
							}
							else {
								echo "Port Locked";
							}	
						     ?>
						</td>					
						<td class="lsm-hidden-phone"><?php echo $port['Speed'] ?>&nbsp;</td>	
						<td class="lsm-hidden-phone"><?php echo $port['Neg'] ?></td>
						<td class="lsm-hidden-phone"><?php echo $port['FlowCtrl'] ?></td>
						<td>
						    <?php 
							if (!$portsToLock || !in_array($port['Port'],$portsToLock) ) 
							{
								echo anchor("switches/changeportadminstate/".$hardwaremodel.'/'.$port['Port'].'/'.$port['AdminState'].'/'.$objectid.'/'.$dnsName.'/'.$FQDN,$port['AdminState']);	 
							}
							else {
								echo "Port Locked";
							}						      						      						      
						    ?>
						</td>
						<td class="lsm-hidden-phone"><?php echo $port['LinkState'] ?></td>
						<td class="lsm-hidden-phone"><?php echo $port['Back Pressure'] ?></td>
						<td class="lsm-hidden-phone"><?php echo $port['Mdix Mode'] ?></td>
					<?php endforeach ?>
					    </tr>
					</p>
				</tbody>
				</table>
			</div>

          </div>
</div>

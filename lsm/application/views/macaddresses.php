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
     <h2><?php echo $dnsName; ?> MAC Addresses</h2> 
     <?php echo anchor("switches/details/".$objectid,"Switch Details Page")?>
          <div class="row-fluid">
			<div class="span12">

			<?php 
	       
			?>
			       			
				<table class="table table-bordered table-striped">
				<thead>
					<tr>
						<th>Mac Address</th>
						<th>Port</th>
						<th>VLAN</th>
					</tr>
				</thead>
				<tbody>
					<p>
					<?php foreach ($macaddresses as $macaddy): ?>

					    <tr>				
						<td><?php echo $macaddy[0] ?></td>
						<td><?php echo $macaddy[1] ?></td>	
						<td><?php echo $macaddy[2] ?></td>
					<?php endforeach ?>
					    </tr>
					</p>
				</tbody>
				</table>
			</div>

          </div><!--/row-->

	   
    
 
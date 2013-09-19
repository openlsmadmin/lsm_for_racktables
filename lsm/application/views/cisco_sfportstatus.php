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
     <h2><?php echo ' Port: '.$portnumber.' Status'?></h2>
     <h3><?php echo $dnsName;?></h3>   
     <?php echo anchor("switches/details/".$objectid,"Switch Details Page");?>
          <div class="row-fluid">
			<div class="span12">  			
				<table class="table table-bordered">
				<thead>
					<tr>
						<th>Status and Counters</th>
					</tr>
				</thead>
				<tbody>
					<p>
					    <tr>
						<td><p class="text-general"><?php print_r($portstatus); ?></p></td>						
					    </tr>
					</p>
				</tbody>
				</table>
			</div>

          </div><!--/row-->
</div> <!--container -->
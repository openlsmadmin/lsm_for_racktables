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
  <h2><?php echo $title; ?></h2>
          <div class="row-fluid">
			<div class="span12">
				<table class="table table-bordered table-striped">
				<thead>
					<tr>
						<th>Object Name</th>
						<th>Label</th>
						<th>Object ID</th>
					</tr>
				</thead>
				<tbody>
					<p>
					<?php foreach ($switches as $networkswitch): ?>

					    <tr>
						
						<td>
						
						
						<?php 
						$modelnumber="";
						 foreach ($networkswitch['atags'] as $attribute => $value) {

						
						      $pos = strpos(trim($value['tag']), 'hardware_model:'); 
						      if ($pos !== false) {
							  $modelnumber = substr(trim($value['tag']), 15);	 
							  break;
						      }
						 }
						 
						 //echo $modelnumber;
						  echo anchor("switches/details/".$networkswitch['id'].'/'.$modelnumber,$networkswitch['name']);

						
						?></td>
						<td><?php echo $networkswitch['name'] ?></td>
						<td><?php echo $networkswitch['id'] ?></td>
					<?php endforeach ?>
					</p>
				</tbody>
				</table>
			</div>

          </div><!--/row-->


    <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="../assets/js/jquery.js"></script>
    <script src="../assets/js/bs-transition.js"></script>
    <script src="../assets/js/bs-alert.js"></script>
    <script src="../assets/js/bs-modal.js"></script>
    <script src="../assets/js/bs-dropdown.js"></script>
    <script src="../assets/js/bs-scrollspy.js"></script>
    <script src="../assets/js/bs-tab.js"></script>
    <script src="../assets/js/bs-tooltip.js"></script>
    <script src="../assets/js/bs-popover.js"></script>
    <script src="../assets/js/bs-button.js"></script>
    <script src="../assets/js/bs-collapse.js"></script>
    <script src="../assets/js/bs-carousel.js"></script>
    <script src="../assets/js/bs-typeahead.js"></script>

	
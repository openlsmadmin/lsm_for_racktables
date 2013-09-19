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
		<div class="span4"><h2>Port VLANS: Port <?php echo $portname;?></h2></div>
	 </div>
	 <div class="row-fluid">		
		<div class="span4"><h3><?php echo $dnsName;?></h3></div>
      </div>
      <div class="row-fluid">
		<div class="span2"><?php echo anchor("switches/details/".$objectid,"Switch Details Page")?></div>
      </div>
      <div class="row-fluid">
		<p><p>
			<table class="table table-bordered"  id="assignedvlans">
			<thead>
				<tr>
					<!--<th>Select Row:</th>-->
					<th>VLAN ID</th>
					<th class="lsm-hidden-phone">Name</th>
					<th>Mode</th>
					<th>&nbsp;</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($portvlan as $vlandetail):?>
				    <tr>					
					<td class='vlanid'><?php echo $vlandetail['VlanId'] ?></td>
					<td class="lsm-hidden-phone"><?php echo $vlandetail['Name']; ?></td>
					<td class='vlanmode'><?php echo $vlandetail['Mode']; ?></td>
					<td><button class='btn deleteBtn'>Delete</button></td>
				      </tr>
				<?php endforeach ?>
				
			</tbody>
			</table>
			
			<button class="btn" id="modifyVLANS">Modify VLANS</button>
			<!-- add some hidden controls for use in javascript -->
			<input type="hidden" id="hardwaremodel" value="<?php echo $hardwaremodel ?>"/>
			<input type="hidden" id="port" value="<?php echo str_replace('/','~',$portname) ?>"/>
			<input type="hidden" id="objectid" value="<?php echo $objectid ?>"/>
			<input type="hidden" id="dnsName" value="<?php echo $dnsName ?>"/>
			<input type="hidden" id="FQDN" value="<?php echo $FQDN ?>"/>
	</div>
	
	<div class="row-fluid">	
	      <div class="span12" id="progress-indicator" style="text-align:center">
		      <p>
		  <img src="<?php echo base_url('assets/img/progressbar.gif');?>"> retrieving...</img>
	    </div>
	</div>
      <div class="row-fluid">	
	  <p>&nbsp;</p>
	  <div class="span10 label label-warning" id="errormessage"></div>
      </div>
      
      <div class="row-fluid">	
	  <div class="span12" id="availableVlansAjaxContainer"></div>
      </div>

      <div class="row-fluid">	    
	<div class="span12" id="newvlandata"></div>
      </div>	  
</div>

    <script>
    
    $(document).ready(function(){
    	//alert("document is ready");
	    //hide the please wait div by default.	    
	    $('#progress-indicator').hide();
	    $('#errormessage').hide();	


	    $('#updateVlan').live('click', function()  {

				$('#progress-indicator').html("<img src='" + BASEPATH + "assets/img/progressbar.gif' /> adding...</img>");
				$('#progress-indicator').fadeIn();	
				 //grab data from user input		    	
				var IdforVlanToAdd = $('#newVlanID').val();
				var modeForVlanToAdd = $('#newvlanmode').val();
				//is the vlan they want to add already in the list? 
				var bexit=0;				
				var table = document.getElementById('assignedvlans'),
				    rows = table.rows, rowcount = rows.length, r,
				    cells, cellcount, c, cell;
				for( r=0; r<rowcount; r++) {
				    cells = rows[r].cells;
				    //cellcount = cells.length;
				    cellcount = 1; //we only want to check the first column...not all
				    for( c=0; c<cellcount; c++) {
				        cell = cells[c];
				        if (cell.firstChild.nodeValue === IdforVlanToAdd){
				        	bexit = 1;
				        	break;
				        }
				    }
				}
				if (bexit != 1){
						var fullpath = BASEPATH + 'index.php/switches/changeportvlan/' +  $('#dnsName').val() + '/' + $('#hardwaremodel').val() + '/' + $('#port').val() + '/' + IdforVlanToAdd + '/' + modeForVlanToAdd + '/' + $('#FQDN').val();							
						$.ajax({
							    url:fullpath,
							    type:'POST',
							    dataType:'json',
							    success: function(returnDataFromController) {
							    	 if (returnDataFromController.status)
									  {									
										//build table contents 
										var htmlstring = '<thead>';  
										htmlstring = htmlstring + '<tr>';
										htmlstring = htmlstring + '<th>VLAN ID</th>';
										htmlstring = htmlstring + '<th class="lsm-hidden-phone">Name</th>';
										htmlstring = htmlstring + '<th>Mode</th>';
										htmlstring = htmlstring + '<th>&nbsp;</th>';
										htmlstring = htmlstring + '</tr>';
										htmlstring = htmlstring + '</thead>';
										htmlstring = htmlstring + '<tbody>';
					  
										//loop through results from ajax call and build table.  
										for(i = 0; i < returnDataFromController.data.length; i++) {
											  //alert(returnDataFromController[i].VlanId);
											  htmlstring = htmlstring + "<tr>"
											  htmlstring = htmlstring + "<td class ='vlanid'>" + returnDataFromController.data[i].VlanId + "</td>";
											  htmlstring = htmlstring + "<td class='lsm-hidden-phone'>" + returnDataFromController.data[i].Name + "</td>";
											  htmlstring = htmlstring + "<td class ='vlanmode'>" + returnDataFromController.data[i].Mode +"</td>";
											  htmlstring = htmlstring + "<td><button class='btn deleteBtn'>Delete</button></td>";
											  htmlstring = htmlstring + "</tr>";	
									  
									         }//end loop
									    htmlstring = htmlstring + "</tbody>";
									    htmlstring = htmlstring + "</table>";

									    $('#assignedvlans').html(htmlstring);
									    $('#progress-indicator').hide();
									    
								      }//end if
								      else
								      {
									      $('#errormessage').html(returnDataFromController.error_msg);
									      $('#errormessage').fadeIn();
									      $('#progress-indicator').hide();					      
								      }					      

							    },// end success
							    error:  function(jqXHR, textStatus, errorThrown)
							    {
								  alert(errorThrown);
								  log_error('deleteportvlan: ' + errorThrown);
							    }
						    });//end ajax.
				}
				else{
					alert('VLAN ' + IdforVlanToAdd + ' is already associated with this port!');
					$('#progress-indicator').hide();	
				}				
	    });
		
	    //notice that you are referencing the delete button by class name. 
	    //bad design to have same ID name for multiple objects.  So referencing it by class instead.
	    $('.deleteBtn').live('click', function()  {

	    			//get a count of all records. only allowed to delete if you have more than one vlan.
	    			var vlancount = $('#assignedvlans tbody tr').length;
	    			if (vlancount > 1)
	    			{							    
							$('#progress-indicator').html("<img src='" + BASEPATH + "assets/img/progressbar.gif' /> deleting...</img>");
							$('#progress-indicator').fadeIn();								
							var userSelectionVlandId  = $(this).parent().siblings('.vlanid').text();	
							//alert(userSelectionVlandId);
							var modeForVlanToDelete = $(this).parent().siblings('.vlanmode').text();					

							var fullpath = BASEPATH + 'index.php/switches/deleteportvlan/' +  $('#dnsName').val() + '/' + $('#hardwaremodel').val() + '/' + $('#port').val() + '/' + userSelectionVlandId + '/' + modeForVlanToDelete + '/' + $('#FQDN').val();
							//console.log(fullpath);
							$.ajax({
								    url:fullpath,
								    type:'POST',
								    dataType:'json',
								    success: function(returnDataFromController) {
								    //alert(returnDataFromController);
										    if (returnDataFromController.status)
										    {		    
											    //build table contents 
											    var htmlstring = '<thead>';  
											    htmlstring = htmlstring + '<tr>';
											    htmlstring = htmlstring + '<th>VLAN ID</th>';
											    htmlstring = htmlstring + '<th class="lsm-hidden-phone">Name</th>';
											    htmlstring = htmlstring + '<th>Mode</th>';
											    htmlstring = htmlstring + '<th>&nbsp;</th>';
											    htmlstring = htmlstring + '</tr>';
											    htmlstring = htmlstring + '</thead>';
											    htmlstring = htmlstring + '<tbody>';
						      
											    //loop through results from ajax call and build table.  
											    for(i = 0; i < returnDataFromController.data.length; i++) {										     
												      htmlstring = htmlstring + "<tr>"
												      htmlstring = htmlstring + "<td class ='vlanid'>" + returnDataFromController.data[i].VlanId + "</td>";
												      htmlstring = htmlstring + "<td class='lsm-hidden-phone'>" + returnDataFromController.data[i].Name + "</td>";
												      htmlstring = htmlstring + "<td class='vlanmode'>" + returnDataFromController.data[i].Mode +"</td>";
												      htmlstring = htmlstring + "<td><button class='btn deleteBtn'>Delete</button></td>";
												      htmlstring = htmlstring + "</tr>";									
											    }//end loop

											    htmlstring = htmlstring + "</tbody>";
											    htmlstring = htmlstring + "</table>";

											    $('#assignedvlans').html(htmlstring);
											    $('#progress-indicator').hide();	
										      }
										      else
										      {
											    $('#errormessage').html(returnDataFromController.error_msg);
											    $('#errormessage').fadeIn();
											    $('#progress-indicator').hide();									      
										      }
								      },//end success ajax call
								      error:  function(jqXHR, textStatus, errorThrown)
								      {
									    alert(errorThrown);							   
								      }

							});//end ajax.						    					 
	    			}
	    			else
	    			{	//console.log('asdf');
	    				alert("Port must have at least one vlan!");
	    			}
	    			 
	    });	    

	    //query and show list of vlans.
	    $('#modifyVLANS').click(function()  {
			     //alert('in the click');
				//disbled button until ajax request is done. 
				$(this).attr("disabled","disabled");
				$('#progress-indicator').html("<img src='" + BASEPATH + "assets/img/progressbar.gif' /> retrieving...</img>");
				$('#progress-indicator').fadeIn();
				
				$.ajax({				  
				  url:'<?php echo site_url('switches/showknownvlans/'.$dnsName.'/'.$hardwaremodel.'/'.$FQDN);?>',
				  type:'POST',
				  dataType:'json',
				  success: function(returnDataFromController) {
					    //alert(returnDataFromController.length);
					    //console.log(returnDataFromController);
					    var htmlstring;
					    var newVlanHTML;
					    htmlstring = "<br><br><B>To reassign the port to a new vlan, click on a VLAN ID below and then click on the <u>Add VLAN</u> button below.</B><br><table class='table table-bordered'>";
					    htmlstring = htmlstring + "<th>VLAN ID</th><th>Name</th>";
					    
					    //loop through results
					    for(i = 0; i < returnDataFromController.length; i++) {
								//alert(returnDataFromController[i].VlanId);
								htmlstring = htmlstring +  "<tr><td><a href=>"+returnDataFromController[i].VlanId+"</a></td><td>"+ returnDataFromController[i].Name+"</td></tr>";						  
					    }
					    //note:  adding onClick='return false;' to the delete button allows us to put it inside the form, without have it 
					    //trigger the submit.  this way the delete button shows up beside the add button, instead of below.
					    newVlanHTML = "<input type='text' name='newVlanID' id='newVlanID' style='width:5em;height:1.5em'/>&nbsp;&nbsp;<select name='newvlanmode' id='newvlanmode'><option value='untagged'>untagged</option><option selected value='tagged'>tagged</option></select>&nbsp;&nbsp;<button type='submit' class='btn' name='updateVlan' id='updateVlan' style='width:10em;height:2em'>Add VLAN</button>";
					    //alert(newVlanHTML);
					    $('#availableVlansAjaxContainer').html(htmlstring);
					    $('#newvlandata').html(newVlanHTML);						
					    $('#progress-indicator').hide();
					  },//end success
					error:  function(jqXHR, textStatus, errorThrown)
					{
					    alert(errorThrown);							   
					}
			      }); //end ajax call
			      $(this).removeAttr("disabled");
	    });
      
	      //adding a click event handler to the list of vlans.  populate the textbox with the value of vlan that is selected.    
	    $("#availableVlansAjaxContainer").delegate("td:first-child a", "click", function(ev) {
				ev.preventDefault();
				$("#newVlanID").val($(this).text());
	    });
	    

      	// prevent users from manually typing inside the newvlanid textbox
      	$('#newVlanID').live('keypress', function(e)  {
   			 e.preventDefault();
		});	    
  }); 
    </script>

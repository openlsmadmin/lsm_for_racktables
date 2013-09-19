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
<div class="container-fluid" id="locationsbar">			
	<div class="row-fluid">
			<div class="span12" id="l1locations">
				<p id="yourgeolocation"></p>
				<h2><?php echo $title;?></h2>
				
				<div class="span3">
						<h4>Branch:</h4>
						<select name='L1Locations' id='L1Locations'>
							<option selected value''></option>
							<?php
								foreach ($branches as $key=>$value)
								{
									echo '<option value="'.$key.'">'.$value."</option>";
								}

							?>
						</select>	
				</div>
				<div class="span3" id="l2locations"></div>
				<div class="span3" id="l3locations"></div>
				<h4>&nbsp;</h4>
				<button class="btn search">Search</button>
			</div>
	</div>

	<!-- DISPLAY RESULTS -->
	<div class="row-fluid">
		
				<div class="span12 visible-desktop visible-tablet" id="l1locations">
						<table id="switchrecords" name="switchrecords" class="table table-bordered">

						</table>
				</div>

				<div class="span12 visible-phone" id="l1locations">
						<div id="mobileswitchrecords">

						</div>
				</div>				

	</div>
</div>


<script>


$(document).ready(function(){
      $('#search').show();
  });

$('.search').live('click',function(){
		   //reset table first. 
		   $('#switchrecords').html('');
		   $('#mobileswitchrecords').html('');
		   // BRANCH
		  $location1selection = $('#L1Locations').val();

		  //BUILDLING
		  if ($('#L2Locations').length) // equivalent to doing exists()
		  {
		  	$location2selection = $('#L2Locations').val(); //looks like 2.5		  
		  	$l2val = $location2selection.split(".")			//split 2.5 into an array
		  	$l2val=($l2val[$l2val.length-1] );	//get the last element of the array.  s/b 5 in this eg)		  	
		  }
		  else
		  {
		  	$l2val = 0;

		  }
		  
		  //ROOM		  
		  if ($('#L3Locations').length) // equivalent to doing exists()
		  {
		  	$location3selection = $('#L3Locations').val();
 
		  	$l3val = $location3selection.split(".")			//split 2.5 into an array
		  	$l3val=($l3val[$l3val.length-1] );	//get the last element of the array.  s/b 5 in this eg)			  	
		  }
		  else
		  {
		  	$l3val = 0;
		  }
		 
		//alert(BASEPATH + 'index.php/switches/getswitchesbylocation/' + $location1selection + '/' + $l2val + '/' + $l3val);  		
		$.ajax({
			    url: BASEPATH + 'index.php/switches/getswitchesbylocation/' + $location1selection + '/' + $l2val + '/' + $l3val,
			    type:'POST',
			    dataType:'json',
			    success: function(returnDataFromController) {
			    	//alert('in success');
			    		var htmlstring;
			    		htmlstring="<select name='L2Locations' id='L2Locations'>";
			    		htmlstring = htmlstring + "<option value=9999>All</option>";

	
						 var JSONdata=returnDataFromController;
						 //console.log(JSONdata);
						 if (JSONdata.length != 0) 
					     {

					     		//create a heading row and attach it to the existing switchrecords table.
					     		var heading = $('<tr id="tblheading" naming="tblheading">').appendTo($('#switchrecords'));
								heading.append($('<td>').append().text('Branch'));
								heading.append($('<td>').append().text('Branch Switch Name'));
								heading.append($('<td>').append().text('Building'));
								heading.append($('<td>').append().text('Building Switch Name'));
								heading.append($('<td>').append().text('Room'));
								heading.append($('<td>').append().text('Room Switch Name'));
 	 	 	 	 	 	 	 	 	 	
								var mobilerow  = $('<ul class="nav nav-list bs-docs-sidenav">').appendTo($('#mobileswitchrecords'));
								
								//loop through each JSONdata item in the array and append another row to the switchrecords table.								
								$.each(JSONdata, function(i, objswitch) {								
								    var row = $('<tr>').appendTo($('#switchrecords'));								   

									if (objswitch.BranchName) {  row.append($('<td>').append(objswitch.BranchName).text(objswitch.BranchName)); }
									else {  row.append($('<td>').append().text('')); }	

									if (objswitch.BranchSwitchName) {  
										row.append($('<td>').append($('<a href='+ BASEPATH + 'index.php/switches/details/' + objswitch.BranchSwitchID +'>').text(objswitch.BranchSwitchName)));
										mobilerow.append($('<li>').append($('<a href='+ BASEPATH + 'index.php/switches/details/' + objswitch.BranchSwitchID +'>').text(objswitch.BranchSwitchName)));
									}
									else  {  row.append($('<td>').append().text('')); }
					
									if (objswitch.BuildingName)  {  row.append($('<td>').append(objswitch.BuildingName).text(objswitch.BuildingName)); }
									else  {  row.append($('<td>').append().text(''));  }				

									if (objswitch.BuildingSwitchName)  {  
											row.append($('<td>').append($('<a href='+ BASEPATH + 'index.php/switches/details/' + objswitch.BuildingSwitchID +'>').text(objswitch.BuildingSwitchName)));	
											mobilerow.append($('<li>').append($('<a href='+ BASEPATH + 'index.php/switches/details/' + objswitch.BuildingSwitchID +'>').text(objswitch.BuildingSwitchName)));	
									}
									else  {  row.append($('<td>').append().text(''));  }	

									if (objswitch.RoomName)  {  row.append($('<td>').append(objswitch.RoomName).text(objswitch.RoomName));  }
									else  {  row.append($('<td>').append().text(''));  }	

									if (objswitch.RoomSwitchName)  {  
										row.append($('<td>').append($('<a href='+ BASEPATH + 'index.php/switches/details/' + objswitch.RoomSwitchID +'>').text(objswitch.RoomSwitchName)));	
										mobilerow.append($('<li>').append($('<a href='+ BASEPATH + 'index.php/switches/details/' + objswitch.RoomSwitchID +'>').text(objswitch.RoomSwitchName)));
									}
									else  {  row.append($('<td>').append().text(''));  }


							
							});

						 }
											   				
			   		}//success


			});//end ajax
  			

});


$('#L1Locations').live('change',function(){

		 $('#search').hide();
		 $('#l2locations').html('');
		 $('#l3locations').html('');		
		 
		  var htmlstring;
		  $selectedvalue = $('#L1Locations').val();
		
		  if ($selectedvalue !='')  //if they want all locations don't do the look ups.
		  {

				  $.ajax({
						    url:"<?php echo site_url('switches/getbuildings/');?>" + "/" + $selectedvalue,
						    type:'GET',
						    dataType:'json',
						    success: function(returnDataFromController) {
						    	   //console.log("getbuildings ajax call successfull");
						    		var htmlstring;
						    		htmlstring="<h4>Building:</h4><select name='L2Locations' id='L2Locations'>";
						    		htmlstring = htmlstring + "<option value='all'>All</option>";

								    //console.log(returnDataFromController);
		    						 var JSONdata=[returnDataFromController];
		    						 //console.log(JSONdata);
		    						 if (JSONdata.length != 0) 
								     {
									        for(var i=0;i<JSONdata.length;i++){
									        var obj = JSONdata[i];
									     
											          for(var key in obj){
											                 var locationkey = key;
											                 var locationname = obj[key];
											    			 htmlstring = htmlstring + "<option value='" + locationkey + "'>" + locationname + "</option>";
											            } //end inner for											        											    	
											}//end outer for
											htmlstring = htmlstring + "</select>";
											$('#l2locations').html(htmlstring);												
										}

										else {
											//alert('i think undefined');
											$('#l2locations').html('');
										}
														   				
						   		}//success
			

						});//end ajax
			}
  			$('#search').show();
  });

$('#L2Locations').live('change',function(){
	 	 $('#l3locations').html('');
		 $('#search').hide();
		  var htmlstring;
		  $selectedvalueL1 = $('#L1Locations').val();
		  $selectedvalueL2 = $('#L2Locations').val();
		
		  $.ajax({
				    url:"<?php echo site_url('switches/getrooms');?>" + "/" + $selectedvalueL2,
				    type:'GET',
				    dataType:'json',
				    success: function(returnDataFromController) {
				    	   //console.log("getrooms ajax call successfull");
				    		var htmlstring;
				    		htmlstring="<h4>Room:</h4><select name='L3Locations' id='L3Locations'>";
				    		htmlstring = htmlstring + "<option value='all'>All</option>";

						    //console.log(returnDataFromController);
    						 var JSONdata=[returnDataFromController];
    						 //console.log(JSONdata);
    						 if (JSONdata.length != 0) 
						     {
							        for(var i=0;i<JSONdata.length;i++){
							        var obj = JSONdata[i];
							     
									          for(var key in obj){
									                 var locationkey = key;
									                 var locationname = obj[key];
									    			 htmlstring = htmlstring + "<option value='" + locationkey + "'>" + locationname + "</option>";
									            } //end inner for									         									       
									    	
							
									}//end outer for
									htmlstring = htmlstring + "</select>";
									$('#l3locations').html(htmlstring);
								}

								else {
									//alert('i think undefined');
									$('#l3locations').html('');
								}
												   				
				   		}//success

		});
  $('#search').show();
  });
 </script>

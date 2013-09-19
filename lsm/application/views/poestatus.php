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
	      <div>
		    <!-- add some hidden controls for use in javascrsipt -->
		    <input type="hidden" id="hardwaremodel" value="<?php echo $hardwaremodel ?>"/>
		    <input type="hidden" id="port" value="<?php echo $portname ?>"/>	
		    <input type="hidden" id="objectid" value="<?php echo $objectid ?>"/>	
		    <input type="hidden" id="dnsName" value="<?php echo $dnsName ?>"/>
		    <input type="hidden" id="FQDN" value="<?php echo $FQDN ?>"/>
	      </div>
		      
	     <div class="span6" id="clientajaxcontainer">
	      
		  <h2>PoE Status: Port <?php echo $portname;?></h2>
		      <p>
			    <?php echo anchor("switches/details/$objectid","Switch Details Page");?>
			    <br><br>
		      <?php  echo $poeStatus ?>   
			  <button class="btn enable">Enable PoE</button>&nbsp;
			  <button class="btn disable">Disable PoE</button>		      	 
	     </div>      
	 
    </div>	  
    <p>
    <div class="span6" id="enable-indicator">
	    <img src="<?php echo base_url('assets/img/progressbar.gif');?>" /> enabling...
    </div>	
    <p>
    <div class="span6" id="disable-indicator">
	    <img src="<?php echo base_url('assets/img/progressbar.gif');?>" /> disabling...
    </div>	 
</div>
<script>

$(document).ready(function(){
      $('#enable-indicator').hide();
      $('#disable-indicator').hide();
      
      //Enable PoE button
      $('.enable').live('click', function()  {
				  $('#enable-indicator').fadeIn();
				  $(this).attr("disabled","disabled");
				  $('.disable').attr("disabled","disabled");
				  var htmlstring;
				  $.ajax({
						    url:"<?php echo site_url('switches/poeOn/'.$dnsName.'/'.$hardwaremodel.'/'.$portname.'/'.$FQDN);?>",
						    type:'POST',
						    dataType:'text',
						    success: function(returnDataFromController) {
							htmlstring = "<h2>Port PoE Status: Port " + $('#port').val() + "</h2><a href=" + BASEPATH + "index.php/switches/details/" + $('#objectid').val() + ">Switch Details Page</a><p><p>" + returnDataFromController + "<button class='btn enable'>Enable PoE</button>&nbsp;<button class='btn disable'>Disable PoE</button>";	
							//alert(htmlstring);
							$('#clientajaxcontainer').html(htmlstring);	
							//console.log(htmlstring);
							$('#enable-indicator').fadeOut();
						  }
				});
				//reenable the buttons.
 				$(this).attr("enabled","enabled");
				$('.disable').attr("enabled","enabled");
      });
      
      
      //Disable PoE button
      $('.disable').live('click', function()  {
				$('#disable-indicator').fadeIn();
				//disbled button until ajax request is done. 
				$(this).attr("disabled","disabled");
				$('.enable').attr("disabled","disabled");
				
				$.ajax({
						  url:"<?php echo site_url('switches/poeOff/'.$dnsName.'/'.$hardwaremodel.'/'.$portname.'/'.$FQDN);?>",
						  type:'POST',
						  dataType:'text',
						  success: function(returnDataFromController) {
							htmlstring = "<h2>Port PoE Status: Port " + $('#port').val() + "</h2><a href=" + BASEPATH + "index.php/switches/details/" + $('#objectid').val() + ">Switch Details Page</a><p><p>"  + returnDataFromController + "<button class='btn enable'>Enable PoE</button>&nbsp;<button class='btn disable'>Disable PoE</button>";	
							
							//alert(htmlstring);
							$('#clientajaxcontainer').html(htmlstring);	
							//console.log(htmlstring);
							$('#disable-indicator').fadeOut();
						}
			     });
			      //reenable the buttons.
				$(this).attr("enabled","enabled");
				$('.enable').attr("enabled","enabled");
    });    
});
</script>

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
		<h2><?php echo $dnsName; ?></h2>
		<h3><?php echo $hardwaremodel; ?></h3>
		<br/>
			<?php if ($connectable) {?>
				<div class="span4">
						<div class="row show-grid">	
				  			<div class="span2">
				  				<button type='button' class='btn saveConfig'>&nbsp;<i class="icon-download-alt"></i>Save Config</button>

				  			</div>
				  			<div class="span1" id="progress-indicator">&nbsp;</div>
				  		</div>
				  		<br/>
				</div>
		  		<div class="span5">
				  		<div class="row show-grid">	
			                <div class="btn-group span3">
				                <button class="btn dropdown-toggle" data-toggle="dropdown">Show Info&nbsp;<span class="caret"></span></button>
				                <ul class="dropdown-menu">
				                  <li><div id="macaddy">&nbsp;&nbsp;<?php echo anchor("switches/macaddresses/".$hardwaremodel.'/'.$objectid.'/'.$dnsName.'/'.$FQDN,'mac-addresses'); ?></div></li>
				                  <li><div id="logs">&nbsp;&nbsp;<?php echo anchor("switches/logs/".$hardwaremodel.'/'.$objectid.'/'.$dnsName.'/'.$FQDN,'logs'); ?></div></li>
								  <li><div id="portstatus">&nbsp;&nbsp;<?php echo anchor("switches/allportsstatus/".$hardwaremodel.'/'.$objectid.'/'.$dnsName.'/'.$FQDN,'port status'); ?></div></li>
				                </ul>
			              	</div>
			              	<br/>
 							<div class="span2"><?php echo anchor("switches/getbranches/","Back to Switch List")?></div>
		              </div><!-- end class row show-grid-->		
		              <br/> 
		        </div>

			  <?php   }//close If statement ?>
			 
				<table class="table table-bordered">
				<thead>
					<tr>
						<th>Port Name</th>
						<th class="lsm-hidden-phone">Visible Label</th>
						<th class="lsm-hidden-phone">Reservation Comments</th>
						<th class="lsm-hidden-phone">Linked Objects</th>
						<th>VLAN</th>
					</tr>
				</thead>
				<tbody>
					
					<?php 
					foreach ($switchDetails['ports'] as $port): 
						$modifiedPortName = str_replace('/', '~',$port['name']);
						?>
					    <tr>				
								<td id="portcounters">												
								    <?php 
									      If (!$portsToLock || !in_array($port['name'],$portsToLock) and $connectable ) 
									      {
									      	
										    echo anchor("switches/portstatus/".$hardwaremodel.'/'.$modifiedPortName.'/'.$objectid.'/'.$dnsName.'/'.$FQDN,$port['name']);
									      }	
									      else 
									      {
									      	echo ($port['name']);
									      }
								    ?>&nbsp;						
								</td>
								<td class="lsm-hidden-phone"><?php echo $port['reservation_comment'] ?></td>	
								<td class="lsm-hidden-phone"><?php echo $port['label']?>&nbsp;</td>
								<td class="lsm-hidden-phone"><?php echo $port['PortAName'] ?></td>	
								<td id="portvlan">&nbsp;
								      <?php 						
									     If (!$portsToLock || !in_array($port['name'],$portsToLock) and $connectable) 
									    {
										  echo anchor("switches/showportvlan/".$hardwaremodel.'/'.$modifiedPortName.'/'.$objectid.'/'.$dnsName.'/'.$FQDN,'<img src="'.base_url().'assets/img/yellowrouter.png" class="objectdetails">'); 
									    }						      
								      ?>
								 </td>	
					  </tr>						 							
					<?php endforeach ?>
					</p>
				</tbody>
				</table>
			</div>
          <input type="hidden" id="FQDN" value="<?php if (isset($FQDN)){ echo $FQDN;} ?>"/>
 		  <input type="hidden" id="hardwaremodel" value="<?php if (isset($hardwaremodel)) { echo $hardwaremodel;} ?>"/>
</div>          
         
    <script>
    $(document).ready(function(){
	    //hide the please wait div by default.
	    $('#progress-indicator').hide();	  

	    $('.saveConfig').live('click', function()  {
			
			$('#progress-indicator').html("<img src='" + BASEPATH + "assets/img/progressbar.gif' />  saving...</img>");
			$('#progress-indicator').fadeIn();			    	
    		//grab data from user input		    	

			var fullpath = BASEPATH + 'index.php/switches/saveconfiguration/' + $('#hardwaremodel').val() + '/' + $('#FQDN').val();
			$.ajax({
				      url:fullpath,
				      type:'POST',
				      dataType:'json',
				      success: function(returnDataFromController) {
						  console.log(returnDataFromController);
						  if (returnDataFromController.status)  {						  	
						      $('#progress-indicator').html("<br/><p>Configuration saved</p>");  
						  }	
 						  else {						      
						     $('#progress-indicator').html("oops! there's been a problem.");  
 						  }

				      },// end success
 				    error:  function(jqXHR, textStatus, errorThrown)
 				    {					      
 				    	alert('ajax error handler: ' + errorThrown);
 				    }
			    });//end ajax.
			
				
	    });//end function
	});//end jquery    
    </script>

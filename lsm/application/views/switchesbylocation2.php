<?php
print_r($branches);

?>
<div class="span9" id="locationsbar">
	<h2><?php echo $title;?></h2>
	<div class="span4" id="l1locations">
		<select name='L1Locations' id='L1Locations'>
			<option value='all' selected=selected >All</option>
			<?php
				foreach ($branches as $key=>$value)
				{
					echo '<option value="'.$key.'">'.$value."</option>";
				}

			?>
		</select>	
	</div>

	<div class="span4" id="l2locations"></div>
	<div class="span4" id="l3locations"></div>
	<button class="btn" id="search">Search</button>
</div>	



<script>

$(document).ready(function(){
      $('#search').show();
  });

$('#L1Locations').live('change',function(){
		 $('#search').hide();
		  var htmlstring;
		  $selectedvalue = $('#L1Locations').val();

		  $.ajax({
				    url:"<?php echo site_url('switches/getbuildings/');?>" + "/" + $selectedvalue,
				    type:'POST',
				    dataType:'json',
				    success: function(returnDataFromController) {
				    	dump(returnDataFromController);
				    		var htmlstring;
				    		htmlstring="<select name='L2Locations' id='L2Locations'>";
				    		htmlstring = htmlstring + "<option value='all'>All</option>";

						    console.log(returnDataFromController);
												
				   }

		});
  $('#search').show();
  });
 </script>
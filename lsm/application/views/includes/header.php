<!DOCTYPE html> 
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
<html lang="en">
  <head>
    <meta http-equiv="X-UA-Compatible" content="IE=Edge"/>
    <meta charset="utf-8">
    <title>techie's networking tool</title>
   <!-- <meta name="viewport" content="width=device-width, initial-scale=1.0"> -->
    <meta content='width=device-width; initial-scale=1.0; maximum-scale=1.0;' name='viewport' />
    <meta name="viewport" content="width = device-width">
    
    <meta name="description" content="">
    <meta name="author" content="jlee" >
    <!-- Le styles -->
    <link href="<?php echo base_url('assets/css/bootstrap.css')?>" rel="stylesheet">
    
    <!-- max-width is based off the actual current display size of the BROWSER window, 
    whereas max-device-width is based on the maximum amount the DEVICE can actually display. 
    So if one's screen resolution is 1280, that is the max-device-width, even though the actual browser window display size may be 50% of that screen 
    (640), which is what max-width is tracking.
    -->
    <link href="<?php echo base_url('assets/css/bootstrap-responsive.css')?>" rel="stylesheet">     
    <link href="<?php echo base_url('assets/css/lsm.css')?>" rel="stylesheet">     


    <script src="<?php echo base_url('assets/js/jquery-1.8.1.min.js')?>"></script>
    <script src="<?php echo base_url('assets/js/jquery-ui-1.8.23.custom.min.js')?>"></script>
    <script src="<?php echo base_url('assets/js/jquery.qtip-1.0.0-rc3.min.js')?>"></script>
    <script>   
      var BASEPATH = "<?php echo base_url(); ?>";
    </script>

   

  </head>
 
  <body>
 
    <div class="navbar navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container-fluid">
          <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a>
          <a class="brand" href="<?php echo base_url('index.php/welcome')?>">LSM</a>
          <div class="nav-collapse">
            <ul class="nav">
              <?php $baseaddy=base_url();
                  $baseaddy = str_replace('lsm','racktables',$baseaddy);
              ?>
              <li><a href="<?php echo $baseaddy;?>">racktables</a></li>
              <li><a href="<?php echo base_url('index.php/about')?>">about</a></li>
              <li><a href="">help</a></li>
              
            </ul>
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>
 

 

 

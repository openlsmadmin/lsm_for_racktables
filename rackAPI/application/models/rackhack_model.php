<?php     
/*
  Custom RackTables API.  At this point, this just augments the api.php file we 
  have in racktables. 
  ultimately, this API will replace the open source version. 
*/
 class rackhack_model extends CI_Model {
	      public function __construct()
	      {
		      $this->load->database('');
		      
	      }	      

	      public function get_switch_model_name($dictionaryID)
	      {	
		    $query = $this->db->get_where('Dictionary', array('dict_key'=>$dictionaryID));	
		    // returns something like 'ProCurve%GPASS%4000M'
		    
		    if ($query->num_rows() > 0)
		    {
			$row = $query->result_array();
			 $name =$row[0]['dict_value'];
		    } 
		    return $name;
	      }
	      

	      /**
          *	Method Name:  get_switches
          * Method Description:  Returns any object that has been defined as a "Network Switch" - objtype_id 8 OR as of racktables version 19.10, 
          *                       objtype_id 1503 for network chassis types.
          * Output:  Array
	      **/
	      public function get_all_switches()

	      {

              $sql = "SELECT RackObject.name, RackObject.id, Dictionary.dict_value
                      FROM RackObject
                      INNER JOIN Dictionary ON objtype_id = Dictionary.dict_key
                      WHERE objtype_id IN (8, 1503)";

              $query = $this->db->query($sql);  

      				if ($query->num_rows() > 0)  
      				{
                //echo $query->num_rows();
      					return ($query->result_array());
      				}
      				else 
      				{
      					return false;
      				}

        }

        /**
          * Method Name:  get_waps
          * Method Description:  Returns any object that has been defined as a Wireless Access Point - objtype_id 965 
          *                       
          * Output:  Array
        **/
	      public function get_all_waps()

	      {
                  /*               
                  SELECT RackObject.name, Dictionary.dict_value
                  FROM`RackObject`
                  INNER JOIN Dictionary ON objtype_id = Dictionary.dict_key
                  WHERE`objtype_id`=8
                  LIMIT 0 , 30     
                  */
                  $this->load->database('racktables');
                  $this->db->select('*');
		  		  $this->db->from('RackObject');
                  $this->db->join('Dictionary', 'Dictionary.dict_key=RackObject.objtype_id');
                  $this->db->where('objtype_id',965);
                  $query = $this->db->get();

  				if ($query->num_rows() > 0)  
  				{
  					return ($query->result_array());
  				}
  				else 
  				{
  					return false;
  				}

            }

	      public function get_all_routers()

	      {
                  /*               
                  SELECT RackObject.name, Dictionary.dict_value
                  FROM`RackObject`
                  INNER JOIN Dictionary ON objtype_id = Dictionary.dict_key
                  WHERE`objtype_id`=8
                  LIMIT 0 , 30     
                  */
	              $this->load->database('racktables');
	              $this->db->select('*');
		  		  $this->db->from('RackObject');
	              $this->db->join('Dictionary', 'Dictionary.dict_key=RackObject.objtype_id');
	              $this->db->where('objtype_id',7);
	              $query = $this->db->get();
  				if ($query->num_rows() > 0)  
  				{
  					return ($query->result_array());
  				}
  				else 
  				{
  					return false;
  				}

            }


          /**
          *	Method Name:  get_locations
          * Method Description:  Returns 3 levels of location information
          * Output:
          *
			*Array ( 
          	*		[0] => Array ( [L1ID] => 2 [L1Location] => CAN [L2ID] => 7 [L2Location] => A2 ServerRm [L3ID] => 10 [L3Location] => NorthWest Corner ) 
			*		[1] => Array ( [L1ID] => 2 [L1Location] => CAN [L2ID] => 8 [L2Location] => A1 Basement [L3ID] => [L3Location] => ) 
			*		[2] => Array ( [L1ID] => 3 [L1Location] => BRK [L2ID] => 9 [L2Location] => BRK Room1 [L3ID] => [L3Location] => ) 
			*		[3] => Array ( [L1ID] => 4 [L1Location] => WKL [L2ID] => [L2Location] => [L3ID] => [L3Location] => ) 
			*	   ) 
          **/
          public function get_locations()
          {
      				/*$sql = "SELECT L1ID, L1Location, L2ID, L2Location, tL3.id L3ID, tL3.tag L3Location FROM
      						(SELECT L1.id L1ID, L1.tag L1Location, tL2.id L2ID, tL2.tag L2Location FROM
      						(SELECT * FROM TagTree tL1
      						WHERE tL1.parent_id=(SELECT id FROM TagTree WHERE upper( tag ) = 'LOCATION' LIMIT 1)) L1
      						LEFT JOIN TagTree tL2 ON tL2.parent_id = L1.id) L2
      						LEFT JOIN TagTree tL3 ON tL3.parent_id = L2.L2ID"; */
              $sql ="SELECT L1ID, L1Location, Concat(L1ID, '.' , L2ID) as L2FullID, L2Location, Concat(Concat(L1ID, '.' , L2ID),'.',tL3.id) as L3FullID, tL3.tag L3Location FROM
                  (SELECT L1.id L1ID, L1.tag L1Location, tL2.id L2ID, tL2.tag L2Location FROM
                  (SELECT * FROM TagTree tL1
                  WHERE tL1.parent_id=(SELECT id FROM TagTree WHERE upper( tag ) = 'LOCATION' LIMIT 1)) L1
                  LEFT JOIN TagTree tL2 ON tL2.parent_id = L1.id) L2
                  LEFT JOIN TagTree tL3 ON tL3.parent_id = L2.L2ID
                  ORDER BY L1Location, L2Location";

      				 $query = $this->db->query($sql);	
        				if ($query->num_rows() > 0)  
        				{
        					return ($query->result_array());
        				}
        				else 
        				{
        					return false;
        				}

          }

          /**
          *	Method Name:  get_switches_by_location
          * Method Description:  Returns array of switches based on location tag.
          * Inputs:  Can assume that you will always have $branch_id.
          *          0 = not set. aka.  don't need to search by this criteria.
          *          9999 = all
          * Output:
          *
          			*Array
          			*(
          			*    [0] => Array
          			*        (
          			*            [tag_id] => 7
          			*            [id] => 416
          			*            [name] => CAESA2-JLEE-1
          			*            [label] => newlabelvalue
          			*            [asset_no] => this
          			*            [objtype_id] => 8
          			*        )
          			*
          			*)
          **/
	    public function get_switches_by_location($branch_id, $building_id, $room_id)
	    {
//echo '<br>'.'branch:'.$branch_id .'<br>'.'building:'.$building_id.'<br>'.'room:'. $room_id.'<BR>';
                if ($room_id > 0)
                {     
                      //echo ' u want rooms<BR>';
                      //ROOM SEARCH

                        $sql="
                              SELECT 
                              (SELECT tag FROM TagTree WHERE id=".$branch_id.") as BranchName, ".$branch_id." as BranchID, NULL as BranchSwitchName, NULL as BranchSwitchID,
                              (SELECT tag FROM TagTree WHERE id=".$building_id.") as BuildingName, ".$building_id." as BuildingID, NULL as BuildingSwitchName, NULL as BuildingSwitchID,
                              TT.tag RoomName, TT.id RoomID, RO.name as RoomSwitchName, RO.id as RoomSwitchID

                              FROM RackObject RO
                              INNER JOIN TagStorage TS ON TS.entity_realm = 'object' AND TS.entity_id = RO.id
                              INNER JOIN TagTree TT ON TT.id = TS.tag_id
                              WHERE RO.objtype_id IN ( 8, 1503 )
                              AND TT.id=".$room_id.
                              " AND TT.parent_id 
                              IN
                              (SELECT ID
                              FROM TagTree 
                              WHERE parent_id=".$branch_id.")";

                } 
                elseif ($building_id > 0) 
                {
                        $sql ="SELECT * FROM
                              (SELECT 
                              (SELECT tag FROM TagTree WHERE id=".$branch_id. ") as BranchName, TT.parent_id as BranchID, NULL as BranchSwitchName, NULL as BranchSwitchID,
                              TT.tag BuildingName, TT.id BuildingID, RO.name as BuildingSwitchName, RO.id as BuildingSwitchID, 
                              NULL as RoomName, NULL as RoomID, NULL as RoomSwitchName, NULL as RoomSwitchID
                              FROM RackObject RO
                              INNER JOIN TagStorage TS ON TS.entity_realm = 'object' AND TS.entity_id = RO.id
                              INNER JOIN TagTree TT ON TT.id = TS.tag_id
                              WHERE RO.objtype_id IN ( 8, 1503 )
                              AND TT.parent_id=".$branch_id. "

                              UNION

                              SELECT 
                              (SELECT tag FROM TagTree WHERE id=".$branch_id. ") as BranchName, ".$branch_id. " as BranchID, NULL as BranchSwitchName, NULL as BranchSwitchID,
                              (SELECT tag FROM TagTree WHERE id=".$building_id.") as BuildingName, ".$building_id." as BuildingID, NULL as BuildingSwitchName, NULL as BuildingSwitchID,
                              TT.tag RoomName, TT.id RoomID, RO.name as RoomSwitchName, RO.id as RoomSwitchID

                              FROM RackObject RO
                              INNER JOIN TagStorage TS ON TS.entity_realm = 'object' AND TS.entity_id = RO.id
                              INNER JOIN TagTree TT ON TT.id = TS.tag_id
                              WHERE RO.objtype_id IN ( 8, 1503 )
                              AND TT.parent_id = ".$building_id. ") Full
                              WHERE Full.BuildingID = ".$building_id;

                }
                else
                {
                      //echo ' u want branch<BR>';
                      //BRANCH SEARCH = anything that has a parent id of branchID 
                      $sql="SELECT * FROM
                            (SELECT 
                            TT.tag BranchName, TT.id BranchID, RO.name as BranchSwitchName, RO.id as BranchSwitchID, 
                            NULL as BuildingName, NULL as BuildingID, NULL as BuildingSwitchName, NULL as BuildingSwitchID,
                            NULL as RoomName, NULL as RoomID, NULL as RoomSwitchName, NULL as RoomSwitchID
                            FROM RackObject RO
                            INNER JOIN TagStorage TS ON TS.entity_realm = 'object' AND TS.entity_id = RO.id
                            INNER JOIN TagTree TT ON TT.id = TS.tag_id
                            WHERE RO.objtype_id IN ( 8, 1503 )
                            AND TT.id=".$branch_id."

                            UNION

                            SELECT 
                              (SELECT tag FROM TagTree WHERE id=".$branch_id. ") as BranchName, TT.parent_id as BranchID, NULL as BranchSwitchName, NULL as BranchSwitchID,
                              TT.tag BuildingName, TT.id BuildingID, RO.name as BuildingSwitchName, RO.id as BuildingSwitchID, 
                              NULL as RoomName, NULL as RoomID, NULL as RoomSwitchName, NULL as RoomSwitchID
                              FROM RackObject RO
                              INNER JOIN TagStorage TS ON TS.entity_realm = 'object' AND TS.entity_id = RO.id
                              INNER JOIN TagTree TT ON TT.id = TS.tag_id
                              WHERE RO.objtype_id IN ( 8, 1503 )
                              AND TT.parent_id=".$branch_id. "



                            UNION

                            SELECT
                            (SELECT tag FROM TagTree WHERE id=".$branch_id.") as BranchName, ".$branch_id." as BranchID, NULL as BranchSwitchName, NULL as BranchSwitchID,
                            TTBuilding.tag BuildingName, TTBuilding.id BuildingID, NULL as BuildingSwitchName, NULL as BuildingSwitchID,
                            TT.tag RoomName, TT.id RoomID, RO.name as RoomSwitchName, RO.id as RoomSwitchID

                            FROM RackObject RO
                            INNER JOIN TagStorage TS ON TS.entity_realm = 'object' AND TS.entity_id = RO.id
                            INNER JOIN TagTree TT ON TT.id = TS.tag_id
                            INNER JOIN TagTree TTBuilding ON TTBuilding.id=TT.parent_id
                            WHERE RO.objtype_id IN ( 8, 1503 )
                            AND TTBuilding.parent_id =".$branch_id.") Full
                            WHERE Full.BranchID=".$branch_id."
                             ORDER BY Full.BranchName, BuildingName, RoomName";             
                } 

               $query = $this->db->query($sql); 
                if ($query->num_rows() > 0)  
                {
                 //print_r($query->result_array());
                //exit;
                  return ($query->result_array());
                }
                else 
                {
                  return false;
                }

      }      


          /**
          *	Method Name:  get_waps_by_location
          * Method Description:  Returns array of switches based on location tag
          * Output:
          **Array
			*(
			*    [0] => Array
			*        (
			*            [tag_id] => 8
			*            [id] => 419
			*            [name] => test123
			*            [label] => 
			*            [asset_no] => 
			*            [objtype_id] => 965
			*        )
			*
			*)
			**/
	    public function get_waps_by_location($tag_id)
	    {

				$sql="SELECT tag_id, RackObject.id, RackObject.name, RackObject.label, RackObject.asset_no, RackObject.objtype_id
						FROM RackObject
						LEFT JOIN TagStorage ON entity_realm = 'object'
						AND entity_id = RackObject.id
						WHERE tag_id =".$tag_id.
						" AND RackObject.objtype_id=965";		
				$query = $this->db->query($sql);	
				print_r($query->result_array());		
  				if ($query->num_rows() > 0)  
  				{
  					return ($query->result_array());
  				}
  				else 
  				{
  					return false;
  				}

        }      


	    public function get_switch_details($object_id)
	    {
				$object_details = array();
				$object_details['main']=NULL;
				$object_details['ports']=NULL;
				$object_details['hardwaredetails']=NULL;
        $object_details['IPV4'] = NULL;
        $object_details['FQDN'] = NULL;
        
				//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
				 //get name, label, assetno, objtype_id, comments, container_name
				$object_details_sql = "SELECT tag_id, RackObject.id, RackObject.name, RackObject.label, RackObject.asset_no, RackObject.objtype_id, 
                (SELECT rack_id FROM RackSpace WHERE object_id = RackObject.id ORDER BY rack_id ASC LIMIT 1) as rack_id, 
                (SELECT parent_entity_id AS rack_id FROM EntityLink WHERE child_entity_type='object' AND child_entity_id = RackObject.id AND parent_entity_type = 'rack' ORDER BY rack_id ASC LIMIT 1) as rack_id_2, 
                (select name from Rack where id = rack_id) as Rack_name, 
                (select row_id from Rack where id = rack_id) as row_id, 
                (select name from Row where id = row_id) as Row_name, 
                (SELECT parent_entity_id FROM EntityLink WHERE child_entity_type='object' AND child_entity_id = RackObject.id AND parent_entity_type = 'object' ORDER BY parent_entity_id ASC LIMIT 1) as container_id, 
                (SELECT name FROM RackObject WHERE id = container_id) as container_name, 
                RackObject.has_problems, RackObject.comment, 
                (SELECT COUNT(*) FROM Port WHERE object_id = RackObject.id) as nports, 
                (SELECT 1 FROM VLANSwitch WHERE object_id = id LIMIT 1) as runs8021Q 
                FROM RackObject 
                LEFT JOIN TagStorage on entity_realm = 'object' 
                AND entity_id = RackObject.id 
                WHERE RackObject.id ='".$object_id."
                ' ORDER BY tag_id";

				$query = $this->db->query($object_details_sql);	
        //echo "<BR>object sql is:".$object_details_sql .'<br>';				
				$object_details['main'] = $query->result_array();
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
				//set SQL retrieving hardware model name from attribute table.			
				$hardware_sql = "SELECT object_id, object_tid as objectTypeID, attr_id, chapter_id, dict_key, dict_value hardwaremodel FROM 
                            AttributeValue 
                            LEFT JOIN Dictionary On dict_key=uint_value
                            WHERE object_id=".$object_id. " and attr_id=2";

				//execute SQL
        $query = $this->db->query($hardware_sql);	

        //save data          
        $object_details['hardwaredetails']=$query->result_array();

        if ($query->num_rows() > 0)
        {
          $object_details['hardwaredetails'][0]['hardwaremodel']= str_replace("%GPASS%"," ",$object_details['hardwaredetails'][0]['hardwaremodel']);
        }
        				
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
				//get port list				
				$port_list_sql = "SELECT p.id, p.name, p.label, p.l2address, p.reservation_comment, lnk.porta, lnk.portb, (SELECT dict_value FROM Dictionary WHERE dict_key =p.type) AS oif_name,
                          (SELECT name FROM RackObject 
                          WHERE p.object_id = RackObject.id) AS object_name, 
                          p.reservation_comment, lnk.porta, lnk.portb, rA.name PortAName, rB.name PortBName
                          FROM Port p
                          LEFT JOIN Link lnk ON lnk.porta = id OR lnk.portb =id
                          LEFT JOIN Port pA ON pA.id=lnk.porta
                          LEFT JOIN Port pB ON pB.id=lnk.portb
                          LEFT JOIN RackObject rA ON rA.id = pA.object_id
                          LEFT JOIN RackObject rB ON rB.id = pB.object_id

                          WHERE p.object_id =".$object_id. " ORDER BY p.id ASC";

			    $query = $this->db->query($port_list_sql);  
//header ('Content-Type: application/json; charset=UTF-8');          
 //echo "<BR>port list sql is:".$port_list_sql .'<br>';
 //print_r($query->result_array());
          $object_details['ports']=$query->result_array();
        
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        //get IPV4 information

          $IPV4_sql ="SELECT name AS osif, type, inet_ntoa(ip) AS IP4Address FROM IPv4Allocation WHERE object_id = ".$object_id;
          $query = $this->db->query($IPV4_sql);  
          $object_details['IPV4']=$query->result_array();    

        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        //get FQDN Name - we will connect to switch using this fully qualified name instead of Common Name or IP addresses.

          $FQDN_sql ="SELECT A.object_id, A.object_tid, A.attr_id, A.string_value, A.uint_value, A.float_value
                      FROM AttributeValue A, Attribute B
                      WHERE A.object_id =".$object_id. " AND A.attr_id =3 LIMIT 1";

          $query = $this->db->query($FQDN_sql);  

          $object_details['FQDN']=$query->result_array();   
       ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


         return $object_details;

	     }
			
    //If port label has SW or RTR in it, lock it down.
    public function get_locked_ports($object_id)
    {
        //get port list       
        $port_list_sql = "SELECT p.id, p.name, p.label, p.l2address, p.reservation_comment, lnk.porta, lnk.portb, (SELECT dict_value FROM Dictionary WHERE dict_key =p.type) AS oif_name,
                          (SELECT name FROM RackObject 
                          WHERE p.object_id = RackObject.id) AS object_name, 
                          p.reservation_comment, lnk.porta, lnk.portb, rA.name PortAName, rB.name PortBName
                          FROM Port p
                          LEFT JOIN Link lnk ON lnk.porta = id OR lnk.portb =id
                          LEFT JOIN Port pA ON pA.id=lnk.porta
                          LEFT JOIN Port pB ON pB.id=lnk.portb
                          LEFT JOIN RackObject rA ON rA.id = pA.object_id
                          LEFT JOIN RackObject rB ON rB.id = pB.object_id

                          WHERE p.object_id =".$object_id. " ORDER BY p.id ASC";

        $query = $this->db->query($port_list_sql);  
        $portlist = $query->result_array();

        $portsToLock = array();

        foreach ($portlist as $port)
          {
            //echo "label value is: ". $port['label'];
            if (  (isset($port['label'])) and ((strlen($port['porta']) > 0) || (strlen($port['portb']) > 0))  )
            {
              //build array
              array_push($portsToLock, $port['name']);
            }
          
          }
         return $portsToLock;
    }
}

?>
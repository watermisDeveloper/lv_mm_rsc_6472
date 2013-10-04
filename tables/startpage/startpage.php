<?php
/**
 * Table DelegateClass
 * The Startpage Delegate Class handels the Startpage. The slots defined in 
 * startpage.html are filled with user role specific content
 * 
 * @version 1.0
 * @author Mirko Maelicke <mirko@maelicke-online.de>
 */
class tables_startpage {
    /**
     * set Permissions for startpage due to user role
     * if nobody logged in, grand read only
     * 
     * @version 1.0
     * @author Mirko Maelicke <mirko@maelicke-online.de>
     */
    function getPermissions(){
        $auth =& Dataface_AuthenticationTool::getInstance();
        $user =& $auth->getLoggedInUser();
        if (isset($user)){ 
            return Dataface_PermissionsTool::getRolePermissions($user->val('Role'));
        }
        else {return Dataface_PermissionsTool::READ_ONLY(); }

    }
    
    /** 
     * Get the Title from startpage table and pass to startpage_title slot
     * in smarty template startpage.html
     * 
     * @version 1.0
     * @author Mirko Maelicke <mirko@maelicke-online.de>
     */
    function block__startpage_title(){
        $title = df_get_record('startpage', array('element'=>'title'));
        echo $title->htmlValue('content');
    }
    
    /** 
     * According to the user role, a edit form link for the startpage 
     * will be included to the startpage_edit slot on smarty template 
     * startpage.html
     * 
     * @version 1.1
     * @author Mirko Maelicke <mirko@maelicke-online.de>
     */
    function block__startpage_edit(){
        $auth =& Dataface_AuthenticationTool::getInstance();
        $app = Dataface_Application::getInstance();
        $user =& $auth->getLoggedInUser();
        
        if(isset($user)){
            if($user->val('Role') == "admin_data" || $user->val('Role') == "admin_system"){
                echo '<div id="start_editing">Edit this Page</div>';
                echo '<div id="edit_area">';
                echo '<ul><li><a href="'.$app->url('-table=startpage&-action=edit&element==title').'">Edit the title</a></li>';
                echo '<li><a href="'.$app->url('-table=startpage&-action=edit&element==introduction').'">Edit the Introduction</a></li>';
//                echo '<li><a href="'.$app->url('-table=startpage&-action=new&element=anouncement').'">Add a new anouncement</a></li>';
                echo '<li><a href="'.$app->url('-table=startpage&-action=edit&element==geoserver').'">edit Geoserver URL</a></li>';
                echo '<li><a href="'.$app->url('-table=mis_upl_history&-action=list').'">View upload history</a></li>';
                echo '</ul><div>';
            }
        }
    }
    
    /** 
     * Include the content of introduction element in startpage table into 
     * startpage_intro slot of  smarty template startpage.html
     * 
     * @version 1.0
     * @author Mirko Maelicke <mirko@maelicke-online.de>
     */ 
    function block__startpage_intro(){
        $introduction = df_get_record('startpage', array('element'=>'introduction'));
        echo $introduction->htmlValue('content');
    }
    
    /** Include the content of user role specific quicklink elements of 
     * startpage table These are quicklinks to table views acording to user role
     * 
     * @version 1.1
     * @deprecated since version 1.1
     * @author Mirko Maelicke <mirko@maelicke-online.de>
     */
    function DEP_block__startpage_quicklink(){
        $auth =& Dataface_AuthenticationTool::getInstance();
        $app = Dataface_Application::getInstance();
        $user =& $auth->getLoggedInUser();
        
        echo "<h1>Quicklink Bar</h1>";
        echo "<ul><li title='show all Stations'>";
        echo "<a href='".$app->url("-table=stations&-action=list")."' id='quicklink_stations'>";
        echo "<img  src='images/stations.png' alt='Stations' width='100px' height='100px' /></a>";
        echo "</li>";
        
        if(isset($user)){
            if ($user->val('Role') == 'udi_data' || $user->val('Role') == 'admin_data' ||
                    $user->val('Role') == 'admin_system' ){
                echo "<li title='show all level 1 catchments'>";
                echo "<a href='".$app->url("-table=nb1&-action=list")."' id='quicklink_nb1'>";
                echo "<img  src='images/nb1.png' alt='Catchment' width='100px' height='100px' /></a>";
                echo "</li>";
            }
            if ($user->val('Role') == 'admin_data' || $user->val('Role') == 'admin_system'){
                echo "<li title='User management'>";
                echo "<a href='".$app->url("-table=mis_users&-action=list")."' id='quicklink_nb1'>";
                echo "<img  src='images/users.png' alt='Users' width='100px' height='100px' /></a>";
                echo "</li>";
                echo "<li title='WaterMIS synchronization'>";
                echo "<a href='".$app->url('-action=startSync')."' id='quicklink_sync'>";
                echo "<img src='images/refresh.png' alt='Sync' width='100px' height='100px' /></a>";
                echo "</li>";
            }
        }
        echo "</ul>";
    }
    
    /** Include a g OpenLayers map into the startpage_mapping
     *  Block of smarty template startpage.html 
     * 
     * @version 1.2
     * @author Mirko Maelicke <mirko@maelicke-online.de>
     */
    function block__startpage_mapping(){
        $nb1s = mysql_query("select id_nb1, description from nb1 order by description ASC",df_db());
        $rivers = mysql_query("select river from rivers order by river ASC", df_db());
        $districts = mysql_query("select id_district, description from admin_district order by description ASC", df_db());
        
        echo "<!-- Map area -->";
        echo "<div style='margin-left:auto; margin-right:auto;'>";
        echo "<div style='width:600px; height:450px;' id='map'></div>";
        
        /* Control Panel */
        echo "<!-- Control Panel -->";
        echo "<div style='width:600px; height: 50px' id='control_panel'>";
        //filter for timeseries
        echo "<input type='checkbox' id='cbFilterTs' onchange='check_cbFilterTs();' />";
        echo "<label id='cb_label' for='cbFilterTs'>hide stations without timeseries</label>";
        echo "<div id='openAdvFilter' style='display: inline-block; margin-left: 2.5em; color: blue; cursor: pointer;'>"
            ."open advanced Filter...</div>";
        echo "<div style='display:inline-block; float:right; color: blue; cursor:pointer; font-size: 170%; font-weight:bolder; margin-left: 5px;'"
            ." id='enlarge_map'>+</div>";
        echo "<div style='display:inline-block; float:right; color: blue; cursor:pointer; font-size: 170%; font-weight:bolder;  margin-left: 5px;'"
            ." id='decrease_map'>-</div></div>";
        echo "<div id='advFilter' style='width: 600px; height:100px; display: none ;'>";
        require_once 'advancedFilter.php';
        echo "</div></div>";
}
    
    /** 
     * Include all anouncment elements from startpage table and insert them 
     * into the anouncment unsorted list tag on smarty template startpage.html
     * 
     * @version 1.1
     * @deprecated since version 1.1
     * @author Mirko Maelicke <mirko@maelicke-online.de>
     */
    function DEP_block__startpage_anouncment(){
        $auth =& Dataface_AuthenticationTool::getInstance();
         $app = Dataface_Application::getInstance();
         $user =& $auth->getLoggedInUser();
         
         $anouncments = df_get_records_array('startpage',array('element'=>'anouncement'));
         echo '<h1>Anouncements</h1>';
         echo '<ul class="bxslider">';
         
         foreach ($anouncments as $anouncment){
             echo '<li><p>'.$anouncment->val('content').'</p>';
             if(isset($user) && ($user->val('Role') == 'admin_system' || $user->val('Role') == 'admin_data')){
                 echo "<p stype='float:left;'>
                     <a href='".$app->url('-action=delete_anouncement&id='.$anouncment->val('id'))."'>
                     Delete this anouncement</a></p>";
             }
             echo "</li>";
         }
         echo '</ul>';
    }
    
    /** 
     * produces two JSON Objects containing the result of passed MySQL Query
     * JSON Objects contain all stations for the startpage map included in 
     * startpage_map slot on smarty template startpage.html
     * 
     * @version 1.0
     * @author Mirko Maelicke <mirko@maelicke-online.de>
     */
    function block__JSON_parse(){
        /*Load all Stations of type Hydro and parse to JSON*/
        /* Refer to the wiki to get the Query explanation */
        $hydro_query = "SELECT DISTINCT name_station, longitude, latitude, 'yes' AS timeseries, id_station, id_nb1, 
            river, id_district, type_station AS idlayer FROM stations
            WHERE EXISTS (SELECT * FROM timeseries WHERE timeseries.id_station = stations.id_station
            AND timeseries.type_station = stations.type_station) AND type_station = 'H'
            UNION SELECT DISTINCT name_station, longitude, latitude, 'no' AS timeseries, id_station, id_nb1, 
            river, id_district, type_station AS idlayer  FROM stations
            WHERE NOT EXISTS (SELECT * FROM timeseries WHERE timeseries.id_station = stations.id_station 
            AND timeseries.type_station = stations.type_station) AND type_station = 'H'";
        //$hydrostations = mysql_query("Select name_station, longitude, latitude from stations where type_station = 'H'", df_db());
        $hydrostations = mysql_query($hydro_query, df_db());
        
        /* Parse to JSON*/
        echo "<script type='text/javascript'>";
        echo "var Hydro_Stations_Array = [";
        while ($station = mysql_fetch_row($hydrostations)){
            if ($station[1] != "" && $station[2] != ""){
                echo "{'name': '".$station[0]."','lon':".
                    $station[1].",'lat':".$station[2].",'ts': '".$station[3]."','id':'".
                        $station[4]."','nb1':'".$station[5]."','riv':'".$station[6].
                        "','dis':'".$station[7]."','idlayer':'".$station[8]."'},";
            }
        }
        echo "];";
        echo "</script>";
        
        /*Load all boreholes and parse to JSON*/
        /* Refer to thwe wiki to get the Query explanation */
        $boreholes_query = "SELECT id_borehole, id2, id3, source, name, id_nb3, 
                            id_district, longitude, latitude, altitude, depth, 
                            deph_rock, stat_lvl, dyn_lvl, dia_drill, dia_pump, 
                            depth_wellscreen, flw_m3ph, beneficiaries, date_drill, 
                            perm_mps, pump, depth_pump, owner, remark, 'BH' AS idlayer
                             FROM boreholes";
        $boreholes = mysql_query($boreholes_query, df_db());
        
        /* Parse to JSON*/
        echo "<script type='text/javascript'>";
        echo "/*Hello from the map.php lavuun*/";
        echo "var Boreholes_Array = [";
        while ($borehole = mysql_fetch_row($boreholes)){
            if ($borehole[7] != "" && $borehole[8] != ""){
                echo "{'id_borehole': '".$borehole[0]."','lon':".
                    $borehole[7].",'lat':".$borehole[8].",'name': '".$borehole[4]."','id':'".
                        $borehole[0]."','depth':'".$borehole[10]."','source':'".$borehole[3].
                        "','altitude':'".$borehole[9]."','idlayer':'".$borehole[25]."'},";
            }
        }
        echo "];";
        echo "</script>";

        
        /*Load all Stations of type Hydro and parse to JSON*/
        /* Refer to thwe wiki to get the Query explanation */
        $meteo_query = "SELECT DISTINCT name_station, longitude, latitude, 'yes' AS timeseries, id_station, id_nb1,
            river, id_district, type_station AS idlayer  FROM stations
            WHERE EXISTS (SELECT * FROM timeseries WHERE timeseries.id_station = stations.id_station
            AND timeseries.type_station = stations.type_station) AND type_station = 'M'
            UNION SELECT DISTINCT name_station, longitude, latitude, 'no' AS timeseries, id_station, id_nb1,
            river, id_district, type_station AS idlayer  FROM stations
            WHERE NOT EXISTS (SELECT * FROM timeseries WHERE timeseries.id_station = stations.id_station 
            AND timeseries.type_station = stations.type_station) AND type_station = 'M'";
        //$meteostations = mysql_query("Select name_station, longitude, latitude from stations where type_station = 'M'", df_db());
        $meteostations = mysql_query($meteo_query, df_db());
        
        /* Parse to JSON*/
        echo "<script type='text/javascript'>";
        echo "var Meteo_Stations_Array = [";
        while ($station = mysql_fetch_row($meteostations)){
            if ($station[1] != "" && $station[2] != ""){
                echo "{'name': '".$station[0]."','lon':".
                    $station[1].",'lat':".$station[2].",'ts': '".$station[3]."','id':'".
                        $station[4]."','nb1':'".$station[5]."','riv':'".$station[6].
                        "','dis':'".$station[7]."','idlayer':'".$station[8]."'},";
            }
        }
        echo "];";
        echo "</script>";
    }
    
    /** 
     * includes a select list into startpage_rivers slot on smarty template 
     * startpage.html.
     * selectlist is populated with all River entries from rivers table and
     * valued with a redirect to the stations table view filtered by selected 
     * rivername. 
     * 
     * @version 1.1
     * @deprecated since version 1.1
     * @author Mirko Maelicke <mirko@maelicke-online.de>
     */
    function DEP_block__startpage_rivers(){
        //get all rivers 
        $rivers = mysql_query("select * from rivers", df_db());
        
        echo "<h1>Select by River</h1><br><br>";
        echo "<select onchange='window.location.href=this.options[this.selectedIndex].value'>";
        echo "<option>Select River ...</option>";
        while ($river = mysql_fetch_row($rivers)){
            echo "<option value='index.php?-table=stations&river=".$river[0]."'>".$river[0]."</option>";
        }
        echo "</select>";
        echo "<br>";
    }
    
    /** 
     * includes a date and time of last synchronization to the startpage_sync_info
     * slot on smarty template startpage.html. The date is stored in startpage
     * table
     * 
     * @version 1.0
     * @author Mirko Maelicke <mirko@maelicke-online.de>
     */
    function block__startpage_sync_info(){
        $info = df_get_record('startpage', array('element'=>'sync'));
        $time = new DateTime;
        $time->setTimestamp((int)$info->val('content'));
        
        echo "Last synchronized: ".$time->format('D, j. M Y g:i a');
    }
    
    /**
     * get the geoserver URL adress from starpage table in mysql database and 
     * parse it to Javascript into var GEOSERVER to make it manageble via MI
     * and reachable via js/openlayers
     * 
     * @version 1.0
     * @author Mirko Maelicke <mirko@maelicke-online.de>
     */
    function block__geoserver(){
        /* get URL from database */
        $app = Dataface_Application::getInstance();        
        $geoserver = df_get_record('startpage', array('element'=>'geoserver'));
        
        /* parse to js/openlayers */
        echo "<script type='text/javascript'>";
        echo "GEOSERVER = '".$geoserver->val('content')."';";
        echo "</script>";
    }
}

?>

<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of map
 *
 * @author mirko_000
 */
class actions_map {
    /** handles the action like collecting user specific page content
     * and redirects to WaterMIS startpage
     */

    function handle($params){
        df_display(array(),'map.html');
}

    /** 
     * produces JSON Objects containing the result of passed MySQL Query
     * JSON Objects contain all stations for the startpage map included in 
     * startpage_map slot on smarty template startpage.html
     * 
     * @version 1.0
     * @author Lavuun Verstraete <verstraete@sher.be>
     */
    function block__JSON_parse(){
        /*Load all boreholes and parse to JSON*/
        /* Refer to thwe wiki to get the Query explanation */
        $boreholes_query = "SELECT id_borehole, id2, id3, source, name, id_nb3, 
                            id_district, longitude, latitude, altitude, depth, 
                            deph_rock, stat_lvl, dyn_lvl, dia_drill, dia_pump, 
                            depth_wellscreen, flw_m3ph, beneficiaries, date_drill, 
                            perm_mps, pump, depth_pump, owner, remark
                             FROM boreholes";
        $boreholes = mysql_query($boreholes_query, df_db());
        
        /* Parse to JSON*/
        echo "<script type='text/javascript'>";
        echo "/*Hello from the map.php lavuun*/";
        echo "var Boreholes_Array = [";
        while ($borehole = mysql_fetch_row($boreholes)){
            if ($borehole[1] != ""){
                echo "{'id_borehole': '".$borehole[0]."','lon':".
                    $borehole[7].",'lat':".$borehole[8].",'name': '".$borehole[4]."','id':'".
                        $borehole[1]."','depth':'".$borehole[10]."','source':'".$borehole[3].
                        "','altitude':'".$borehole[9]."'},";
            }
        }
        echo "];";
        echo "</script>";
}
}
?>

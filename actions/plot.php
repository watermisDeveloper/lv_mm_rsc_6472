<?php
/**
 * Dataface action class definition
 * plot action provides all necessar data for the WaterMIS plotter. Depending on
 * the user role, station owner and data aggregation type, the access may be 
 * denied. va_data user can only plot data of aggregation type M or A, as well 
 * as their own data. Higher permission roles can plot any data.
 * 
 * @version 1.1
 * @author Mirko Maelicke <mirko@maelicke-online.de>
 */
class actions_plot {
    function handle($params){
        $app =& Dataface_Application::getInstance();
        $record =& $app->getRecord();
        $auth =& Dataface_AuthenticationTool::getInstance();
        $user =& $auth->getLoggedInUser();
        $plot = FALSE;
        $hierachy = array();
        //create a hierachy list
        $query = df_query('select role_name, hierachy from mis_users_role_hierachy');
        while ($row = mysql_fetch_row($query)){$hierachy[$row[0]]= $row[1]; }
        
        /* get the aggregation type */
        $agg = $record->strval('sensor');
        $agg = $agg{0};
        
        /* get the owner of this station 
         * caution: this script is run from the sensor ($record) not the station!
         */
        $station = df_get_record('stations', array('type_station'=>$record->val('type_station'),
                'id_station'=>$record->val('id_station')));
        $owner_userid = $station->val('owner_userid');
        
        
        if (!isset($user)){
            if ($agg == 'M' || $agg == 'A'){
                $plot = TRUE;
            }
        }
        elseif ($user->val('Role') == 'va_data') {
            if ($owner_userid == $user->val('userid')){
                $plot = TRUE;
            }
            elseif ($agg == 'M' || $agg == 'A'){
                $plot = TRUE;
            }
        }
        elseif ($hierachy[$user->val('Role')] >= $hierachy['v_data']){
            $plot = TRUE;
        }
        
        if($plot){
            $query = "SELECT mydate,myvalue FROM timeseries WHERE type_station = '".$record->val('type_station').
                    "' and id_station = ".$record->val('id_station')." and sensor = '".
                    $record->val('sensor')."' and type_timeseries = '".$record->val('type_timeseries').
                    "' ORDER BY mydate ASC";
            $res = mysql_query($query ,df_db());
            
            while ($row = mysql_fetch_row($res)){
                
                if (($date = DateTime::createFromFormat('Y-m-d H:i:s', $row[0])) !== FALSE){
                    $data[] = array($date->getTimestamp(),(float)$row[1]);
                }
            }

            if(isset($data)){
                require_once 'graph.php';
            }
            else {
                $message = '<meta http-equiv="refresh" content="0; URL=index.php?-table=stations&-action=browse&type_station='.
                    $record->val('type_station').'&id_station='.$record->val('id_station').'&sensor='.$record->val('sensor')
                    .'&type_timeseries='.$record->val('type_timeseries').'&--msg=No timeseries data related to this sensor'.
                    ' and/or the date format is corrupt.';
                if (isset($user) &&($user->val('Role') == 'udi_data' || $user->val('Role') == 'admin_data'
                        || $user->val('Role') == 'admin_system')){
                    $message .= '   Check the data in the database executing SQL statement: \"'.query.'\"';
                } 
                $message .= '">';
                die($message);
                
            }
        }
        else {
            //redirect to startpage giving a message
            $owner = df_get_record('mis_users',array('userid'=>$owner_userid));
            echo '<meta http-equiv="refresh" content="0; URL=index.php?-table=help&help_page=data_owner&--msg=You+don\'t+have+'.
                'permission+to+view+detailed+data.The+requested+data+belongs+to+user+'.$owner->val('username').'">';
//            echo '<pre>';
//            var_dump($owner);
//            echo '</pre>';
        }
    }
}

?>

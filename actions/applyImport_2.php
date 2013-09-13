<?php
/**
 * Dataface action class definition
 * applyImport action is called from import form on import.php. A Post call is
 * required containing the upload file in csv format. The file is checked for 
 * having 8 lines, as required by the database. After importing them into 
 * temporary timeseries_copy table this is compared to timeseries and the number
 * of hydro and meteo record INSERTs and UPDATEs are determined. The data is 
 * transferred to destination table using a INSERT ON DUPLICATE KEY UPDATE 
 * SQL statement.
 * 
 * @todo upload only csv files and deny access for all other files
 * @version 1.0
 * @author Mirko Maelicke <mirko@maelicke-online.de>
 */
class actions_applyImport_2 {
    function handle($params){
        $auth =& Dataface_AuthenticationTool::getInstance();
        $user =& $auth->getLoggedInUser();
        if (!isset($user)){
            die('<meta http-equiv="refresh" content="0; URL=index.php?--msg=Please+log+in+to+apply+synchonization.">');
        }

        if (isset($_POST['temp_file_name'])){
            $i = 0;
            if (($csv = fopen($_POST['temp_file_name'], 'r')) !== FALSE){
                /* start a timer */
                $starttime = new DateTime();
                $starttime = $starttime->getTimestamp();
                //delete the temp. table
                mysql_query("delete from timeseries_copy ",df_db());
                
                //insert all records to temporaty table timeseries_copy
                while(($row = fgetcsv($csv,0 ,$_POST['delimeter'])) !== FALSE ){
                    if (count($row) == 8){
                        $query = "insert into timeseries_copy (type_station, id_station, sensor, 
                                type_timeseries, mydate, myvalue, origin, quality,create_userid,create_date) values".
                            "('$row[0]',$row[1],'$row[2]','$row[3]','$row[4]',$row[5],'$row[6]','$row[7]','{$user->val('username')}',$starttime)";
                        mysql_query($query, df_db());
                        $i++;
                    }
                }
                fclose($csv);
                /* delete the upload file again */
                unlink($_POST['temp_file_name']);
                
                /* get the number of updates */
                $res = mysql_query("SELECT timeseries_copy.Type_Station, Count(timeseries_copy.Id_Station) AS records_tscopy_count_all,".
                        " Count(timeseries.Id_Station) AS records_tscopy_count_update FROM timeseries RIGHT JOIN timeseries_copy ".
                        "ON (timeseries.mydate = timeseries_copy.mydate) AND (timeseries.type_timeseries = timeseries_copy.type_timeseries) ".
                        "AND (timeseries.sensor = timeseries_copy.sensor) AND (timeseries.Id_Station = timeseries_copy.Id_Station) AND ".
                        "(timeseries.Type_Station = timeseries_copy.Type_Station) GROUP BY timeseries_copy.Type_Station;", df_db());
                /*get the insert_update information*/
                $hydro_total = 0; $hydro_update = 0; $meteo_total = 0; $meteo_update = 0;
                while(($row = mysql_fetch_row($res)) !== FALSE){
                    if ($row[0] == "H"){
                        $hydro_total = (int)$row[1];
                        $hydro_update = (int)$row[2];
                    }
                    elseif ($row[0] == "M"){
                        $meteo_total = (int)$row[1];
                        $meteo_update = (int)$row[2];
                    }
                }
                
                /* do the import on database level */
                $query = "INSERT INTO timeseries (type_station, id_station, sensor, type_timeseries, mydate, myvalue, origin, quality, create_userid, create_date) 
                    SELECT type_station, id_station, sensor, type_timeseries, mydate, myvalue, origin, quality, create_userid, create_date 
                    FROM timeseries_copy c
                    ON DUPLICATE KEY UPDATE myvalue=c.myvalue, origin=c.origin, quality=c.quality, create_userid=c.create_userid, create_date=c.create_date";
                mysql_query($query, df_db());
                
                /* Create History Entry */
                $date = date_format(new DateTime(), "Y-m-d H:i:s");
                mysql_query('insert into mis_upl_history (user_id, mydate, file_name, file_size, total_records, hydro_insert, hydro_update,
                    meteo_insert, meteo_update) values ('.$_POST['userid'].',"'.$date.'","'.
                        $_POST['temp_file_name'].'",'.$_POST['filesize'].','.($hydro_total + $meteo_total).','.($hydro_total - $hydro_update).
                        ','.$hydro_update.','.($meteo_total - $meteo_update).','.$meteo_update.')',df_db());
                
                $endtime = new DateTime();
                $endtime = $endtime->getTimestamp();
                $runtime = round((($endtime - $starttime) / 60),2);        //in minutes
                echo '<meta http-equiv="refresh" content="5; URL=index.php?-table=mis_upl_history&-action=viewUploadHistory&mydate='.$date.'&'.
                    '--msg=The raw importing runtime took '.$runtime.' minutes">';
            }
            else {
                die('<meta http-equiv="refresh" content="0; URL=index.php?--msg=The+file+was+not+'.
                        'passed+correctly,+please+check+the+file+format">');
            }
        }                       
        else {
            die('<meta http-equiv="refresh" content="0; URL=index.php?--msg=Data+transfer+failed+for+unknown+reason!">');
        }
    }
}

?>

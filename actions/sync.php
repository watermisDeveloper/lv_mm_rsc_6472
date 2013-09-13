<?php
/**
 * Dataface action class definition
 * sync action is callable for any admin user Role. Will manage the synchronization
 * process and update the sync message on startpage
 * 
 * @todo Insert a wait page or busy page to signalize work in progress to the user
 * @see run_sync
 * @version 1.0
 * @author Mirko Maelicke <mirko@maelicke-online.de>
 */
class actions_sync {
    function handle($params){
         $auth =& Dataface_AuthenticationTool::getInstance();
        $user =& $auth->getLoggedInUser();
        
        if (isset($user)){
            if($user->val('Role') == 'admin_data' || $user->val('Role') == 'admin_system'){
                /* run the synchronization process*/
                $this->run_sync();
                
                /* renew sync information */
                $time = new DateTime();
                $query = "update startpage set content='".$time->getTimestamp()."'  where element='sync'";
                mysql_query($query, df_db());
            }
        }
    }
    
    /** 
     * run_sync function
     * checks for each required file in temp_sync folder if it exists. 
     * calls the specific import function for each sync table
     * 
     * @see sync_stations
     * @see sync_sensors
     * @see sync_rivers
     * @see sync_discharge_measrmnts
     * @see sync_rating_dates
     * @see sync_rating_hk
     * @see sync_rating_hq
     * @version 1.1
     * @author Mirko Maelicke <mirko@maelicke-online.de>
     */
    function run_sync(){
       /* sync the rivers */
       if (file_exists('tmp_sync/rivers.csv')){
           if (($i = $this->sync_rivers('tmp_sync/rivers.csv')) > 0){
               echo "<p>$i rivers were synchronized</p>"; 
           } 
           else { echo  "<p>rivers.csv has a corrupt format.</p>"; }
           unlink('tmp_sync/rivers.csv');
       } 
       else { echo  "<p>rivers.csv was not found.</p>"; }
        
       /* sync the stations */
       if (file_exists('tmp_sync/stations.csv')){
           if (count($err = $this->check_stations('tmp_sync/stations.csv')) > 0 ){
               rmSyncDir();
               die('<meta http-equiv="refresh" content="0; URL=index.php?-action=postSync">');
           }
           if (($i = $this->sync_stations('tmp_sync/stations.csv')) > 0){
               echo  "<p>$i stations were synchronized</p>";
           } 
           else { echo  "<p>stations.csv has a corrupt format.</p>"; }
           unlink('tmp_sync/stations.csv');
       } 
       else { echo  "<p>stations.csv was not found.</p>"; }
       
       /* sync the sensors */
       if(file_exists('tmp_sync/sensors.csv')){
           if(($i = $this->sync_sensors('tmp_sync/sensors.csv')) > 0){
               echo  "<p>$i sensors were synchronized</p>";
           } 
           else { echo  "<p>sensors.csv has a corrupt format.</p>"; }
           unlink ('tmp_sync/sensors.csv');
       } 
       else { echo  "<p>sensors.csv was not found.</p>"; }
       
       /* sync the discharge measurements */
       if(file_exists('tmp_sync/discharge_measrmnts.csv')){
           if(($i = $this->sync_discharge_measrmnts('tmp_sync/discharge_measrmnts.csv')) > 0){
               echo  "<p>$i discharge measurements were synchronized</p>";
           } 
           else { echo  "<p>discharge_measrmnts.csv has a corrupt format.</p>"; }
           unlink('tmp_sync/discharge_measrmnts.csv');
       } 
       else { echo  "<p>discharge_measrmnts.csv was not found.</p>"; }

       /* sync the rating dates */
       if(file_exists('tmp_sync/rating_dates.csv')){
           if(($i = $this->sync_rating_dates('tmp_sync/rating_dates.csv')) > 0){
               echo  "<p>$i rating dates were synchronized</p>";
           } 
           else { echo  "<p>rating_dates.csv has a corrupt format.</p>"; }
           unlink('tmp_sync/rating_dates.csv');
       } 
       else { echo  "<p>rating_dates.csv was not found.</p>"; }

       if(file_exists('tmp_sync/rating_hk.csv')){
           if(($i = $this->sync_rating_hk('tmp_sync/rating_hk.csv')) > 0){
               echo  "<p>$i rating H-K were synchronized</p>";
           } 
           else { echo  "<p>rating_hk.csv has a corrupt format.</p>"; }
           unlink('tmp_sync/rating_hk.csv');
       } 
       else { echo  "<p>rating_hk.csv was not found.</p>"; }

       if(file_exists('tmp_sync/rating_hq.csv')){
           if(($i = $this->sync_rating_hq('tmp_sync/rating_hq.csv')) > 0){
               echo  "<p>$i rating H-Q were synchronized</p>";
           } 
           else { echo  "<p>rating_hq.csv has a corrupt format.</p>"; }
           unlink('tmp_sync/rating_hq.csv');
       } 
       else { echo  "<p>rating_hq.csv was not found.</p>"; }

       /* remove the synchrnization folder again */
       rmdir('tmp_sync/');
    }

    /** 
     * check_stations function
     * opens $filePath in tmp_sync folder and uploads content to stations4sync
     * table using a INSERT SQL query. Lines are checked for having 20 columns, 
     * as required by the database. the view stations4sync_fk_conflicts presents 
     * all stations of not known foreign keys. These are pushed to the user and 
     * will cause the script to die.
     * 
     * @return array $err all error messages
     * @param string $filePath path to the file to upload
     * @version 1.0
     * @author Mirko Maelicke <mirko@maelicke-online.de>
     */
    function check_stations($filePath){
        if (($stations = fopen($filePath, 'r')) !== FALSE){
            $err = array();
            mysql_query("Delete from stations4sync", df_db());
            while (($row = fgetcsv($stations,0,',')) !== FALSE){
                if (count($row) == 20){
                    $query = "insert into stations4sync ( `type_station` ,`id_station` ,`id_secondaire` ,`id_tertiaire` ,
                        `type_meteo` ,`name_station` ,`country` ,`id_province` ,`id_district` ,`id_nb1` ,`id_nb2` ,
                        `id_nb3` ,`river` ,`latitude` ,`longitude` ,`altitude` ,`sortme` ,`owner_userid` ,`commentary` ,
                        `id_sector`) values ('".$row[0]."','".$row[1]."','".$row[2]."','".$row[3]."','".$row[4]."','"
                        .$row[5]."','".$row[6]."','".$row[7]."','".$row[8]."','".$row[9]."','".$row[10]."','"
                        .$row[11]."','".$row[12]."','".$row[13]."','".$row[14]."','".$row[15]."','".$row[16]."','"
                        .$row[17]."','".$row[18]."','".$row[19]."')";
                    mysql_query($query, df_db());
                }
            }
            $err_res = mysql_query("Select * from stations4sync_fk_conflicts", df_db());
            while ($error = mysql_fetch_row($err_res)){
                $err[] = $error;
            }
            return $err;
        }
    }
    
    /** 
     * sync_stations function
     * opens $filePath in tmp_sync folder and uploads content to stations
     * table using a INSERT ON DUPLICATE KEY UPDATE SQL query. Lines are checked
     * for having 20 columns, as required by the database.
     * 
     * @return int number of inserted or updated rows
     * @param String $filePath path to the file to upload
     * @version 1.0
     * @author Mirko Maelicke <mirko@maelicke-online.de>
     */
    function sync_stations($filePath){
        if (($stations = fopen($filePath, 'r')) !== FALSE){
            $i = 0;
            while (($row = fgetcsv($stations, 0, ',')) !== FALSE){
                if (count($row) == 20){
                    $query = "insert into stations ( `type_station` ,`id_station` ,`id_secondaire` ,`id_tertiaire` ,
                        `type_meteo` ,`name_station` ,`country` ,`id_province` ,`id_district` ,`id_nb1` ,`id_nb2` ,
                        `id_nb3` ,`river` ,`latitude` ,`longitude` ,`altitude` ,`sortme` ,`owner_userid` ,`commentary` ,
                        `id_sector`) values ('".$row[0]."','".$row[1]."','".$row[2]."','".$row[3]."','".$row[4]."','"
                        .$row[5]."','".$row[6]."','".$row[7]."','".$row[8]."','".$row[9]."','".$row[10]."','"
                        .$row[11]."','".$row[12]."','".$row[13]."','".$row[14]."','".$row[15]."','".$row[16]."','"
                        .$row[17]."','".$row[18]."','".$row[19]."')
                        on duplicate key update id_secondaire=values(id_secondaire), id_tertiaire=values(id_tertiaire),
                        type_meteo=values(type_meteo), name_station=values(name_station), country=values(country), 
                        id_province=values(id_province),id_district=values(id_district),id_nb1=values(id_nb1),id_nb2=values(id_nb2),
                        id_nb3=values(id_nb3), river=values(river), latitude=values(latitude), longitude=values(longitude),
                        altitude=values(altitude), sortme=values(sortme), owner_userid=values(owner_userid), 
                        commentary=values(commentary), id_sector=values(id_sector)";
                mysql_query($query, df_db());
                $i++;
                }
            }
            return $i;
        }
        fclose($stations);
    }
    
    /** 
     * sync_sensors function
     * opens $filePath in tmp_sync folder and uploads content to sensors
     * table using a INSERT ON DUPLICATE KEY UPDATE SQL query. Lines are checked
     * for having 18 columns, as required by the database.
     * 
     * @return int number of inserted or updated rows
     * @param String $filePath path to the file to upload
     * @version 1.0
     * @author Mirko Maelicke <mirko@maelicke-online.de>
     */
    function sync_sensors($filePath){
        if (($sensors = fopen($filePath, 'r')) !== FALSE){
            $i = 0;
            while (($row = fgetcsv($sensors, 0, ',')) !== FALSE){
                if (count($row) == 18){
                    $query = "insert into sensors (  `type_station` ,`id_station` ,`sensor` ,`type_timeseries` ,
                        `sensortype` ,`description` ,`commentary` ,`code_datalogger` ,`values_calculated` ,
                        `acquisition_auto` ,`operationnal` ,`agregation` ,`time_difference` ,`mini` ,`maxi` ,
                        `gradient_maxi` ,`sensorprecision` ,`sensordecimals`) values 
                        ('".$row[0]."','".$row[1]."','".$row[2]."','".$row[3]."','".$row[4]."','"
                        .$row[5]."','".$row[6]."','".$row[7]."','".$row[8]."','".$row[9]."','".$row[10]."','"
                        .$row[11]."','".$row[12]."','".$row[13]."','".$row[14]."','".$row[15]."','".$row[16]."','"
                        .$row[17]."') 
                        on duplicate key update sensortype=values(sensortype), description=values(description),
                        commentary=values(commentary), code_datalogger=(code_datalogger), values_calculated=values(values_calculated), 
                        acquisition_auto=(acquisition_auto), operationnal=values(operationnal), agregation=values(agregation), 
                        time_difference=values(time_difference), mini=values(mini), maxi=values(maxi), gradient_maxi=values(gradient_maxi), 
                        sensorprecision=values(sensorprecision), sensordecimals=values(sensordecimals)";
                mysql_query($query, df_db());
                $i++;
                }
            }
            return $i;
        }
        fclose($sensors);
    }
    
    /** 
     * sync_rivers function
     * opens $filePath in tmp_sync folder and uploads content to rivers
     * table using a INSERT ON DUPLICATE KEY UPDATE SQL query. Lines are checked
     * for having 3 columns, as required by the database.
     * 
     * @return int number of inserted or updated rows
     * @param String $filePath path to the file to upload
     * @version 1.0
     * @author Mirko Maelicke <mirko@maelicke-online.de>
     */
    function sync_rivers($filePath){
        if (($rivers = fopen($filePath, 'r')) !== FALSE){
            $i = 0;
            while (($row = fgetcsv($rivers,0, ',')) !== FALSE){
                if (count($row) == 3){
                    $query = "insert into rivers (`river` ,`description` ,`riversort`) values 
                        ('".$row[0]."','".$row[1]."','".$row[2]."') 
                        on duplicate key update description=values(description), riversort=values(riversort)";
                    mysql_query($query, df_db());
                    $i++;
                }
            }
            return $i;
        }
        fclose($rivers);
    }
    
    /** 
     * sync_discharge_measrmnts function
     * opens $filePath in tmp_sync folder and uploads content to 
     * discharge_measrmnts table using a INSERT ON DUPLICATE KEY UPDATE SQL 
     * query. Lines are checked for having 17 columns, as required by the database.
     * 
     * @return int number of inserted or updated rows
     * @param String $filePath path to the file to upload
     * @version 1.0
     * @author Mirko Maelicke <mirko@maelicke-online.de>
     */
    function sync_discharge_measrmnts($filePath){
        if (($discharge = fopen($filePath, 'r')) !== FALSE){
            $i = 0;
            while(($row = fgetcsv($discharge, 0, ',')) !== FALSE){
                if (count($row) == 17){
                    $query = "insert into discharge_measrmnts (   `type_station` ,`id_station` ,`sensor` ,
                        `type_timeseries` ,`mydate` ,`h` ,`q` ,`date_start` ,`date_end` ,`h_start` ,`h_end` ,
                        `h_mini` ,`h_maxi` ,`commentary` ,`author_dischargemeasrmnt` ,`author_processing` ,
                        `enabled` ) values 
                        ('".$row[0]."','".$row[1]."','".$row[2]."','".$row[3]."','".$row[4]."','"
                        .$row[5]."','".$row[6]."','".$row[7]."','".$row[8]."','".$row[9]."','".$row[10]."','"
                        .$row[11]."','".$row[12]."','".$row[13]."','".$row[14]."','".$row[15]."','".$row[16]."') 
                        on duplicate key update h=values(h), q=values(q), date_start=values(date_start), 
                        date_end=values(date_end), h_start=values(h_start), h_end=values(h_end), h_mini=values(h_mini), 
                        h_maxi=values(h_maxi), commentary=values(commentary), author_dischargemeasrmnt=values(author_dischargemeasrmnt), 
                        author_processing=values(author_processing), enabled=values(enabled)";
                    mysql_query($query, df_db());
                    $i++;
                }
            }
            return $i;
        }
        fclose($discharge);
    }
    
    /** 
     * sync_rating_dates function
     * opens $filePath in tmp_sync folder and uploads content to rating_dates
     * table using a INSERT ON DUPLICATE KEY UPDATE SQL query. Lines are checked
     * for having 12 columns, as required by the database.
     * 
     * @return int number of inserted or updated rows
     * @param String $filePath path to the file to upload
     * @version 1.0
     * @author Mirko Maelicke <mirko@maelicke-online.de>
     */
    function sync_rating_dates($filePath){
        if (($rating = fopen($filePath, 'r')) !== FALSE){
            $i = 0;
            while (($row = fgetcsv($rating, 0, ',')) !== FALSE){
                if (count($row) == 12){
                    $query = "insert into rating_dates (    `type_station` ,`id_station` ,`sensor` ,`type_timeseries` ,
                        `sensor_output` ,`date_validity` ,`date_rating` ,`author_rating` ,`commentary` ,`duree_gradient` ,
                        `sensortype` ,`method_conversion`) values 
                        ('".$row[0]."','".$row[1]."','".$row[2]."','".$row[3]."','".$row[4]."','"
                        .$row[5]."','".$row[6]."','".$row[7]."','".$row[8]."','".$row[9]."','".$row[10]."','"
                        .$row[11]."') 
                        on duplicate key update date_rating=values(date_rating), author_rating=values(author_rating),
                        commentary=values(commentary), duree_gradient=values(duree_gradient), sensortype=values(sensortype), 
                        method_conversion=values(method_conversion)";
                mysql_query($query, df_db());
                $i++;
                }
            }
            return $i;
        }
        fclose($rating);
    }

    /** 
     * sync_rating_hk function
     * opens $filePath in tmp_sync folder and uploads content to rating_hk
     * table using a INSERT ON DUPLICATE KEY UPDATE SQL query. Lines are checked
     * for having 8 columns, as required by the database.
     * 
     * @return int number of inserted or updated rows
     * @param String $filePath path to the file to upload
     * @version 1.0
     * @author Mirko Maelicke <mirko@maelicke-online.de>
     */
    function sync_rating_hk($filePath){
        if (($hk = fopen($filePath, 'r')) !== FALSE){
            $i = 0;
            while (($row = fgetcsv($hk, 0, ',')) !== FALSE){
                if (count($row) == 8){
                    $query = "insert into rating_hk (`type_station` ,`id_station` ,`sensor` ,`type_timeseries` ,
                        `sensor_output` ,`date_validity` ,`h` ,`k`) values 
                        ('".$row[0]."','".$row[1]."','".$row[2]."','".$row[3]."','".$row[4]."','"
                        .$row[5]."','".$row[6]."','".$row[7]."') 
                        on duplicate key update k=values(k)";
                mysql_query($query, df_db());
                $i++;
                }
            }
            return $i;
        }
        fclose($hk);
    }

    /** 
     * sync_rating_hq function
     * opens $filePath in tmp_sync folder and uploads content to rating_hq
     * table using a INSERT ON DUPLICATE KEY UPDATE SQL query. Lines are checked
     * for having 8 columns, as required by the database.
     * 
     * @return int number of inserted or updated rows
     * @param String $filePath path to the file to upload
     * @version 1.0
     * @author Mirko Maelicke <mirko@maelicke-online.de>
     */
    function sync_rating_hq($filePath){
        if (($hq = fopen($filePath, 'r')) !== FALSE){
            $i = 0;
            while (($row = fgetcsv($hq, 0, ',')) !== FALSE){
                if (count($row) == 8){
                    $query = "insert into rating_hq (`type_station` ,`id_station` ,`sensor` ,`type_timeseries` ,
                        `sensor_output` ,`date_validity` ,`h` ,`q`) values 
                        ('".$row[0]."','".$row[1]."','".$row[2]."','".$row[3]."','".$row[4]."','"
                        .$row[5]."','".$row[6]."','".$row[7]."') 
                        on duplicate key update q=values(q)";
                mysql_query($query, df_db());
                $i++;
                }
            }
            return $i;
        }
        fclose($hq);
    }
    
    
    /** 
     * rmSyncDir function
     * secure removal of all tmp_sync dir content and dir removal afterwads
     * 
     * @deprecated since version 1.0
     * @version 1.0
     * @author Mirko Maelicke <mirko@maelicke-online.de>
     */   
    function rmSyncDir(){
        if(file_exists('tmp_sync/rivers.csv')){
            unlink('tmp_sync/rivers.csv');
        }
        if(file_exists('tmp_sync/stations.csv')){
            unlink('tmp_sync/stations.csv');
        }
        if(file_exists('tmp_sync/sensors.csv')){
            unlink('tmp_sync/sensors.csv');
        }
        if(file_exists('tmp_sync/discharge_measrmnts.csv')){
            unlink('tmp_sync/discharge_measrmnts.csv');
        }
        if(file_exists('tmp_sync/rating_dates.csv')){
            unlink('tmp_sync/rating_dates.csv');
        }
        rmdir('tmp_sync/');
    }
}

?>

<?php
/**
 * Dataface action class definition
 * applyImport action is called from import form on import.php. A Post call is
 * required containing the upload file in csv format. The file is checked for 
 * having 8 lines, as required by the database. After importing them using a 
 * INSERT ON DUPLICATE KEY UPDATE SQL query a new entry in mis_upl_history table 
 * is created. 
 * 
 * @todo upload only csv files and deny access for all other files
 * @version 1.0
 * @deprecated since 1.0S
 * @author Mirko Maelicke <mirko@maelicke-online.de>
 */
class actions_applyImport {
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
                while(($row = fgetcsv($csv,0 ,$_POST['delimeter'])) !== FALSE ){
                    if (count($row) == 8){
                        $query = "insert into timeseries (type_station, id_station, sensor, 
                                type_timeseries, mydate, myvalue, origin, quality,create_userid,create_date) values".
                            "('$row[0]',$row[1],'$row[2]','$row[3]','$row[4]',$row[5],'$row[6]','$row[7]','{$user->val('username')}',$starttime)
                             on duplicate key update myvalue=values(myvalue),origin=values(origin), quality=values(quality), 
                             create_userid=values(create_userid), create_date=values(create_date)";
                        mysql_query($query, df_db());
                        $i++;
                    }
                }
                fclose($csv);
                /* delete the upload file again */
                unlink($_POST['temp_file_name']);
                
                /* Create History Entry */
                mysql_query('insert into mis_upl_history (user_id, mydate, file_name, file_size, num_records) 
                    values ('.$_POST['userid'].',"'.date_format(new DateTime(), "Y-m-d H:i:s").'","'.
                        $_POST['temp_file_name'].'",'.$_POST['filesize'].','.$i.')',df_db());
                
                $endtime = new DateTime();
                $endtime = $endtime->getTimestamp();
                $runtime = ($endtime - $starttime) / 60;        //in minutes
                echo '<meta http-equiv="refresh" content="0; URL=index.php?--msg=recieved+file:+'.
                        $_POST["temp_file_name"].'+containing+'.$i.'+entries.+Raw+import+running'.
                        '+for+'.$runtime.'+minutes.">';
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

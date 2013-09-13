<?php
/**
 * Table DelegateClass
 * Xataface Delegate Class declaration for Xataface 2.0alpha application
 * WaterMIS. Creates a human readable view of upload history entries
 * 
 * @version 1.0
 * @author Mirko Maelicke <mirko@maelicke-online.de>
 */
class tables_mis_upl_history {
    /**
     * Insert the content of mydate field on mis_upl_history into title_date 
     * block on uploadRecord.html template
     * 
     * @version 1.0
     * @author Mirko Maelicke <mirko@maelicke-online.de>
     */
    function block__title_date(){
        $app =& Dataface_Application::getInstance();
        $record =& $app->getRecord();
        
        echo $record->htmlValue('mydate');
    }

    /**
     * Insert the content of username field on mis_users with id of user_id 
     * field onmis_upl_history into upl_user block on uploadRecord.html template
     * 
     * @version 1.0
     * @author Mirko Maelicke <mirko@maelicke-online.de>
     */
    function block__upl_user(){
        $app =& Dataface_Application::getInstance();
        $record =& $app->getRecord();
        
        $user = df_get_record('mis_users', array("userid"=>$record->val('user_id')));
        
        echo "<a href='index.php?-table=mis_users&-action=browse&userid={$user->val('userid')}'>
            {$user->htmlValue('username')}</a>";
    }
    
    /**
     * Insert File information from mis_upl_history into upl_file_info block 
     * on uploadRecord.html template. Info contains file name, its size with 
     * correct file size suffix and the total number of imported records
     * 
     * @version 1.0
     * @author Mirko Maelicke <mirko@maelicke-online.de>
     */
    function block__upl_file_info(){
        $app =& Dataface_Application::getInstance();
        $record =& $app->getRecord();
        $i= 0;
        $suffix = array(0=>'byte',1=>'KB', 2=>'MB', 3=>'GB');
        $size = (int)$record->val('file_size');
        while ($size > 1024){
            $size /= 1024;
            $i++;
        }
        
        
        echo "<p>File: ".$record->val('file_name')." (".round($size,1)." $suffix[$i]), containing ".
                $record->val('total_records')." records.";
    }
    
    /**
     * Insert a data information table created from mis_upl_history table of 
     * given record into upl_data_info block of uploadRecord.html template.
     * 
     * @version 1.0
     * @author Mirko Maelicke <mirko@maelicke-online.de>
     */
    function block__upl_data_info(){
        $app =& Dataface_Application::getInstance();
        $record =& $app->getRecord();
        
        echo "<table style='border:1 px solid black;border-radius:10px;text-align:center;' width='50%'>";
        echo "<tr><th>Station Type</th><th>inserted Records</th><th>updated Records</th></tr>";
        echo "<tr><td>Meteo</td><td>{$record->val('meteo_insert')}</td>
            <td>{$record->val('meteo_update')}</td></tr>";
        echo "<tr><td>Hydro</td><td>{$record->val('hydro_insert')}</td>
            <td>{$record->val('hydro_update')}</td></tr>";
        echo "</table>";
    }
}

?>

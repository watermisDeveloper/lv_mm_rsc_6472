<?php
/**
 * Dataface action class definition
 * Opens and displays uploadRecord.html template
 * 
 * @version 1.0
 * @author Mirko Maelicke <mirko@maelicke-online.de>
 */
class actions_viewUploadHistory {
    function handle($params){
        df_display(array(), 'uploadRecord.html');
    }
}

?>

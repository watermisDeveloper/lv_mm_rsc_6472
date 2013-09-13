<?php
/**
 * Dataface action class definition
 * Opens and displays startSync.html template
 * 
 * @version 1.0
 * @author Mirko Maelicke <mirko@maelicke-online.de>
 */
class actions_startSync {
    function handle($params){
        df_display(array(),'startSync.html');
    }
}

?>

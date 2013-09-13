<?php

/**
 * Dataface action class definition
 * Opens and displays postSync.html template
 * 
 * @version 1.0
 * @author Mirko Maelicke <mirko@maelicke-online.de>
 */
class actions_postSync {
    function handle($params){
        df_display(array(),'postSync.html');
    }
}

?>

<?php
/**
 * Dataface action class definition
 * Opens and displays help.html template
 * 
 * @version 1.0
 * @author Mirko Maelicke <mirko@maelicke-online.de>
 */
class actions_help {
    function handle($params){
        df_display(array(), 'help.html');
    }
}

?>

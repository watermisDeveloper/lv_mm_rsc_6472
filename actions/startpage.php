<?php
/**
 * Dataface action class definition
 * This is an action performed by WaterMIS, as the user calls the "Start"
 * button in the navigation bar. Will build a user-role specific startpage 
 * 
 * @version 1.0
 * @author Mirko Maelicke <mirko@maelicke-online.de>
 */
class actions_startpage {
    /** handles the action like collecting user specific page content
     * and redirects to WaterMIS startpage
     */
    function handle($params){
        df_display(array(),'startpage.html');
}
}

?>

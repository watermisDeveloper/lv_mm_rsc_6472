<?php
/**
 * Dataface action class definition
 * Delete the anouncement set of startpage table of id passed as $_GET['id']
 * 
 * @todo calling the action through URL makes it possible to delete anouncements
 *          for everyone. Insert a check for admin user role.
 * @version 1.0
 * @author Mirko Maelicke <mirko@maelicke-online.de>
 */
class actions_delete_anouncement {
    function handle($params){
        $app = Dataface_Application::getInstance();
        $id = $_GET['id'];
        df_query('DELETE FROM startpage where id = '.$id, df_db());
        //df_display(array(),'startpage.html');
        
    }  
}

?>

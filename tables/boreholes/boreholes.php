<?php
/**
 * Table DelegateClass
 * Xataface Delegate Class declaration for Xataface 2.0alpha application
 * WaterMIS. Includes map, plotting and import section to each station record.
 * 
 * @version 1.0
 * @author Mirko Maelicke <mirko@maelicke-online.de>
 */
class tables_boreholes {
    /**
     * Returns permissions array.  This method is called every time an action on 
     * sensor table is performed to make sure that the user has permission to 
     * perform the action.
     * @param record A Dataface_Record object (may be null) against which we check
     *               permissions.
     * @see Dataface_PermissionsTool
     * @see Dataface_AuthenticationTool
     * @version 1.0
     * @author Mirko Maelicke <mirko@maelicke-online.de>
     */
     function getPermissions(&$record){
         $auth =& Dataface_AuthenticationTool::getInstance();
         $user =& $auth->getLoggedInUser();
         if ( !isset($user) ) return Dataface_PermissionsTool::READ_ONLY();
             // if the user is null then nobody is logged in... read only
         $role = $user->val('Role');
         return Dataface_PermissionsTool::getRolePermissions($role);
             // Returns all of the permissions for the user's current role.
    }
    
    /**
     * Create a new section for each station Record to include a OpenLayers
     * map object, showing the station.
     * 
     * @return array Dataface section content array
     * @version 1.0
     * @author Mirko Maelicke <mirko@maelicke-online.de>
     */
    function section__map(){
        return array(
            'content'=>"<div style='width:300px; height:200px' style='
                margin-left:auto; margin-right:auto;' id='map'></div>",
            'class'=>'main',
            'label'=>'Map',
            'order'=>'1'
        );
    }
   
    /**
     * includes necessary javascript files for map section
     * 
     * @version 1.0
     * @author Mirko Maelicke <mirko@maelicke-online.de>
     */
    function block__custom_javascripts(){
        $app =& Dataface_Application::getInstance();
        $record =& $app->getRecord();
        
        echo "\t\t<!-- jQuery library (served from Google) -->\n";
        echo "\t\t<script src='//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js'></script>\n";
        echo "\t\t<!-- load OpenLayers -->\n";
        echo "\t\t<script src='js/ol/OpenLayers.js'></script>\n";
        echo "<script type='text/javascript' src='js/boreholes.js'></script>\n";
        
        /*parse Station information to Javascript using JSON*/
        if(isset($record)){   
            echo "<script type='text/javascript'>";
            echo "var Borehole ={'name':'".$record->val('name')."','lon':'".
                    $record->val('longitude')."','lat':'".$record->val('latitude')."'};";
            echo "</script>";
        }
    }
}

?>

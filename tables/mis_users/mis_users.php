<?php
/**
 * Table DelegateClass
 * Xataface Delegate Class declaration for Xataface 2.0alpha application
 * WaterMIS. Set permissions for logged in user depending his user role on
 * sensor table 
 * 
 * @version 1.0
 * @author Mirko Maelicke <mirko@maelicke-online.de>
 */
class tables_mis_users {
    /** 
     * grants access to the registration form. As a non registered user has 
     * no access to the application, getPermissions has to grand it in the 
     * table Delegate Class in order to make registration possible
     * 
     * @version 1.0
     * @author Mirko Maelicke <mirko@maelicke-online.de>
     */
    function getPermissions(&$record){
        $auth =& Dataface_AuthenticationTool::getInstance();
        $user =& $auth->getLoggedInUser();
        
        //check user role
        if (isset($user)){
            if ($user->val('Role') == 'admin_system' || $user->val('Role') == 'admin_data'){
                return null;
            }
        }
        //grand permissions to enter registration form
        $perms['register'] = 1;
        return $perms;
    }
    
    /**
     * As any user or non registered user can access registration form, the 
     * access to the  Role field has to be limited. Otherwise the user could 
     * make himself a admin.
     * This field has to have a default value in the Databse!
     * 
     * @version 1.0
     * @author Mirko Maelicke <mirko@maelicke-online.de>
     */
    function Role__permissions($record){
        $auth =& Dataface_AuthenticationTool::getInstance();
        $user =& $auth->getLoggedInUser();
        
        //check user role
        if (isset($user)){
            if ($user->val('Role') == 'admin_system' || $user->val('Role') == 'admin_data'){
                return null;
            }
        }
        return Dataface_PermissionsTool::NO_ACCESS();
    }
}

?>

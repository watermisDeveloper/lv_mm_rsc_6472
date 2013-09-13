<?php
/**
 * Table DelegateClass
 * Xataface Delegate Class declaration for Xataface 2.0alpha application
 * WaterMIS. Includes a plotting form to each sensor record and grants 
 * permission to not logged in users.
 * 
 * @version 1.0
 * @author Mirko Maelicke <mirko@maelicke-online.de>
 */
class tables_sensors {
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
     * Create a new section for each sensor Object to include a link to a
     * custom action, creating a graph
     * 
     * @return array Dataface section content array
     * @version 1.0
     * @author Mirko Maelicke <mirko@maelicke-online.de>
     */
    function section__graph(){
        $app =& Dataface_Application::getInstance();
        return array(
            'content' => "<form action='{$app->url('-action=plot')}' method='post'>
               <input type='submit' value='Plot this Timeseries' /></form>",
            'class' => "main",
            'label' => "Timeseries Plotting options",
            'order' => "10"
        );
    }
}

?>

<?php

/**
 * Xataface ApplicationDelegate
 * ApplicationDelegate Class defines rules, configurations and actions 
 * linked to the entire Water MIS application. This file is part of Water MIS
 * application using Xataface 2.0alpha
 *
 * @author Mirko Maelicke <mirko@maelicke-online.de>
 * @version 1.0
 * 
 */
class conf_ApplicationDelegate {
    /**
     * Returns permissions array.  This method is called every time an action is 
     * performed to make sure that the user has permission to perform the action.
     * 
     * @param record A Dataface_Record object (may be null) against which we check
     *               permissions.
     * @see Dataface_PermissionsTool
     * @see Dataface_AuthenticationTool
     * @return Dataface Permission
     * @version 1.0
     * @autor Mirko Maelicke <mirko@maelicke-online.de>
     */
    function getPermissions(&$record){
        $auth =& Dataface_AuthenticationTool::getInstance();
        $user =& $auth->getLoggedInUser();
        $app = Dataface_Application::getInstance();
        $app_query =& $app->getQuery();
        
        if ( !isset($user) ) return Dataface_PermissionsTool::NO_ACCESS();
        $hierachy = array();
        //create a hierachy list
        $query = df_query('select role_name, hierachy from mis_users_role_hierachy');
        while ($row = mysql_fetch_row($query)){$hierachy[$row[0]]= $row[1]; }

        //get table hierachy
        $table_role = df_get_record('mis_tables_roles', array('table_name'=>$app_query['-table']));
        $user_role = $user->val('Role');

        //check maximum permissions
        if ($hierachy[$user_role] >= $hierachy[$table_role->val('role_name_maxprv')]) {
            return Dataface_PermissionsTool::getRolePermissions($user_role);
        }
        elseif ($hierachy[$user_role] >= $hierachy[$table_role->val('role_name_minprv')]) {
            return Dataface_PermissionsTool::READ_ONLY();
        }
        else return Dataface_PermissionsTool::NO_ACCESS();
    }

    
     /**
      * before rendering any page, create a extended navigation bar for the system
      * admin. Set default action for startpage, help page and import history page.
      * include custom css and javascript
      * 
      * @version 1.0
      * @author Mirko Maelicke <mirko@maelicke-online.de>
      */      
      function beforeHandleRequest(){
         $auth =& Dataface_AuthenticationTool::getInstance();
         $user =& $auth->getLoggedInUser();
          $app =& Dataface_Application::getInstance(); 
          $tables = df_get_records_array('mis_tables_roles',array());
          $i = 0;
          
          //Make the startpage action the default table action
          $query =& $app->getQuery();
          if ( $query['-table'] == 'startpage' and ($query['-action'] == 'browse' or $query['-action'] == 'list') ){
              $query['-action'] = 'startpage';
          }
          //make help action the default table action
          if ( $query['-table'] == 'help' and ($query['-action'] == 'browse' or $query['-action'] == 'list') ){
              $query['-action'] = 'help';
          }
          
          //make vieUploadHistory the default table action
          if ( $query['-table'] == 'mis_upl_history' and ($query['-action'] == 'browse'  or $query['-action'] == 'view')){
              $query['-action'] = 'viewUploadHistory';
          }

          
          if (isset($user)) {
              //loop through all tables
              foreach ($tables as $table){
                  if ($user->val('Role') == 'admin_system'){
                      //admin system will see all tables
                      $app->_conf['_tables'][$table->val('table_name')] = $table->val('label');
                      $app->_conf['_prefs']['horizontal_tables_menu'] = 0;
                  }                  
              }
          }

          //add custom stylesheets
          $app->addHeadContent('<link rel="stylesheet" type="text/css" href="themes/style.css"/>'); 
          //css for boxslider on startpage
          $app->addHeadContent('<link href="js/bxslider/jquery.bxslider.css" rel="stylesheet" />');
    }
    
    /**
     * Depending on the actual logged in user role and the called table, a 
     * proper sidebar is created. In order to make it easy and useable only links
     * should be shown, the user is allowed to use.
     * 
     * @version 1.1
     * @author Mirko Maelicke <mirko@maelicke-online.de>
     */
    function block__sidebar(){
        $auth =& Dataface_AuthenticationTool::getInstance();
        $app =& Dataface_Application::getInstance();
        $query =& $app->getQuery();
        $user =& $auth->getLoggedInUser();
        $table_role = df_get_record('mis_tables_roles', array('table_name'=>$query['-table']));
        
        $hierachy = array();
        //create a hierachy list
        $get_query = df_query('select role_name, hierachy from mis_users_role_hierachy');
        while ($row = mysql_fetch_row($get_query)){$hierachy[$row[0]]= $row[1]; }
        echo "<ul>";
        /* include at least back and home button */
        if (isset($_SESSION['backlink'])){
            echo "<li><a href='".$_SESSION['backlink']."'><img src='images/s_back.png' alt='back' />
                <span>back</span></a></li>";
        }
        echo "<li><a href='index.php?-table=startpage'><img src='images/s_home.png' alt='Home' /><span>Start
            </span></a></li>";
        echo "<li><a href='index.php?-table=stations'><img src='images/s_stations.png' alt='Stations' /><span>Stations
            </span></a></li>";
        echo "<li><a href='index.php?-table=boreholes'><img src='images/s_boreholes.png' alt='Boreholes'/><span>Boreholes
            </span></a></li>";
        if (isset($query['-table']) && $query['-table'] !== 'startpage' ){
            if (isset($user) && $hierachy[$user->val('Role')] >= $hierachy[$table_role->val('role_name_maxprv')]){
                /* include also an edit bar a.s.o */
                if ($query['-action'] !== 'startSync'){
                    echo "<li><a href='".$app->url('-action=new')."'><img src='images/s_new.png' alt='new' />
                        <span>new</span></a></li>";
                }
                if ($query['-action'] == 'view'){
                    echo "<li><a href='".$app->url('-action=edit')."'><img src='images/s_edit.png' alt='edit' />
                    <span>edit</span></a></li>";
                }
            }
            if (isset($user) && $hierachy[$user->val('Role')] >= $hierachy[$table_role->val('role_name_minprv')]){
                if ($query['-action'] == 'view'){
                    echo "<li><a href='index.php?-table=".$query['-table']."&-action=list'>
                        <img src='images/s_show.png' alt='show' /><span>all</span></a></li>";
                }
            }
         
        }
        if (isset($user) && ($user->val('Role') == 'admin_data' || $user->val('Role') == 'admin_system')){
            echo "<li><a href='".$app->url('-table=mis_users&-action=list')."'><img src='images/s_users.png' alt='users' />
                    <span>Users</span></a></li>";
            if ($query['-action'] !== 'startSync'){
                echo "<li><a href='".$app->url('-action=startSync')."'><img src='images/s_sync.png' alt='sync' />
                        <span>Sync</span></a></li>";
            }
        }
        
        /* help button for everyone */
        echo "<li><a href='index.php?-table=help'><img src='images/s_help.png' alt='back' />
            <span>Help</span></a></li>";
        echo "</ul>";
        
        /* save the actual backlink in the session */
        $_SESSION['backlink'] = ''.$app->url('');
    }
    
    /**
     * as the logged in user is an admin, include a Edit Message link on 
     * synchronization page to redirect to an editing form for the message, to 
     * be shown on sync&import page 
     * 
     * @version 1.0
     * @author Mirko Maelicke <mirko@maelicke-online.de>
     */
    function block__sync_message(){
        $auth =& Dataface_AuthenticationTool::getInstance();
        $user =& $auth->getLoggedInUser();
        $content = df_get_record('startpage', array('element'=>'sync_message'));
        
        try {
            echo $content->val('content');
            if (isset($user) && ($user->val('Role') == 'admin_system') || $user->val('Role') == 'admin_data'){
                echo "<br><a href='index.php?-table=startpage&-action=edit&element=sync_message' style='float:right;'>
                    Edit message</a><br>";
            }
            echo "<h1>  </h1>";

            
        } catch (Exception $exc) {
            echo 'No sync message found in database!';
        }
    }
    
    /**
     * as the logged in user is an admin, include a Import Message link on 
     * synchronization page to redirect to an editing form for the message, to 
     * be shown on sync&import page 
     * 
     * @version 1.1
     * @author Mirko Maelicke <mirko@maelicke-online.de>
     */
    function block__import_message(){
        $auth =& Dataface_AuthenticationTool::getInstance();
        $user =& $auth->getLoggedInUser();
        $content = df_get_record('startpage', array('element'=>'import_message'));
        
        if (isset($content)) {
            echo $content->val('content');
            if (isset($user) && ($user->val('Role') == 'admin_system') || $user->val('Role') == 'admin_data'){
                echo "<br><a href='index.php?-table=startpage&-action=edit&element=import_message' style='float:right;'>
                    Edit message</a><br>";
            }
            echo "<h1>  </h1>";

            
        } else {
            echo 'No import message found in database!';
        }
    }
    
    /**
     * as the logged in user is at leastan udi_data, include an import form 
     * containing user information, used in <import.php> 
     * 
     * @version 1.1
     * @author Mirko Maelicke <mirko@maelicke-online.de>
     */
    function block__import_form(){
        $app =& Dataface_Application::getInstance();
        $auth =& Dataface_AuthenticationTool::getInstance();
        $user =& $auth->getLoggedInUser();
        $query = df_query('select role_name, hierachy from mis_users_role_hierachy');
        while ($row = mysql_fetch_row($query)){$hierachy[$row[0]]= $row[1]; }

        if (isset($user) && $hierachy[$user->val('Role')] >= $hierachy['udi_data']){
            echo '<form action="import.php" method="post" enctype="multipart/form-data">'.
                    '<input type="file" name="file" /><br><span style="font-size: 150%;">Separation Char:   </span>'.
                    '<input type="text" id="deli" name="delimeter" value="," style="width: 20px" /><br>'.
                    '<input type="submit" value="Import" /><input type="hidden" name="user" value=\''.
                    $user->val('Role').'\' /></form>';
        }
    }


    
    /**
     * as the logged in user is an admin, create a Snyc error report from
     * the stations4sync_fk_conflicts view
     * 
     * @version 1.0
     * @author Mirko Maelicke <mirko@maelicke-online.de>
     */
    function block__postSync_message(){
        $auth =& Dataface_AuthenticationTool::getInstance();
        $user =& $auth->getLoggedInUser();
        $result = mysql_query("Select * from stations4sync_fk_conflicts",df_db());
        $err_messages = array();
        while(($row = mysql_fetch_row($result)) !== FALSE){
            $err_messages[] = $row;
        }
        
        
        if (isset($user) && ($user->val('Role') == 'admin_system') || $user->val('Role') == 'admin_data'){
            if (count($err_messages) > 0){
                echo "<h3>The follwoing stations contain not known foreign keys:</h3><ul>";
                foreach ($err_messages as $msg){
                    echo "<li><a href='javascript:alert(\"$msg[1]\");'>$msg[0]</a></li>";
                }
                echo "</ul>";
            }
            else { echo "<br><br>no foreign key errors occured during last synchronization<br><br>"; }
        }
    }
}


?>

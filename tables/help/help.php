<?php
/**
 * Table DelegateClass
 * prvides several methods to manage the WaterMIS Resources Module help page.
 * The correct help page is served by using block methods of Xataface front-end
 * on smarty template help.html
 * 
 * @version 1.0
 * @author Mirko Maelicke <mirko@maelicke-online.de>
 */
class tables_help {
    /**
     * replace help_content block on smarty template help.html. The help page to
     * show is requested by GET, if not set show help index. For logged in admins
     * a link to the page edit form is produced
     * 
     * @version 1.0
     * @author Mirko Maelicke <mirko@maelicke-online.de> 
     */
    function block__help_content(){
        $auth =& Dataface_AuthenticationTool::getInstance();
        $user =& $auth->getLoggedInUser();
        if(isset($_GET['help_page'])){
            $content = df_get_record('help', array('page'=>$_GET['help_page']));
            $page = $_GET['help_page'];
        }
        else {
            $content = df_get_record('help', array('page'=>'index'));
            $page = 'index';
        }
        
        echo $content->val('content');
        echo "<br><h1>  </h1><br>";
        if (isset($user) && ($user->val('Role') == 'admin_data' || $user->val('Role') == 'admin_system')){
            echo "<a href='index.php?-table=help&-action=edit&page=".$page."' style='float:right;'>Edit this Page</a>";
        }
    }
    
    /**
     * replace help_index block on smarty template help.html. The index is a 
     * unsorted list of all help pages represented by entries in help table.
     * For each entry a page is created, except index entry.
     * 
     * @version 1.0
     * @author Mirko Maelicke <mirko@maelicke-online.de> 
     */
    function block__help_index(){
        $entries = mysql_query("select * from help where page != 'index'", df_db());
        
        if(!isset($_GET['help_page']) || (isset($_GET['help_page']) && $_GET['help_page'] == 'index')){       
            echo " <h3>Index</h3><ul>";
            while (($row = mysql_fetch_row($entries)) !== FALSE){
                echo "<li><a href='index.php?-table=help&help_page=".$row[1]."'>".$row[2]."</a></li>";
            }
            echo "</ul>";
        }
    }
}

?>

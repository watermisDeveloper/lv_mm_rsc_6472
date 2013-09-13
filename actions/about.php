<?php
/**
 * Dataface action class definition
 * Opens and displays about.html template
 * 
 * @version 1.0
 * @author Mirko Maelicke <mirko@maelicke-online.de>
 */
class actions_about {
  function handle($params){
      df_display(array(),'about.html');
  }
}
?>
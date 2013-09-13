<!DOCTYPE html>
<?php
if (isset($_GET['discard'])){
    //remove_tmp_sync();
    secRmDir('tmp_sync/');
    die('<meta http-equiv="refresh" content="0; URL=index.php?-action=startSync">');
}

if (!isset($_FILES['zipfile'])){ die('The selected file could not be found or is damaged.'); }

/** Extracts a ZIP Archive to the temporary synchonization folder of WaterMIS, if
 * it's not existing jet. Otherwise the script dies.
 * 
 * @version 1.0
 * @author Mirko Maelicke <mirko@maelicke-online.de>
 */
function extractZIP($zipPath){
    if (@!mkdir('tmp_sync')){die('The temporary synchronization directory already exists.
        Please make sure there is no sychrinization process active.');}
    $zip = new ZipArchive();
    if ($zip->open($zipPath)){
        $zip->extractTo('tmp_sync/');
        $zip->close();
        return TRUE;
    }
    else { return FALSE; }
}

/**
 * Remove all sync files and sync dir from root directory
 * 
 * @version 1.0
 * @author Mirko Maelicke <mirko@maelicke-online.de>
 */
function remove_tmp_sync(){
    if (file_exists('tmp_sync/discharge_measrmnts.csv')){
        unlink('tmp_sync/discharge_measrmnts.csv');}
    if (file_exists('tmp_sync/rivers.csv')){
        unlink('tmp_sync/rivers.csv');}
    if (file_exists('tmp_sync/sensors.csv')){
        unlink('tmp_sync/sensors.csv');}
    if (file_exists('tmp_sync/stations.csv')){
        unlink('tmp_sync/stations.csv');}
    if (file_exists('tmp_sync/rating_dates.csv')){
        unlink('tmp_sync/rating_dates.csv');}
    rmdir('tmp_sync/');
}

/**
 * secRmDir function
 * secure removal of a complete folder and all of its content
 * 
 * @version 1.0
 * @author Mirko Maelicke <mirko@maelicke-online.de>
 */
function secRmDir($dirPath){
    $files = scandir($dirPath);
    $i = 0;
    foreach ($files as $file){
        if ($i > 1){
            unlink("".$dirPath.$file); 
        }
        $i++;
    }
    rmdir($dirPath);
}

?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Water MIS synchronizer</title>
        <style type="text/css">
            body {
                width: 100%;
                height:100%;
            }
            div#page {
                width: 1000px;
                height: 100%;
                margin-left: auto;
                margin-right: auto;
                border: 1px solid rgb(238,238,238);
                box-shadow: 0px 0px 2em rgb(153,153,153);
                text-align: center;
            }
            h1 {
                text-align: center;
                color: darkblue;
                font-size: 220%;
            }
            p {
                margin-left: 10%;
                margin-right: 10%;
            }
            form {
                width: 500px;
                margin-left: auto;
                margin-right: auto;
            }
            ul {
                font-size: 110%;
            }
            ul a {
                text-decoration: none;
                color: green
            }
            ul a:hover {
                color: lightgreen;
                font-weight: bold;
            }
        </style>
    </head>
    <body>
        <div id="page">
            <h1>Water MIS Synchronizer</h1>
            <h3>Files found:</h3>
            <ul>
        <?php
            if (extractZIP($_FILES['zipfile']['tmp_name'])){
                /* show all files in the zip */
                $i = 0;
                $sync_files = scandir('tmp_sync/');
                foreach ($sync_files as $file) {
                    if ($i > 1){
                        echo "<li><a href=\"javascript:alert('This feature is not supported jet');\">$file</a></li>";
                    }
                    $i++;
                }
         ?></ul><?php
                /* check for correct filenames */
                if (file_exists('tmp_sync/discharge_measrmnts.csv') && file_exists('tmp_sync/rivers.csv') && 
                        file_exists('tmp_sync/sensors.csv') && file_exists('tmp_sync/stations.csv') &&
                        file_exists('tmp_sync/rating_dates.csv')){
                    echo "<form action='index.php?-action=sync' method='post'>
                        <input type='submit' value='Synchronize now' />
                        </form>";
                }
                else {
                    echo "<p>One or more required file was not found!</p>
                        <p>Make sure your ZIP contains: discharge_measrmnts.csv, rivers.csv, 
                        sensors.csv, stations.csv, rating_dates.csv</p>";
                    echo "<form action='index.php?-action=sync' method='post'>
                        <input type='submit' value='Synchronize only found files' />
                        </form>";

                }
                
            }
            else {
                echo "The ZIP Archive could not be opened.";
            }
        ?>
        
        <div style="border: 1px solid black; background-color: #F5F6CE;padding: 5px; margin-top: 25px;">
            Do not close the window, use the 'Discard Synchronization' Button.<br>
            <form action='<?php echo $_SERVER['PHP_SELF'];?>' method='get'>
                <input type='hidden' name='discard' value='true' /><br>
                <input type='submit' value='Discard Synchonization' />
            </form>
        </div>
        </div>
    </body>
</html>

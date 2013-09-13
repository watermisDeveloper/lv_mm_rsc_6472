<!DOCTYPE html>
<html>
    <head>
<?php 
//define some stuff, should be given by df later on
if(isset($_POST['user'])){
    $delimeter = $_POST['delimeter'];
    $user = $_POST['user'];
}
//csv MIME type list
$csv_mimetypes = array(
    'text/csv',
    'text/plain',
    'application/csv',
    'text/comma-separated-values',
    'application/excel',
    'application/vnd.ms-excel',
    'application/vnd.msexcel',
    'text/anytext',
    'application/octet-stream',
    'application/txt',
);
?>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Water MIS Import form</title>
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
        <?php
            /* As the Upload is aborted, delete the file*/
            if (isset($_POST['discard'])){
                unlink($_POST['filename']);
                
                die('Upload has sucessfully been aborted. You can close this Page or 
                    <a href="index.php?-table=startpage&-action=startSync">go back to import page now</a>
                    <meta http-equiv="refresh" content="5; URL=index.php?-table=startpage&-action=startSync">');
            }
            if (!isset($_FILES['file'])){ die('The file could not be found or is damaged!'); }
            /* check the mime type */
            if (!in_array($_FILES['file']['type'], $csv_mimetypes)) {
                die('<meta http-equiv="refresh" content="0; URL=index.php?--msg=Upload+MIME+type+is+not+allowed.+Only+CSV+upload+is+supported">');
            }
            
            /* move uploaded file to new location */
            $location = 'temp/'.$user.$_FILES['file']['name'];
            move_uploaded_file($_FILES['file']['tmp_name'], $location);
            $data = array(); $ignored= array();
            if (($csv_file = fopen($location, 'r'))!== FALSE){
                $i = 0;
                while ($i < 100 && (($line = fgetcsv($csv_file, 0, $delimeter))!== FALSE)){
                    /* check the lines */
                    if (count($line) == 8){
                        array_push($data, $line);
                    }
                    else {
                        array_push($ignored, 'Line '.$i.':'.implode(' ',$line));
                    }
                    $i++;
                }
                fclose($csv_file);
            }
            echo "<script type='text/javascript'>";
            echo "data = ".json_encode($data).";\n";
            //echo "meta = ".json_encode($meta).";";
            echo "</script>";
        ?>
        <script type="text/javascript">
//       $(document).ready(function(){
//            document.getElementById('input_data').value = JSON.stringify(data);
//        });
        function fillForm(){
            document.getElementById('input_data').value = JSON.stringify(data);
            
        }
        </script>
        <style type='text/css'>
            div#page {
                width: 1000px;
                margin-left: auto;
                margin-right: auto;
                border: 1px solid rgb(238,238,238);
                box-shadow: 0px 0px 2em rgb(153,153,153);
            }
            div#ignored {
                padding-left: 10px;
                padding-right: 10px;
            }
            table#preview {
                width: 800px;
                margin-left: auto;
                margin-right: auto;
                border: 1px solid black;
                border-radius: 15px;
                padding: 10px;
            }
            td {
                min-width: 60px;
            }
            h1 {
                text-align: center;
                color: darkblue;
                font-size: 220%;
            }
            h3 {
                text-align: center;
                color: darkblue;
                font-size: 160%;
            }
            th {
                background-color: aqua;
            }
            div#form {
                width: 200px;
                margin-left: auto;
                margin-right: auto;
            }
            div#textarea {
                margin-left: 10%;
                margin-right: 10%;
            }
            p {
                margin-left: 10%;
                margin-right: 10%;                
            }
        </style>
    </head>
    <body>
        <div id="page">
        <div>
        <h1>Preview</h1>
        <table border="1" style="text-align: center;" id='preview'>
            <tr><th>type_station</th><th>id_station</th><th>sensor</th><th>type_timeseries</th>
            <th>mydate</th><th>myvalue</th><th>origin</th><th>quality</th></tr>
            <?php
            $j = 0;
            foreach ($data as $row){
                $i = 0;
                if ($j < 25){
                    echo "<tr>";
                    foreach($row as $cell){
                        echo "<td>$cell</td>";
                        $i++;
                    }
                    echo "</tr>";
                }
                $j++;
            }
            ?> 
            </table>
        
        <?php if (isset($warning)){?>
        <p id='explanation'>Some or all of your records do not belong to the calling station.</p>
        <?php }?>
        <p>Make sure you want to insert these values as they are! Otherwise Discard the Upload Process
        <span style='color:red; font-weight: bold;'>Do not close this window.</span></p>
        
        
        <div id='form'>
            <form action="index.php?-action=applyImport_2" method="post">
                <input type ="Submit" value="Import these values to WaterMIS" />
                <input type ='hidden' name='delimeter' value='<?php echo $delimeter?>' />
                <input type='hidden' name='userid' value='<?php echo $user; ?>' />
                <input type='hidden' name='filesize' value='<?php echo $_FILES['file']['size']; ?>' />
                <input type="hidden" name="temp_file_name" value="<?php echo $location; ?>" />
            </form>
        <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method='post'>
        <input type='hidden' name='discard' value='true' />
        <input type="hidden" name="filename" value="<?php echo $location; ?>" />
        <input type='hidden' name='station' value='<?php echo json_encode($station);?>' />
        <input type='Submit' value='Discard Upload' />
        </form></div>
        </div>
        <?php if (count($ignored) > 0){?>
        <div id="ignored">
            <h1>Ignored values</h1>
            <p>The following lines were ignored:</p>
            <div id="textarea"><p>
            <?php
            foreach ($ignored as $row){
                echo ''.$row.'<br>';
            }
            ?>
            </p></div>
        </div>
        <?php } 
        ?>
    </div>
    </body>
</html>

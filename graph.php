<!DOCTYPE html>
<html>
    <head>
<?php
/** 
 * Plotting script for WaterMIS - a xataface 2.0alpha application
 * @abstract Uses Highcharts JS to plot timeseries of openstation
 * Codes retrieved from the hardcoded values in Hydraccess and subsequently in the mySQL data model
 * @todo Verify if needed to be put in a table or not
 * @author: Mirko Maelicke
 * 
 */
$sensor_type = array('C'=>'Stages','D'=>'Discharge','P'=>'Precipitation','Q'=>'Quality',
    'M'=>'Meteorological','T'=>'Temperature','H'=>'Humidity','E'=>'Evaporation','L'=>'Groundwater Level');
$sensor_type_unit = array('C'=>'cm','D'=>'m³/s','P'=>'mm','Q'=>'','M'=>'','T'=>'°C','H'=>'%','E'=>'mm','L'=>'cm');
$unit = 'na';preg_match('/\(.*\)/', $record->val('sensortype'), $unit);
if (!isset($unit[0])){ $unit[0] = $sensor_type_unit[$record->val('type_timeseries')];}
$unit[0] = trim($unit[0], '()');
$sensor_type_color = array('C'=>'brown','D'=>'blue','P'=>'blue','Q'=>'green','M'=>'yellow',
    'T'=>'red','H'=>'grey','E'=>'orange','L'=>'black');
$chart_type = array('C'=>'area','D'=>'line','P'=>'column','Q'=>'line','M'=>'line','T'=>'line',
    'H'=>'line','E'=>'line','L'=>'column');
$aggregate_type = array('I'=>'None','D'=> 'Hour','J'=>'Daily','M'=>'Monthly','A'=>'Annualy');



?>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Water MIS Plotter</title>
        
        <!-- stylesheets -->
        <!-- linking stylesheets is for any reason not working -->
        <style type="text/css">
            div.main{
                position: relative;
                width: 1000px;
                margin-left: auto;
                margin-right: auto;
                border: 1px solid rgb(238,238,238);
                box-shadow: 0px 0px 2em rgb(153,153,153);

            }
            div#container {
                border: 1px solid rgb(238,238,238);
                box-shadow: 0px 0px 2em rgb(153,153,153);
                border-radius: 15px;
                margin-left: 10px;
                margin-right: 10px;
            }
            div.content{
                height: 500px;
                width: 550px;
                overflow: auto;
                border: 1px solid black;
                margin-left: auto;
                margin-right: auto;
                display: none;
            }
            div.toggle {
                text-align: center;
                font-weight: bold;
                cursor: pointer;
                border: 1px solid rgb(238,238,238);
                box-shadow: 0px 0px 2em rgb(153,153,153);
                background-color: rgb(238,238,238);
                padding: 10px;
                margin-top: 10px;
                margin-bottom: 10px;
                border-radius: 5px;
            }
            div.toggle:hover {
                background-color: rgb(248,248,238);
                box-shadow: 0px 0px 2em rgb(203,203,203);
                color: grey;
            }
            h1 {
                text-align: center;
                font-size: 220%;
                color: darkblue;
            }
            ul.sidebar {
                border: 1px solid rgb(238,238,238);
                box-shadow: 0px 0px 2em rgb(153,153,153);
                position: fixed;
                margin-left: -55px;
                list-style-type: none;
                width: 30px;
                z-index: 1;
                overflow: hidden;
                padding-left: 0px;
                background-color: rgb(238,238,238);
                border-radius: 15px;
            }
            ul.sidebar:hover {
                width: 200px;
            }
            ul.sidebar li {
                height: 26px;
                margin:0px;
                padding: 5px;
            }
            ul.sidebar li:hover {
                background-color: white;
            }
            ul.sidebar a, div#sb_table, div#sb_structure, div#sb_exel {
                text-decoration: none;
                font-weight: bold;
                display: block;
                color: black;
                cursor: pointer;
            }
            ul.sidebar span {
                display: inline-block;
                margin-left: 30px;
            }
            div#table table {
                text-align: center;
                width: 500px;
            }
        </style>
        <!-- scripts -->
         <!-- jQuery library (served from Google) -->
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
        <script src="http://code.highcharts.com/highcharts.js"></script>
        <!-- convert PHP data array in JS array -->
        <!-- Option to use JSON output of xataface? -->
        <script type="text/javascript">
           plotset = [<?php 
                foreach ($data as $point){
                    echo "[{$point[0]}*1000,{$point[1]}],";
                }
                ?>];
        </script>
        <!-- create chart -->
        <script type="text/javascript">
$(function () { 
   $('#container').highcharts({
        chart: {
            type: '<?php echo $chart_type[$record->val('type_timeseries')];?>',
            zoomType: 'x'
        },
        title: {
            text: '<?php echo "{$aggregate_type[$agg]} aggregated {$sensor_type[$record->val('type_timeseries')]} - Timeseries";?>'
        },
        xAxis: {
          title: {
              text: 'Date/Time'
          },
          type: 'datetime'
        },
        yAxis: {
            title: {
                text: '<?php echo $sensor_type[$record->val('type_timeseries')];?> [<?php echo $unit[0];?>]'
            },
            labels: {
                formatter: function(){
                    return this.value + ' <?php echo $unit[0];?>'
                }
            }
        },
        series: [{
            name: '<?php echo "{$sensor_type[$record->val('type_timeseries')]} Station: {$record->val('id_station')}({$record->display('type_station')})";?>',
            data: plotset,
            //pointStart: Date.UTC(2013, 4,23,11,6,0),
            //pointInterval: 180 *1000,  //3 minutes
            color: '<?php echo $sensor_type_color[$record->val('type_timeseries')];?>'
        }]
    });
});
$(document).ready(function(){
    /*Use the slidebox for view*/
    $('#toggle_structure').click(function(){
        $('#structure').slideToggle('slow');
    });
    $('#toggle_table').click(function(){
        $('#table').slideToggle('slow');
    });
    $('#sb_structure').click(function(){
        $('#structure').slideToggle('slow');
    });
    $('#sb_table').click(function(){
        $('#table').slideToggle('slow');
    });
    $('#sb_exel').click(function(e){
        window.open('data:application/vnd.ms-excel, '+ $('#table').html());
        e.preventDefault();
    });
});    
        </script>

    </head>
    <body>
        <div class="main">
            <?php df_display(array(),'Dataface_Logo.html');?>
            <ul class="sidebar">
                <li><a href="<?php echo 'index.php?-table=stations&-action=view&type_station='.
                        $record->val('type_station').'&id_station='.$record->val('id_station').''; ?>">
                        <img src="images/sb_back.png" alt="  " /><span>back</span></a></li>
                <li><div  id="sb_table"><img src="images/sb_table.gif" alt="  " /><span>table</span></div></li>
                <li><div id="sb_structure"><img src="images/sb_structure.png" alt="  " /><span>structure</span></div></li>
                <li><div id="sb_exel"><img src="images/sb_excel.png" alt=" " style="width: 26px; height: 26px;" />
                        <span>Export to Exel</span></div></li>
            </ul>
            <h1>Station: <?php echo "{$record->val('id_station')}({$record->display('type_station')})";?></h1>
            <div id="container" style="width:980px; height:400px;"></div>
            <h1>Details</h1>
            <div id="toggle_table" class="toggle">View as table</div>
            <div id="table" class="content">
                <table>
                    <tr><th>Date</th><th><?php echo "{$sensor_type[$record->val('type_timeseries')]} [{$unit[0]}]"; ?></th></tr>
                    <?php
                    foreach ($data as $row){
                        echo "<tr><td>";
                        $date = new DateTime;
                        date_timestamp_set($date, $row[0]);
                        if ($agg === 'D'){
                            echo $date->format('d.m.Y H');
                        }
                        elseif ($agg === 'J'){
                            echo $date->format('d.m.Y');
                        }
                        elseif ($agg === 'M'){
                            echo $date->format('M Y');
                        }
                        elseif ($agg === 'A') {
                            echo $date->format('Y');                 
                        }
                        else {
                            echo $date->format('d.m.Y H:i');
                        }
                        echo "</td><td>{$row[1]} </td></tr>";
                    }
                    ?>
                </table>
            </div>
            <div id="toggle_structure" class="toggle">Examine data structure</div>
            <div id="structure" class="content">
                <p>
                    <?php
                    echo "<pre>";
                    var_dump($data);
                    echo "</pre>;"
                    ?>
                </p>
            </div>
            
        </div>
    </body>
</html>
<?php
/**
 * plot.php declaration of actions_plot page
 * This is an action performed by WaterMIS, as the user sends the "Plot"
 * form of a sensor record. This will prepare timeseries data and require a 
 * graphical output script 
 *
 * @author Mirko Maelicke
 */
class actions_plot {
    function handle($params){
        //get application reference
        $app =& Dataface_Application::getInstance();
        $record =& $app->getRecord();
        
        //find matching records
        $res = df_get_records_array('timeseries',array('type_station'=>'='.$record->val('type_station'),
            'id_station'=>'='.$record->val('id_station'),'sensor'=>'='.$record->val('sensor'),
            'type_timeseries'=>'='.$record->val('type_timeseries')));
        
        //combine the matching data as date/value pairs
        if ($_POST['aggregate'] != 'm' && $_POST['aggregate'] != 'a'){
            $combined = $this->create_combined_array($res, "true");       //use timestamp as date format
        }
        else {
            $combined = $this->create_combined_array($res, "false");      //use DateTime as date format
        }
        
        
        //aggregate the data, if needed
        if($_POST['aggregate'] != 'n'){
            $combined = $this->aggregate_combined_array($combined, $_POST['aggregate']);
        }
        
        
        //create a graph
        //require_once 'graph.php';  //highcharts plotting
                
    }
    function aggregate_combined_array($combined, $timestepcode){
        /* this will aggregate the combined array acording to aggregate var*/

        //sort input array
        array_multisort($combined);
        
        if($timestepcode == 'm' || $timestepcode == 'a'){
            $combined = $this->parseTimestamps($combined);
        }
        
        //create timestep and date array
        $date = array();
        $i = 0;
        if ($timestepcode == 'h' || $timestepcode == 'd'){
            $decode_timestep = array('h'=>60*60,'d'=>60*60*24);
            $timestep = $decode_timestep[$timestepcode];
            $start = floor($combined[0][0]/$timestep)*$timestep;                //first date round to full timestep
//            $end = ceil(end($combined)[0]/$timestep)*$timestep;                 //last date round to full next timestep 
            $tempdate = $start;
            //create date array
            while($tempdate <= $end){
                $date[$i] = $tempdate;
                $tempdate += $timestep;
                $i++;
            }
            
            /*order the timeseries for aggregation*/
            $combined = $this->orderValues($combined, $date, $timestep);
        }
        elseif ($timestepcode == 'm' || $timestepcode == 'a'){
            $decode_timestep = array('m'=>'month','a'=>'year');
            $start = $combined[0][0];
            $end = end($combined);
            $end = $end[0];
            if ($timestepcode == 'm'){
                 $timestep = DateInterval::createFromDateString('P1Y');
                $start->setDate((int)$start->format('Y'),(int)$start->format('m'), 1);   //first date round to full date
                $end->setDate((int)$end->format('Y'),(int)$end->format('m'),(int)$end->format('t')); //last date round to full next date
            }
            elseif ($timestepcode == 'a') {
                $timestep = DateInterval::createFromDateString('P1M');
                $start->setDate($start->format('Y'),1,1);                       //1. Jan of given year
                $end->setDate($end->format('Y'),12,31);                         //31. Dec of given year
            }
            $tempdate = new DateTime();
            $tempdate = $start;
            while ($tempdate->getTimestamp() <= $end->getTimestamp()){
                $date[$i] = $tempdate;
                $tempdate->modify('first day of next '.$decode_timestep[$timestepcode]);
                $i++;
            }
            /*order the timeseries for aggregation*/
            //$combined = $this->orderValuesDate($combined, $date, $timestep);
        }
        //development
        echo "<pre>";
        print_r($combined);
        echo "</pre>";


        
        
        //$combined = $this->summarizeNew($combined);
        return $combined;

    }
    
    function orderValues($arrayValues, $date, $timestep){
        /*This function will reorder a given Array $arrayValues. $arrayValues has to
         * contain several Array containing two elements. The first is the date as a
         * UNIX Timestamp. The second element contains the value.
         * The $date has to be given, which is  an array of all days, values are 
         * reqired for.
         */
        $i = 0;
        $j = 0;
        $x = 0;
        $returnArray = array();

        while ((isset($arrayValues[$j]) && isset($date[$i]))){
            if ($date[$i] <= $arrayValues[$j][0] && ($date[$i] + $timestep) >= $arrayValues[$j][0]){
                if(isset($returnArray[$i][1])){
                    $x = count($returnArray[$i][1]);
                }
                else { $x = 0;}
                $returnArray[$i][1][$x] = $arrayValues[$j][1];
                $j++;
            }
            else{
                $returnArray[$i][0] = $date[$i];
                $i++;
            }
        }
        //attach the last timestamp
        if(isset($date[$i])){
            $returnArray[$i][0] = $date[$i];
        }

        //return the ordered array
        return $returnArray;

    }
    
    function orderValuesDate($arrayValues, $date, $timestep){
        /*This function will reorder a given Array $arrayValues. $arrayValues has to
         * contain several Array containing two elements. The first is the date as a
         * PHP Datetime object. The second element contains the value.
         * The $date has to be given, which is  an array of all days, values are 
         * reqired for.
         */
        $i = 0;
        $j = 0;
        $x = 0;
        $returnArray = array();

        while ((isset($arrayValues[$j]) && isset($date[$i]))){
            if ($date[$i] <= $arrayValues[$j][0] && ($date[$i]->add($timestep)) >= $arrayValues[$j][0]){
                if(isset($returnArray[$i][1])){
                    $x = count($returnArray[$i][1]);
                }
                else { $x = 0;}
                $returnArray[$i][1][$x] = $arrayValues[$j][1];
                $j++;
            }
            else{
                $returnArray[$i][0] = $date[$i];
                $i++;
            }
        }
        //attach the last timestamp
        if(isset($date[$i])){
            $returnArray[$i][0] = $date[$i];
        }

        //return the ordered array
        return $returnArray;

    }
    
    
    function summarizeNew($arrayValues, $mode="mean"){
        $resultArray = array();

        $i = 0;
        $j = 0;
        $resultArray = array();

        while (isset($arrayValues[$i])){
            //loop through all datepoints

            if (isset($arrayValues[$i][1])){
                //data is availible
                $temp = 0;
                foreach ($arrayValues[$i][1] as $value){
                    $temp += $value;
                }
                //$temp is now the sum of all datapoints
                if ($mode == "mean"){
                    $n = count($arrayValues[$i][1]);
                    $temp /= $n;
                }
                array_push($resultArray, array($arrayValues[$i][0],$temp));
            }
            $i++;
        }
        return $resultArray;
    }
    
    /** creates a combined array containing pairs of date/values
     *  if timestamp is TRUE, the date will be saved as a UNIX timestamp
     *  else as a PHP Datetime object.
     */
    function create_combined_array($records, $timestamp="true"){
        $data = array();
        foreach ($records as $record){
//            if ($timestamp == "true"){
                $el = array(DateTime::createFromFormat('Y-m-d H:i:s', $record->strval('mydate'))->getTimestamp(),
                    (float)$record->htmlValue('myvalue'));
                array_push($data, $el);
//            }
/*            else {
                $el = array(DateTime::createFromFormat("Y-m-d H:i:s", $record->strval('mydate'),
                    new DateTimeZone("Africa/Kigali")),(float)$record->htmlValue('myvalue'));
                array_push($data, $el);
            }*/
        }
        return $data;
    }
    
    function parseTimestamps($combined){
        $result = array();
        $i = 0;
        foreach ($combined as $element){
                $ts = $element[0];
                $result[$i][0] = new DateTime(); 
                $result[$i][0]->setTimestamp($ts);
                $result[$i][1] = $element[1];
                $i++;
            }
        return $result;
    }
}
?>

<table style="width: 100%;height: 100%; text-align: center" id='filterarea'> 
    <tr><td>Filter by Catchment</td><td>Filter by River</td><td>Filter by District</td><td>Filter by Location</td></tr>
    <tr><td><select onchange='applyAdvFilter("nb1",this.options[this.selectedIndex].value);'>
    <?php
        while ($nb1 = mysql_fetch_row($nb1s)){
            echo "<option value='".$nb1[0]."'>".$nb1[1]." (".$nb1[0].")</option>";
        }
    ?>
    </select></td>
    <td><select onchange='applyAdvFilter("riv",this.options[this.selectedIndex].value);'>
            <?php
                while ($river = mysql_fetch_row($rivers)){
                    echo "<option value='".$river[0]."'>".$river[0]."</option>";
                }
            ?>
    </select></td>
    <td><select onchange='applyAdvFilter("dis",this.options[this.selectedIndex].value);'>
            <?php
                while ($district = mysql_fetch_row($districts)){
                    echo "<option value='".$district[0]."'>".$district[1]."</option>";
                }
            ?>
    </select></td>
    <td>Lon: <input type='text' style='width: 3em' id='tx_lon' value='' />
        Lat: <input type='text' style='width: 3em' id='tx_lat' value='' /><br>
        <input type='button' style='margin-top: 5px; float: right; ' value ='Show!' 
               onClick='whereAmI(parseFloat($("#tx_lon").val()),parseFloat($("#tx_lat").val()));
               $("#tx_within").css("display","block");' /><br> 
        <p id='tx_within' style='margin-left: 5px; display: none;'>Show stations within: 
        <input type='text' value='50' style='width: 5em;' 
               onchange='applyAdvFilter("loc", this.value)'/> km.</p>
        
    </td>
    </tr>
</table>
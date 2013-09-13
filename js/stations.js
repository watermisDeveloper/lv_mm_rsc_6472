/* stations.js
 * This script declares initialization function for the OpenLayer Map Object
 * included to each stations Record in a separate section. The actual station 
 * is parsed as a JSON object of structure:
 *      Station{'name': stationname, 'lon': longitude, 'lat': latitude}
 * 
 * @author Mirko Maelicke
 */
//init the map as the station was loaded 
$(document).ready(function(){
    init_station_map();
});

var map, markers;
function init_station_map(){
    map = new OpenLayers.Map('map');
        //add base map OSM
    map.addLayer(new OpenLayers.Layer.OSM());
    markers = new OpenLayers.Layer.Markers('Station');
    
    map.addLayer(markers);
    map.addControl(new OpenLayers.Control.LayerSwitcher());
    
    /*set map center*/
    map.setCenter(new OpenLayers.LonLat(parseFloat(Station.lon), parseFloat(Station.lat)).transform(
    new OpenLayers.Projection('EPSG:4326'), map.getProjectionObject()), 10);
    
    /*Show the Station as a marker*/
    station_lonlat = new OpenLayers.LonLat(parseFloat(Station.lon), parseFloat(Station.lat)).transform(
        new OpenLayers.Projection("EPSG:4326"), map.getProjectionObject());

    var size = new OpenLayers.Size(21,25);
    var offset = new OpenLayers.Pixel(-(size.w/2), -size.h);
    var icon = new OpenLayers.Icon('images/marker.png',size,offset);

   
    markers.addMarker(new OpenLayers.Marker(station_lonlat, icon));
}


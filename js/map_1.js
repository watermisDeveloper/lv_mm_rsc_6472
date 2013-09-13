//$(document).ready(function() { 
//    init_map(); });

/* OpenLayers map settings */
var map, boreholes, view_bh, rwandabounds;
var nb1_wms, nb2_wms, district_wms;
var Meta = new Array();
var selectControl, hoverControl, panelCustomNavToolbar, CustomNavToolbar;


 
        $(document).ready(function(){
            init_map();
        });




function init_map() {



    //create map object
    /* Set the bounds of your geographical area, by
   specifying bottom-left (bl) and top right
   (tr) corners */
   rwandabounds = new OpenLayers.Bounds();
        var bl_point = new OpenLayers.LonLat(3172900,-332400);
        var tr_point = new OpenLayers.LonLat(3477000,-70700);
 //       var bl_point = new OpenLayers.LonLat(19.3, 34.75);
 //       var tr_point = new OpenLayers.LonLat(29.65,41.8);
        rwandabounds.extend(bl_point);
        rwandabounds.extend(tr_point);

    //Define the MAP Options//
    var options = {
        'units' :   "m",
        'numZoomLevels' :   28,
        'sphericalMercator': true,
        'maxExtent': rwandabounds,
        'projection'    :   new OpenLayers.Projection("EPSG:900913"),
        'displayProjection':    new OpenLayers.Projection("EPSG:4326")
        };
        
    map = new OpenLayers.Map('map', options);
    
    /* add base maps OSM and Google. TO use Googlestreetmaps, the API
     * has to be included from 
     * http://maps.google.com/maps/api/js?v=3&amp;sensor=false   */
    map.addLayer(new OpenLayers.Layer.OSM('Open Street Map'));
    map.addLayer(new OpenLayers.Layer.Google("Google Street Map", {visibility: false}));
    map.addLayer(new OpenLayers.Layer.Google( "Google Physical",{type: google.maps.MapTypeId.TERRAIN}));
    map.addLayer(new OpenLayers.Layer.Google( "Google Hybrid",{type: google.maps.MapTypeId.HYBRID, numZoomLevels: 20}));
    
    /* Set map Center and extend to show entire Rwanda */
    map.setCenter(new OpenLayers.LonLat(30.071100, -1.958018).transform(
            new OpenLayers.Projection('EPSG:4326'), map.getProjectionObject()), 8);
    map.addControl(new OpenLayers.Control.LayerSwitcher());
    map.addControl(new OpenLayers.Control.OverviewMap());
    var AMousePosition = new OpenLayers.Control.MousePosition();
    map.addControl(AMousePosition);
    $("div.olControlMousePosition").css("bottom","5px");
    /* hide the default Zoom control and show a nice one 
     *  Just Hiding the object is not a good way, but works*/
    $('.olControlZoom').css('display', 'none');
    map.addControl(new OpenLayers.Control.PanZoomBar());
       
    /* do some styling*/
    $('.layersDiv').css('border-radius', '15px');
    $('.layersDiv').css('box-shadow','0px 0px 1.5em rgb(153,153,153)');
    $('.layersDiv').css('background-color','rgb(238,238,238)');
    $('.layersDiv').css('color','black');

    /*create Boreholes layer*/
    var boreholes = new OpenLayers.Layer.Vector('Borejokes', {
        styleMap: new OpenLayers.StyleMap({
            externalGraphic: 'images/dot.png',
            graphicWidth: 16, graphicHeight: 16, graphicYOffset: -15,
            pointRadius: 10,
            //Clustering not working (LV)
            strategies: [
            new OpenLayers.Strategy.Fixed(),
            new OpenLayers.Strategy.Cluster({
                distance: 450
                })  
             ]
        })
    });
    var select = new OpenLayers.Control.SelectFeature(boreholes, {hover: true}
                );
    map.addControl(select);
    select.activate();
    boreholes.events.on({"featureselected": display});
    
    map.addLayer(boreholes);
    fillLayer(boreholes, Boreholes_Array);
    view_bh = Boreholes_Array;
    
    function fillLayer(layer, EntryArray){
        $.each(EntryArray, function(i, object) {
        var objectLocation = new OpenLayers.Geometry.Point(parseFloat(object.lon),
                parseFloat(object.lat)).transform(new OpenLayers.Projection('EPSG:4326'), map.getProjectionObject());
        features = new OpenLayers.Feature.Vector(objectLocation);
        layer.addFeatures(features);
        Meta[""+features.id] = {'id': object.id_borehole,'source':object.source};

    });
   }  
   function display(event) {
                // clear previous photo list and create new one
                document.getElementById("map_title").innerHTML = "";
                var msg = event.feature.id_borehole;
                document.getElementById("map_title").innerHTML = msg;
               
            }
}
//$(document).ready(function() { 
//    init_map(); });

/* OpenLayers map settings */

/* map.js
 * This is custom js file for control function of the map
 * 
 * 
 * 
 * OpenLayers:
 * The Map is initialized and the Stations_Array, Meteo_Array and Boreholes_array is used to produce Vector 
 * Layers.
 * Improvements: Link in Popup relates the station to its name, better to use
 *                  its combined key: station_id, type_station
 * 
 * @author: Lavuun Verstraete
 * 
 * it refers to extra js libraries used for popup/tooltips
 * ../lib/patches_OL-popup-autosize.js
 * ../lib/FeaturePopups.js
 */
var map, boreholes, view_bh, rwandabounds, select;
var nb1_wms, nb2_wms, district_wms, hydrostations, meteostations;
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
    
    map = new OpenLayers.Map( 'map', { controls: [] }, options);
    //map = new OpenLayers.Map('map', options);
    
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
    //map.addControl(new OpenLayers.Control.PanZoomBar());
    /* do some styling*/
    $('.layersDiv').css('border-radius', '15px');
    $('.layersDiv').css('box-shadow','0px 0px 1.5em rgb(153,153,153)');
    $('.layersDiv').css('background-color','rgb(238,238,238)');
    $('.layersDiv').css('color','black');
    
    
    zb = new OpenLayers.Control.ZoomBox(
                {title:"Zoom box: Selecting it you can zoom on an area by clicking and dragging."});
    
    var panel = new OpenLayers.Control.Panel({//defaultControl: zb,
        initialize: function(options) {
            OpenLayers.Control.Panel.prototype.initialize.apply(this, [options]);
            // To make the custom navtoolbar use the regular navtoolbar style
            this.addControls([
                new OpenLayers.Control.ZoomBox(
                {title:"Zoom box: Selecting it you can zoom on an area by clicking and dragging."}),
                new OpenLayers.Control.Button({title: "Click to go to max extent", 
                    id: 'btnMaxExtent',       
                    type: OpenLayers.Control.TYPE_BUTTON,
                    trigger: function() {map.zoomToExtent(rwandabounds); initSelectControl();}})
                ]);
                this.displayClass = 'olControlNavToolbar';
        },
        /**
        * Method: draw
        * calls the default draw, and then activates mouse defaults.
        */
        
        
        draw: function() {
            var div = OpenLayers.Control.Panel.prototype.draw.apply(this, arguments);
            this.activateControl(this.controls[0]);
            return div;
        }
        });
    
            
    nav = new OpenLayers.Control.NavigationHistory();
            // parent control must be added to the map
   //map.addControl(nav);
   panel.addControls([zb, nav.next, nav.previous]);
            
   map.addControl(panel); 
    $("div.olControlNavToolbar").css("top","5px");
    $("div.olControlNavToolbar").css("left","60px");
    
    map.addControl(new OpenLayers.Control.ScaleLine());


    /*Create and Add Layers*/
    var boreholes = new OpenLayers.Layer.Vector('Boreholes', {
//        strategies: [   new OpenLayers.Strategy.Fixed(),
//                        new OpenLayers.Strategy.Cluster()],
//    styleMap: new OpenLayers.StyleMap({
//        'default': new OpenLayers.Style({
//                pointRadius: '${radius}',
//                fillOpacity: 0.6,
//                fillColor: '#ffcc66',
//                strokeColor: '#cc6633'
//            }, {
//                context: {
//                    radius: function(feature) {
//                        return Math.min(feature.attributes.count, 10) * 1.5 + 2;
//                    }
//                }
//        }),
//        'select': {fillColor: '#8aeeef'}

        styleMap: new OpenLayers.StyleMap({
            externalGraphic: 'images/dot.png',
            graphicWidth: 16, graphicHeight: 16, graphicYOffset: -15,
            pointRadius: 10
        })
    });
//    var select = new OpenLayers.Control.SelectFeature(boreholes, {hover: true}
//                );
//    map.addControl(select);
//    select.activate();
//    boreholes.events.on({"featureselected": display});
//    
    map.addLayer(boreholes);
    fillLayer(boreholes, Boreholes_Array);
    
    view_bh = Boreholes_Array;

    /*create Hydrostations layer*/
    var hydrostations = new OpenLayers.Layer.Vector('Hydrological Stations', {
        styleMap: new OpenLayers.StyleMap({
            externalGraphic: 'images/marker_blue.png',
            graphicWidth: 20, graphicHeight: 24, graphicYOffset: -15,
            pointRadius: 10
            //title: '{$tooltip}'
        })
    });
    map.addLayer(hydrostations);
    fillLayer(hydrostations, Hydro_Stations_Array);
    view_hydro = Hydro_Stations_Array;
 
    /*create Meteostations layer*/
    var meteostations = new OpenLayers.Layer.Vector('Meteorological Stations', {
        styleMap: new OpenLayers.StyleMap({
            externalGraphic: 'images/marker_red.png',
            graphicWidth: 20, graphicHeight: 24, graphicYOffset: -15,
            title: '{$tooltip}', pointRadius: 10
        })
    });
    map.addLayer(meteostations);
    fillLayer(meteostations, Meteo_Stations_Array);
    view_meteo = Meteo_Stations_Array;

        /*create the wms layers (nb123 etc...)*/
    nb1_wms = new OpenLayers.Layer.WMS("Catchment level 1", "http://41.215.250.87:8080/geoserver/ows?",
            {   layers: 'nwrmp_wgs84tm:NB1_wgs84tm',styles: 'NB1',transparent: true,format: 'image/png'},
            {   isBaseLayer: false,opacity: 0.8,tiled: false}
          );
    nb2_wms = new OpenLayers.Layer.WMS("Catchment level 2", "http://41.215.250.87:8080/geoserver/wms?",
            {   layers: 'nwrmp_wgs84tm:NB2_wgs84tm',styles: 'NB2',transparent: true,format: 'image/png'},
            {   isBaseLayer: false,opacity: 0.8,tiled: false,visibility:false}
          );              
    district_wms = new OpenLayers.Layer.WMS("District", "http://41.215.250.87:8080/geoserver/wms?",
            {   layers: 'nwrmp_wgs84tm:District_wgs84tm',styles: 'District',transparent: true,format: 'image/png'},
            {   isBaseLayer: false,opacity: 0.8,tiled: false,visibility:false}
          );
    map.addLayers([nb1_wms,nb2_wms,district_wms]);    


    /*Select and hoover => popup and tooltip 
     * @author:jorix
     * @source: https://github.com/jorix/OL-FeaturePopups
     * 
     * */
            select = new OpenLayers.Control.FeaturePopups({
                selectionBox: true,
                layers: [[
                    hydrostations , {templates: {
                        hover: "${feature.name}",
                        single: "<h2>${.name}</h2>${.id}",
                        item: "<li><a href=\"#\" ${showPopup()}>${.name}</a></li>"
                    }}
                ]]
            });

            map.addControl(select);

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

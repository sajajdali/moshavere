import L from "leaflet";
(function ($) {
    var app = new Mapp({
        element: "#app",
        presets: {
          latlng: {
            lat: 35.73249,
            lng: 51.42268
          },
          zoom: 10
        },
        apiKey: "Your API Key"
      });
      app.addVectorLayers();

      // Add in a crosshair for the map
      var crosshairIcon = L.icon({
        iconUrl: 'https://cloud.son.ir/index.php/s/qVUHj7HJSr1A7MK/download',
        iconSize:     [20, 20], // size of the icon
        iconAnchor:   [10, 10], // point of the icon which will correspond to marker's location
      });
      var crosshairMarker = new L.marker(app.map.getCenter(), {icon: crosshairIcon, clickable:false});
      crosshairMarker.addTo(app.map);

      // Move the crosshair to the center of the map when the user pans
      app.map.on('move', function(e) {
        crosshairMarker.setLatLng(app.map.getCenter());
      });

      crosshairMarker.on('click', function(event){
        console.log(event.latlng)
      });
})(jQuery);

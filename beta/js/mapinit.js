console.log('madisonrightnow.com: initializing map');
var map;
function initialize() {
  /*var myLatlng = new google.maps.LatLng(43.07, -89.39);*/
  var myLatlng = new google.maps.LatLng(43.074793, -89.383863);

  var mapOptions = {
    zoom: 10,
    center: myLatlng,
    draggable: false,
    zoomControl: true,
    scrollwheel: false,
    disableDoubleClickZoom: false
  };
  map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);
  var trafficLayer = new google.maps.TrafficLayer();
  trafficLayer.setMap(map);
  $(document).on('shown.bs.collapse', function () {
	google.maps.event.trigger(map, "resize");
  });
}
google.maps.event.addDomListener(window, 'load', initialize);
// April 5 2018 xy

var slowCameraRefreshInterval = 60; // seconds
var busRefreshInterval = 30; // seconds
busRefreshInterval = busRefreshInterval * 1000;
slowCameraRefreshInterval = slowCameraRefreshInterval * 1000;
var streaming = lightboxOpen = false;
var map;

// BUS MAP ----------------------------------------------------------------------------
function initMap() {
	map = new google.maps.Map(document.getElementById('map'), {
	  zoom: 12,
	  center: {lat: 43.0731, lng: -89.4012}
	});
	map.data.loadGeoJson('/php/bus.php?function=map');
	map.setOptions({styles: styles['mrnMapStyle']});
	map.data.setStyle(function(feature) {
		return /** @type {google.maps.Data.StyleOptions} */({
			icon: "/images/measle.png"
		});
	});
}

// CLOCK -------------------------------------------------------------------------------
function GetClock(){
	tday=new Array("Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday");
	tmonth=new Array("January","February","March","April","May","June","July","August","September","October","November","December");
	var d=new Date();
	var nday=d.getDay(),nmonth=d.getMonth(),ndate=d.getDate(),nyear=d.getFullYear();
	var nhour=d.getHours(),nmin=d.getMinutes(),nsec=d.getSeconds(),ap;
	if(nhour==0){ap=" AM";nhour=12;}
	else if(nhour<12){ap=" AM";}
	else if(nhour==12){ap=" PM";}
	else if(nhour>12){ap=" PM";nhour-=12;}
	if(nmin<=9) nmin="0"+nmin;
	if(nsec<=9) nsec="0"+nsec;
	document.getElementById('clockSpan').innerHTML=""+tday[nday]+", "+tmonth[nmonth]+" "+ndate+", "+nyear+" at "+nhour+":"+nmin+":"+nsec+ap+"";
}

// HEX GENERATOR FUNCTION --------------------------------------------------------------
function randHex(length) {
	var text = "";
	var possible = "ABCDEF0123456789";
	for (var i = 0; i < length; i++)
	text += possible.charAt(Math.floor(Math.random() * possible.length));
	return text;
}

// TOAST FUNCTION ----------------------------------------------------------------------
function snack(msg) {
	var x = document.getElementById("snackbar");
	x.className = "show";
	$('#snackbar').html(msg);
	setTimeout(function(){ x.className = x.className.replace("show", ""); }, 4000);
}

// MAD IMAGE STREAMER ------------------------------------------------------------------
function freshenImages(){
	console.log('freshenImages: invoked');
	$('.mrn-img-refresh').each(function () {
		if( ( $(this).closest('div').hasClass('show') && lightboxOpen == false ) || 
			( $(this).hasClass('lightboxed') && lightboxOpen == true ) ){
			var curSrc = $(this).attr('src');
			$(this).attr('src', curSrc);
		}
	});
	$('.mrn-img-refresh').each(function () {
		$(this).off('load').on('load',function() {
			var curSrc = $(this).attr('src');
			if( ( $(this).closest('div').hasClass('show') && lightboxOpen == false ) || 
				( $(this).hasClass('lightboxed') && lightboxOpen == true ) ){
				if (streaming == false){ $('#statusSpan').html('<i class="fas fa-sync fa-spin"></i>'); }
				$(this).attr('src', curSrc);
				var thisAlt = $(this).attr('alt');
				console.log('fra:200: ' + thisAlt);
				streaming = true;
			}
			else{
				$('#statusSpan').html('<i class="fas fa-check-circle"></i>');
				streaming = false;
			}
		});
    });
	$('.mrn-img-refresh').off('error').on('error', function(){
		if( ( $(this).closest('div').hasClass('show') && lightboxOpen == false ) || 
			( $(this).hasClass('lightboxed') && lightboxOpen == true ) ){
			var curSrc = $(this).attr('src');
			$(this).attr('src', curSrc);
			var thisAlt = $(this).attr('alt');
			console.log('fra:400: ' + thisAlt);
		}
	});
}

// SLOW IMAGE STREAMER -----------------------------------------------------------------
function freshenImagesSlowly(){
	setInterval(function() {
		$('.mrn-img-refresh-slowly').each(function () {
			if( ( $(this).closest('div').hasClass('show') && lightboxOpen == false ) || 
				( $(this).hasClass('lightboxed') && lightboxOpen == true ) ){
				$('#statusSpan').html('<i class="fas fa-sync fa-spin"></i>');
				var curSrc = $(this).attr('src');
				var thisAlt = $(this).attr('alt');
				$(this).attr('src', curSrc);
				$('#statusSpan').html('<i class="fas fa-check-circle"></i>');
				console.log('slo:upd: ' + thisAlt);
			}
		});
	}, slowCameraRefreshInterval);
}

// BUS LOCATION UPDATES ----------------------------------------------------------------
function busUpdate(){
	console.log('bus:ini');
	setInterval(function() {
		if ( $('#map').parent().hasClass('show') && lightboxOpen == false ) {
			console.log('bus:upd');
			map.data.forEach(function(feature) {
				map.data.remove(feature);
			});
			geoURL = '/php/bus.php?function=map&rev=' + randHex(8);
			map.data.loadGeoJson(geoURL);
		}
	}, busRefreshInterval);
}

// LIGHTBOX EVENTS ---------------------------------------------------------------------
$('.lightboxLink').featherlight({
	afterOpen: function(event){
		lightboxOpen = true;
		// i wish i could abort pending image requests at this point
		freshenImages();
		snack('Please wait for the live feed to start');
	},
	afterClose: function(event){
		lightboxOpen = false;
		freshenImages();
	}
});

// KICKSTART when card closes ----------------------------------------------------------
$('#cameras, #dotcameras').on('shown.bs.collapse', function () {
	freshenImages();
});

// PARKING -----------------------------------------------------------------------------
$.ajax({
	type: 'GET',
	url: 'php/parking.php',
	data: {
		'function' : 'cityParking'
	}
})
.done(function(data){ $('#parkingCardBody').html(data); })
.fail(function() { console.log('AJAX GET failed: parking'); });

// CAMPUS PARKING ----------------------------------------------------------------------
$.ajax({
	type: 'GET',
	url: 'php/parking.php',
	data: {
		'function' : 'campusParking'
	}
})
.done(function(data){ $('#campusParkingCardBody').html(data); })
.fail(function() { console.log('AJAX GET failed: campusParking'); });

// DOCUMENT READY ----------------------------------------------------------------------
$(document).ready(function(){
	GetClock(); setInterval(GetClock,1000);
	freshenImages();
	freshenImagesSlowly();
	busUpdate();
	$('[data-toggle="tooltip"]').tooltip();
	$('#statusSpan').html('<i class="fas fa-check-circle"></i>');
});
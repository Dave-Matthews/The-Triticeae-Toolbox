      var map;
      var lat = 10;
      var lng = 10;
      var stationCount = 1;
      var php_self = document.location.href;
      var unq_file = "";

      function placeMarker(e) {
          placeMarkerAndPanTo(e.latLng, map);
      }

      function getPrev() {
          stationCount = stationCount + 1;
          if (stationCount < 1) {
              stationCount = 1;
          }
          getWeather();
      }

      function getNext() {
          stationCount = stationCount + 1;
          if (stationCount > 5) {
              stationCount = 5;
          }
          getWeather();
      }

      function getWeather() {
          unq_file = Date.now();
          var date1 = document.getElementById('date1').value;
          var date2 = document.getElementById('date2').value;
          $( "#spinner" ).show();
          jQuery.ajax({
            type: "GET",
            url: php_self,
            data: "function=getWeather&lat=" + lat + "&long=" + lng + "&unq=" + unq_file + "&date1=" + date1 + "&date2=" + date2 + "&cnt=" + stationCount,
            success: function(data, textStatus) {
                jQuery("#step2").html(data);
                $( "#spinner" ).hide();
            },
            error: function() {
                alert('Error retrieving weather data');
            }
        });
      }

      function initMap() {

        var chicago = new google.maps.LatLng(41.850, -87.650);

        var map = new google.maps.Map(document.getElementById('map'), {
          center: chicago,
          zoom: 6
        });

        // Try HTML5 geolocation
        if (navigator.geolocation) {
          navigator.geolocation.getCurrentPosition(function(position) {
            var pos = {
              lat: position.coords.latitude,
              lng: position.coords.longitude
            };

            coordInfoWindow.setPosition(pos);
            coordInfoWindow.setContent('Found Location');
            lat = position.coords.latitude;
            lng = position.coords.longitude;
            map.setCenter(pos);
          }, function() {
            handleLocationError(true, infoWindow, map.getCenter());
          });
        } else {
          // Browser doesn't support Geolocation
          handleLocationError(false, infoWindow, map.getCenter());
        }
   
        map.addListener('click', function(e) {
          //placeMarkerAndPanTo(e.latLng, map);
          coordInfoWindow.setContent(createInfoWindowContent(e.latLng, map.getZoom()));
          coordInfoWindow.setPosition(e.latLng);
        });

        var coordInfoWindow = new google.maps.InfoWindow();
        coordInfoWindow.setContent(createInfoWindowContent(chicago, map.getZoom()));
        coordInfoWindow.setPosition(chicago);
        coordInfoWindow.open(map);
 
      }

      function placeMarkerAndPanTo(latLng, map) {
        var marker = new google.maps.Marker({
          position: latLng,
          map: map
        });
        map.panTo(latLng);
      }

      function createInfoWindowContent(latLng, zoom) {
        var scale = 1 << zoom;
        lat = latLng.lat();
        lng = latLng.lng();
        return [
          'Selected Location',
          'LatLng: ' + latLng
        ].join('<br>');
      }


@extends('layout')

@section('main')
    <div class="container">
        <a href="{{ url('/logout') }}">Logout</a>
        <h2>Meeting Management</h2>
        <form action="{{ route("addMeeting") }}" method="POST">
            @csrf
            
            {{-- Get current logged in user id from database --}}
            <input type="hidden" name="user_id" value="{{ Auth::id() }}">
            
            <div class="row">
                <div class="col-sm-3">
                    <label for="">Location</label><br>
                    <input type="text" name="location" id="location" required>
                </div>
                <div class="col-sm-3">
                    <label for="">Client Name</label><br>
                    <input type="text" name="name" placeholder="Client Name" required>
                </div>
                <div class="col-sm-3">
                    <label for="">Meeting Time Duration</label><br>
                    <input type="number" name="time" placeholder="Meeting time (in Minutes)" required>
                    <br> <span>Available (09:00 am to 06:00 pm)</span>
                </div>
                <div class="col-sm-3">
                    <label for="">Date</label><br>
                    <input type="date" name="date" required>
                </div>
            </div>
            <input type="hidden" id="latitude" name="latitude">
            <input type="hidden" id="longitude" name="longitude">
            <input type="hidden" id="ip" name="ip">
            <input type="hidden" id="city" name="city">
            <input type="hidden" id="dtime" name="dtime">
            <input type="hidden" id="dkm" name="dkm">
            <div class="row">
                <div class="col-sm">
                    <input type="submit" class="btn btn-primary">
                </div>
            </div>
        </form>
        @if (Session::has('success'))
            <p style="color:green;">{{ Session::get('success') }}</p>
        @endif
    </div>
    {{-- Google Place Autocomplete API [replace new key with the old ones] --}}
    <script type="text/javascript"
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAOJWkhABD8cXf15rr4dm-4ZQMW2g_wSQo&libraries=places"></script>

    <script>
        $(document).ready(function() {
            var autocomplete;
            var to = 'location';
            autocomplete = new google.maps.places.Autocomplete((document.getElementById(to)), {
                types: ['geocode'],
            });

            // find latitude and longitude of the selected location and add them to the respected field
            google.maps.event.addListener(autocomplete, 'place_changed', function() {

                var near_place = autocomplete.getPlace();

                jQuery("#latitude").val(near_place.geometry.location.lat())
                jQuery("#longitude").val(near_place.geometry.location.lng())

                // find user current ip address 
                $.getJSON(" https://api.ipify.org/?formate=json", function(data) {

                    let ip = data.ip;

                    jQuery("#ip").val(ip);

                    getCity(ip);
                });
            });
        });

        function getCity(ip) {
            var req = new XMLHttpRequest();
            req.open("GET", "http://ip-api.com/json/" + ip, true);
            req.send();

            req.onreadystatechange = function() {
                if (req.readyState == 4 && req.status == 200) {
                    var obj = JSON.parse(req.responseText);
                    jQuery("#city").val(obj.city);
                    calculateDistance();
                }
            }
        }

        function calculateDistance() {
            var to = jQuery("#city").val();
            var from = jQuery("#location").val();

            var service = new google.maps.DistanceMatrixService();

            service.getDistanceMatrix({

                origins: [to],
                destinations: [from],
                travelMode: google.maps.TravelMode.DRIVING,
                unitSystem: google.maps.UnitSystem.metric,
                avoidHighways: false,
                avoidTolls: false,

            }, callback);
        }

        function callback(response, status) {
            if (status != google.maps.DistanceMatrixStatus.OK) {
                console.log("Something wrong");
            } else {
                if (response.rows[0].elements[0].status == "ZERO_RESULTS") {
                    console.log("No roads available");
                } else {
                    var distance = response.rows[0].elements[0].distance;
                    var duration = response.rows[0].elements[0].duration;
                    var distance_in_km = distance.value/1000; //distance in kilometers
                    var duration_in_minutes = duration.value/60;

                    jQuery("#dkm").val(parseInt(distance_in_km));
                    jQuery("#dtime").val(parseInt(duration_in_minutes));
                }
            }
        }
    </script>
@endsection

@extends('layout')

@section('main')
    <div class="container">
        <a href="{{ url('/logout') }}">Logout</a>
        <h2>Meeting Management</h2>
        <form action="" method="POST">
            @csrf
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
            <div class="row">
                <div class="col-sm">
                    <input type="submit" class="btn btn-primary">
                </div>
            </div>
        </form>
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
                });
            });
        });
    </script>
@endsection
@extends('layout')

@section('main')
    <div class="container">
        <a href="{{ route('logout') }}">Logout</a>
        <h2>Meeting Management</h2>
        <form action="{{ route('addMeeting') }}" method="POST">
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
                    <label for="">Date Add/View</label><br>
                    <input type="date" name="date" id="date" required>
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
        @if (Session::has('error'))
            <p style="color:red;">{{ Session::get('error') }}</p>
        @endif
    </div>
    {{-- Avilable Meeting Data --}}
    <div class="container">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>S.No</th>
                    <th>Name</th>
                    <th>Location</th>
                    <th>Latitude</th>
                    <th>Longitude</th>
                    <th>Meeting Time</th>
                    <th>Distance Time</th>
                    <th>Distance KM</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody class="tbody">
                @if (count($meeting) > 0)
                    @foreach ($meetings as $meeting)
                        <td>{{ $meeting->id }}</td>
                        <td>{{ $meeting->name }}</td>
                        <td>{{ $meeting->location }}</td>
                        <td>{{ $meeting->latitude }}</td>
                        <td>{{ $meeting->longitude }}</td>
                        <td>{{ $meeting->meeting_time }}</td>
                        <td>{{ $meeting->distance_time }}</td>
                        <td>{{ $meeting->distance_km }} KM</td>
                        <td>{{ $meeting->date }}</td>
                    @endforeach
                @else
                    <tr>
                        <td colspan="9">No Meetings Found!</td>
                    </tr>
                @endif
            </tbody>
        </table>
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

            //get meetings by date
            $("#date").change(function(){
                var date = $(this).val();

                $.ajax({
                    url:"{{ route('getDateMeetings') }}",
                    type:"GET",
                    data:{'date':date},
                    success:function(data){
                        var html = "";
                        var meetings = data.meetings;
                        if (meetings.length > 0) {
                            for (let i = 0; i < meetings.length; i++) {
                                html +=`
                                    <tr>
                                        <td>`+meetings[i]['id']+`</td>    
                                        <td>`+meetings[i]['name']+`</td>    
                                        <td>`+meetings[i]['location']+`</td>    
                                        <td>`+meetings[i]['latitude']+`</td>    
                                        <td>`+meetings[i]['longitude']+`</td>    
                                        <td>`+meetings[i]['meeting_time']+`</td>    
                                        <td>`+meetings[i]['distance_time']+`</td>    
                                        <td>`+meetings[i]['distance_km']+` KM</td>    
                                        <td>`+meetings[i]['date']+`</td>    
                                    </tr>
                                `;                                
                            }
                        } else {
                            html += `
                                <tr>
                                    <td colspan="9">No Meetings Found!</td>    
                                </tr>
                            `;
                        }
                        $(".tbody").html(html);
                    }
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
                    var distance_in_km = distance.value / 1000; //distance in kilometers
                    var duration_in_minutes = duration.value / 60;

                    jQuery("#dkm").val(parseInt(distance_in_km));
                    jQuery("#dtime").val(parseInt(duration_in_minutes));
                }
            }
        }
    </script>
@endsection

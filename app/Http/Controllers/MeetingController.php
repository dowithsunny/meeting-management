<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use Illuminate\Http\Request;

class MeetingController extends Controller
{
    public function addMeeting(Request $request){
     $meeting = New Meeting;
     $meeting->user_id = $request->user_id;
     $meeting->location = $request->location;
     $meeting->latitude = $request->latitude;
     $meeting->longitude = $request->longitude;
     $meeting->meeting_time = $request->time;
     $meeting->distance_time = $request->dtime;
     $meeting->distance_km = $request->dkm;
     $meeting->date = $request->date;
     $meeting->save();

     return back()->with("Success","Meeting schedule with client added successfully!");
    }
}

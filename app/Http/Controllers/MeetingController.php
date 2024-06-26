<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\UserController;

class MeetingController extends Controller
{
    public function addMeeting(Request $request)
    {
        $minutes = Meeting::select(DB::raw("SUM(meeting_time) as total_time"))
            ->where([
                'user_id' => $request->user_id,
                'date' => $request->date,
            ])->get();

        $currentUserMinutes = 9 * 60;

        if ($minutes[0]['total_time'] >= $currentUserMinutes) {
            return back()->with("error", "On this date " . $request->date . " all schedule are busy, please select another available date.");
        } elseif (($minutes[0]['total_time'] + $request->time) > $currentUserMinutes) {
            $currentRemMinutes = $currentUserMinutes - $minutes[0]['total_time'];
            return back()->with("error", "On this date " . $request->date . " you've only " . $currentRemMinutes . " mintues remain for meeting.");
        } else {
            $meeting = new Meeting;
            $meeting->user_id = $request->user_id;
            $meeting->location = $request->location;
            $meeting->latitude = $request->latitude;
            $meeting->longitude = $request->longitude;
            $meeting->meeting_time = $request->time;
            $meeting->distance_time = $request->dtime;
            $meeting->distance_km = $request->dkm;
            $meeting->date = $request->date;
            $meeting->save();

            return back()->with("Success", "Meeting schedule with client added successfully!");
        }
    }

    public function getDateMeetings(Request $request)
    {
        $userCont = new UserController;
        $tableData = Meeting::where('date', $request->date)->get();

        $uInfo = @unserialize(file_get_contents("http://ip-api.com/php"));

            $lat = $uInfo['lat'];
            $lang = $uInfo['lon'];

            $meetings = [];

            foreach($tableData as $data)
            {
                $km = $userCont->calculateDistance($lat, $lang, $data->latitude, $data->longitude);
                $data['current_km'] = ceil($km['kilometers']);
                $meetings[] = $data;
            }
            $key = array_column($meetings, 'current_km');
            
            array_multisort($key, SORT_ASC, $meetings);

        return response()->json(['meetings' => $meetings]);
    }
}

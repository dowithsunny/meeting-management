<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class UserController extends Controller
{
    public function loadRegister()
    {
        if (Auth::check()) {
            return view('/home');
        }
        return view('register');
    }

    public function userRegister(Request $request)
    {
        $request->validate([
            "name" => 'string|required|min:1',
            "email" => 'string|required|email|max:100|unique:users',
            "password" => 'string | required | min:6 | confirmed'
        ]);

        $user = new User;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();

        return back()->with('success', 'Your Registration has been Success!');
    }

    public function loadLogin()
    {
        if (Auth::check()) {
            return view('/home');
        }
        return view('login');
    }

    public function userLogin(Request $request)
    {
        $request->validate([
            "email" => 'required|email',
            "password" => 'required '
        ]);

        $userCredential = $request->only('email', 'password');


        if (Auth::attempt($userCredential)) {
            return redirect('/home');
        } else {
            return back()->with('error', 'Email or Password is incorrect!');
        }
    }

    public function home()
    {
        if (Auth::check()) {
            $tableData = Meeting::where('user_id', Auth::id())->get();

            $uInfo = @unserialize(file_get_contents("http://ip-api.com/php"));

            $lat = $uInfo['lat'];
            $lang = $uInfo['lon'];

            $meetings = [];

            foreach ($tableData as $data) {
                $km = $this->calculateDistance($lat, $lang, $data->latitude, $data->longitude);
                $data['current_km'] = ceil($km['kilometers']);
                $meetings[] = $data;
            }

            $key = array_column($meetings, 'current_km');
            
            array_multisort($key, SORT_ASC, $meetings);

            return view('home', compact('meetings'));
        } 
        else {
            return redirect('/');
        }
    }

    public function logout(Request $request)
    {
        $request->session()->flush();
        Auth::logout();
        return redirect('/');
    }

    function calculateDistance($lat1, $long1, $lat2, $long2)
    {
        $theta = $long1 - $long2;
        $miles = (sin(deg2rad($lat1))) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));

        $miles = acos($miles);
        $miles = rad2deg($miles);

        $result['miles'] = $miles * 60 * 1.1515;
        $result['kilometers'] = $result['miles'] * 1.609344;

        return $result;
    }
}

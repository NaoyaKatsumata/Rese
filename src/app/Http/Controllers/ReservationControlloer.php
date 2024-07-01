<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;

class ReservationControlloer extends Controller
{
    public function done(Request $request){
        // dd($request);
        $userId = $request->userId;
        $shopId = $request->shopId;
        $strnum = $request->num;
        $num = preg_replace("/[^0-9]/", "", $strnum);
        $date = $request->date;
        $time = $request->time;
        Reservation::create([
            'user_id'=>$userId,
            'shop_id'=>$shopId,
            'reservation_number'=>$num,
            'reservation_date'=>$date.' '.$time,
        ]);
        // dd($request);
        return view('done',['userId'=>$userId]);
    }
}
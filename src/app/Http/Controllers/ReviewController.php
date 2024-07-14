<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\User;
use App\Models\Reservation;
use App\Models\Shop;

class ReviewController extends Controller
{
    public function store(Request $request){
        // dd($request);
        $userId=$request->userId;
        $shopId=$request->shopId;
        $review=$request->review;
        $comment=$request->comment;
        $userName = User::select('name')
        ->where("id","=",$userId)
        ->first();

        $reviewed = Review::where("user_id","=",$userId)
        ->where("shop_id","=",$shopId)
        ->first();
        // dd($reviewed);

        if(is_null($reviewed)){
            Review::create([
                'user_id'=>$userId,
                'shop_id'=>$shopId,
                'review'=>$review,
                'comment'=>$comment,
            ]);
        }else{
            $reviewed->update([
                'user_id'=>$userId,
                'shop_id'=>$shopId,
                'review'=>$review,
                'comment'=>$comment,]);
        }

        $reservations = Reservation::join("shops","shops.id","=","reservations.shop_id")
        ->where("user_id","=",$userId)
        ->orderBy('reservation_date','asc')
        ->orderBy('reservation_time','asc')
        ->get();
        $favorites = Shop::join("favorites","shops.id","=","favorites.shop_id")
        ->join("areas","areas.id","=","shops.area_id")
        ->join("categories","categories.id","=","shops.category_id")
        ->where("user_id","=",$userId)
        ->get();

        // dd($userName,$reservations,$favorites);
        return view('mypage',['userName'=>$userName,'reservations'=>$reservations,'favorites'=>$favorites]);
    }
}
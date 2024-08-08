<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shop;
use App\Models\User;
use App\Models\Favorite;
use App\Models\Area;
use App\Models\Category;
use App\Models\Reservation;
use App\Models\Owner;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ShopController extends Controller
{
    public function shopAll(){
        Auth::guard('web')->logout();
        $shops = Shop::all();
        $areas = Area::all();
        $categories = Category::all();
        return view('allshop',['shops'=>$shops,'areas'=>$areas,'categories'=>$categories,'selectedArea'=>'','selectedCategory'=>'']);
    }

    public function favorite(Request $request){
        // dd($request);
        $userId = $request->userId;
        $shopId = $request->shopId;
        $selectedArea = $request->area;
        $selectedCategory = $request->category;
        $owners = Owner::where("user_id","=",$userId)
        ->get();
        $request->session()->regenerateToken();
        if($request->area === 'All shop'){
            $areaId = Area::all();
        }else{
            $areaId = Area::where("area_name","=",$request->area)
            ->first();
        }
        $categoryName = $request->category_name;
        //リクエストされた店舗がお気に入り可動か取得
        $userFavorite = Favorite::where('user_id','=',$userId)
        ->where('shop_id','=',$shopId)
        ->first();
        //お気に入り登録か削除か判定&SQL実行
        if(!(is_null($shopId))){
            if(is_null($userFavorite)){
            //お気に入り登録処理
                Favorite::create([
                    'user_id' => $userId ,
                    'shop_id'=> $shopId
                ]);
            }else{
                //お気に入り削除処理
                $userFavorite->delete();
            }
        }
        //Userのお気に入りの店舗取得
        $userFavorites = User::select('users.id','favorites.shop_id')
        ->where('users.id','=',$userId)
        ->join('favorites','users.id','=','favorites.user_id')
        ->get();
        //全店舗取得
        if(!isset($areaId) or $request->area === 'All shop'){
            $shops = Shop::all();
        }else{
            $shops = Shop::where("area_id","=",$areaId->id)
            ->get();
        }
        //全エリア取得
        $areas = Area::all();
        //全カテゴリー取得
        $categories = Category::all();
        return view('allshop',['userFavorites'=>$userFavorites,'shops'=>$shops,'areas'=>$areas,'categories'=>$categories,'selectedArea'=>$selectedArea,'selectedCategory'=>$selectedCategory,'owners'=>$owners]);
    }

    public function search(Request $request){
        $userId = $request->userId;
        $selectedArea = $request->area;
        $selectedAreaId = Area::where("area_name","=",$selectedArea)
        ->first();
        $selectedCategory = $request->category;
        $selectedCategoryId = Category::where("category_name","=",$selectedCategory)
        ->first();
        $selectedShop = $request->shopName;
        if(isset($selectedAreaId)){
            $shops = Shop::where("area_id","=",$selectedAreaId->id)
            ->get();
        }
        if(isset($selectedCategoryId)){
            $shops = Shop::where("category_id","=",$selectedCategoryId->id)
            ->get();
        }
        if(isset($selectedShop)){
            $shops = Shop::where("shop_name","like","%".$selectedShop."%")
            ->get();
        }
        if(!isset($shops)){
            $shops = Shop::all();
        }
        $areas = Area::all();
        $categories = Category::all();
        $userFavorites = Favorite::where('user_id','=',$userId)
        ->get();
        return view('allshop',['shops'=>$shops,'areas'=>$areas,'categories'=>$categories,'userFavorites'=>$userFavorites,'selectedArea'=>$selectedArea,'selectedCategory'=>$selectedCategory,'selectedShop'=>$selectedShop]);
    }

    public function detail(Request $request){
        $shopId = $request->shopId;
        $errStatus=false;

        $shop = Shop::where("id","=",$shopId)
        ->first();
        $selectedArea = Shop::join("areas","shops.area_id","=","areas.id")
        ->where("shops.id","=",$shopId)
        ->first();
        $areas = Area::all();

        $selectedCategory = Shop::join("categories","shops.category_id","=","categories.id")
        ->where("shops.id","=",$shopId)
        ->first();
        $categories = Category::all();

        $nowOwner = Owner::join("users","users.id","=","owners.user_id")
        ->where("owners.shop_id","=",$shopId)
        ->orderBy('owners.created_at','desc')
        ->first();
        $owners = User::where("authority","=","2")
        ->get();

        $reservationDate = date("Y/m/d");
        $reservationList = Reservation::join("shops","shops.id","=","reservations.shop_id")
        ->join("users","users.id","=","reservations.user_id")
        ->where("shop_id","=",$shopId)
        ->where("reservation_date",">=",$reservationDate)
        ->get();

        return view('detail',['shop'=>$shop,'selectedArea'=>$selectedArea,'selectedCategory'=>$selectedCategory,'owners'=>$owners,'areas'=>$areas,'categories'=>$categories,'reservationList'=>$reservationList,'errStatus'=>$errStatus,'nowOwner'=>$nowOwner]);
    }
}

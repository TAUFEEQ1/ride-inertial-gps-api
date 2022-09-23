<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Estimator;
use App\Http\Helpers\ETL;
use App\Models\Location;
use Laravel\Lumen\Http\Request;

class LocationController extends Controller
{
    //
    public function record(Request $request){
        $pin = $request->json('point');
        list($lat,$lon,$time) = $pin;
        Location::create(['lat'=>$lat,'lng'=>$lon,'recorded_at'=>$time]);

        $etl = new ETL;
        $etl->load();
        if($etl->count()<3){
            return response('Not Ready',202);
        }

        $locations = $etl->transform();
        $estimator = new  Estimator($locations);
        return $estimator->speed();

    }
    public function clear(){
        Location::truncate();
        return "Operation Successul";
    } 
}

<?php
namespace App\Http\Helpers;
use App\Models\Location;
use Location\Coordinate;
use Location\Distance\Vincenty;
use DateTime;
use Illuminate\Support\Facades\Log;

class ETL{
    public function __construct()
    {
        $this->calculator = new Vincenty(); 
        $prime = Location::orderBy('recorded_at','asc')->first();
    
        $this->ref_time = new DateTime($prime->recorded_at);
        $this->ref_point = [$prime->lat,$prime->lng];
    }
    public function load(){
        $this->locations = Location::orderBy('recorded_at','asc')->skip(1)->take(20)->get();
    }
    public function count(){
    
        return $this->locations->count();
    }
    public function transform(){
        return $this->locations->map(function($location){
            $current_date = new DateTime($location->recorded_at);
            $since_start = $current_date->getTimestamp() - $this->ref_time->getTimestamp();
            $location->duration = $since_start;
            list($dep_lat,$dep_lon) = $this->ref_point;
            $c1 = new Coordinate($dep_lat, $dep_lon);
            $c2 = new Coordinate($location->lat,$location->lng);
            $distance = $c1->getDistance($c2,$this->calculator);
            $location->distance = $distance;
            return $location;
        });
    }
}
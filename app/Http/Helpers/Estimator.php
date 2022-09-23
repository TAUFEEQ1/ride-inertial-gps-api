<?php
namespace App\Http\Helpers;

use Illuminate\Support\Facades\Log;

use Phpml\Regression\LeastSquares;

class Estimator{
    public function __construct($locations)
    {
        $this->locations = $locations;
    }
    private function average(){
        $speeds = $this->locations->map(function ($location){
            return $location->distance/$location->duration;
        });
        $average_speed = $speeds->avg();
        return ['coeff'=>$average_speed];
    }
    private function regeress(){
        $times = $this->locations->map(function ($location){
            return [$location->duration];
        });
        $distances = $this->locations->pluck('distance')->all();
        $regression = new LeastSquares();
        Log::info($times);
        Log::info($distances);
        $regression->train($times->toArray(),$distances);
        $intercept = $regression->getIntercept();
        $coeffs = $regression->getCoefficients();
        return ['intercept'=>$intercept,'coeff'=>$coeffs[0]];        
    }
    public function speed(){
        if($this->locations->count() < 7){
            return $this->average();
        }
        return $this->regeress();
    }
}
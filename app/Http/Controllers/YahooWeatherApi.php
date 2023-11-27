<?php
 
namespace App\Http\Controllers;
 
use Illuminate\Http\Request;

class YahooWeatherApi
{
    private $api_key;

    private $location;
    private $lat;
    private $lon;

    public function __construct()
    {
        $this->api_key = config('const.yahoo_client_id');
        $config_address = config('const.home_address');

        $locations = self::FetchLocationData($config_address);
        $this->location = $locations['name'];
        $this->lat = $locations['lat'];
        $this->lon = $locations['lon'];
    }

    public function GetLocation()
    {
        return $this->location;
    }

    public function GetLat()
    {
        return $this->lat;
    }

    public function GetLon()
    {
        return $this->lon;
    }

    public function GetRainNowAndForecast()
    {
        return self::GetRain();
    }

    public function GetRainForecast()
    {
        $list = self::GetRain();

        foreach ($list as $i => $w)
        {
            if ($w['type'] != 'forecast')
            {
                unset($list[$i]);
            }
        }

        return $list;
    }

    private function GetRain()
    {
        $weather_array = self::FetchRainData();

        //降水強度実測値を変数に格納
        $date = $weather_array["Feature"]["0"]["Property"]["WeatherList"]["Weather"]["0"]["Date"];
        $rainfall=$weather_array["Feature"]["0"]["Property"]["WeatherList"]["Weather"]["0"]["Rainfall"];

        $weatherList =  $weather_array["Feature"]["0"]["Property"]["WeatherList"]["Weather"];

        $list = [];
        foreach ($weatherList as $w)
        {
            $date_string = $w['Date'];                        
            $date = strtotime($date_string);
            $now = time();
            $time = (int)date('i', $date - $now).'分後';

            $listtemp = array('type' => $w['Type'], 
                              'date' => $w['Date'], 
                              'time_mm' => $time, 
                              'location' => $this->location,
                              'rainfall' => (ceil($w['Rainfall'] * 10) / 10),   // 小数点第２位を切り上げ 
                              'rainfall_color' => Color::GetRainfallColor($w['Rainfall']));
            $list[] = $listtemp;
        }

        return $list;
    }

    private function FetchRainData()
    {
        $coordinate_string = $this->lon . "," . $this->lat;

        $url = 'https://map.yahooapis.jp/weather/V1/place?' 
                . 'appid=' . $this->api_key 
                . '&coordinates=' . $coordinate_string
                . '&output=' . 'json';

        $weather_json = file_get_contents($url);
        $weather_array = json_decode($weather_json, true);
        return $weather_array;
    }

    private function FetchLocationData($address)
    {
        $url = "https://map.yahooapis.jp/geocode/V1/geoCoder?"
                . "appid=" . $this->api_key
                . "&query=" . urlencode($address)
                . "&output=json&recursive=true";

        $contents = file_get_contents($url);
        $contents = json_decode($contents);

        $Coordinates = $contents ->Feature[0]->Geometry->Coordinates;
        $name = $contents ->Feature[0]->Name;
        $geo = explode(",", $Coordinates);

        $lat = $geo[1];
        $lon = $geo[0];

        $list = array('lat'=>$lat, 'lon'=>$lon, 'name'=>$name);

        return $list;
    }

}
<?php
 
namespace App\Http\Controllers;
 
use Illuminate\Http\Request;

use App\Models\Location;//追記
use App\Models\Coordinate;//追記
use App\Models\Weather;
use App\Models\Rain;


use DateTime;
use DateTimeZone;
use DateInterval;
 
class LocationController extends Controller
{
    private $config_location;

    private $location;
    private $lat;
    private $lon;

    public function __construct()
    {
        $config_location = config('const.home_address');

        // 住所
        $db_location = new Location($config_location);
        if ($db_location->ExistsData())
        {
            $this->location = $db_location->GetLocation();
        } else {
            $yahoo_weather_api = new YahooWeatherApi;
            $this->location = $yahoo_weather_api->GetLocation();

            $db_location->SetLocation($this->location);
        }

        // 緯度経度
        $db_coordinate = new Coordinate($this->location);
        if ($db_coordinate->ExistsData())
        {
            $this->lat = $db_coordinate->GetLat();
            $this->lon = $db_coordinate->GetLon();
        } else {
            $yahoo_weather_api = new YahooWeatherApi;
            $this->lat = $yahoo_weather_api->GetLat();
            $this->lon = $yahoo_weather_api->GetLon();

            $db_coordinate->SetData($this->lat, $this->lon);
        }

        // $this->lat = $db_coordinate->GetLat();
        // $this->lon = $db_coordinate->GetLon();

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
}
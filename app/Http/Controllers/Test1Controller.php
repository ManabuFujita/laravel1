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
 
class Test1Controller extends Controller
{
    private $location;

    // public function index(Request $request)
    // {
    //     $lists = Test1::all();

    //     return view('test1.index')->with('lists', $lists);
    // }


    public function index2(Request $request)
    {
        // 位置情報 -----------------------------------------------------------
        $lc = new LocationController;
        $this->location = $lc->GetLocation();
        $lat = $lc->GetLat();
        $lon = $lc->GetLon();



        // yahoo -------------------------------------------------------------
        $yahoo_weather = new YahooWeatherApi;


        // １時間降水量を取得
        $weather_rain = $yahoo_weather->GetRainForecast();
        
        // print_r($weather_rain);

        $list = [];
        foreach ($weather_rain as $i => $w)
        {
            $list[] = array('datetime' => $w['date'], 'rainfall' => $w['rainfall']);
        }

        // DB登録
        $db_rain = new Rain($this->location);
        $db_rain->SetData($list);

        // 表示用データ作成
        $now = new DateTime();

        $weather_rain = [];
        if ($db_rain->IsRainForecast())
        {
            $rain_data = $db_rain->GetData();

            foreach ($rain_data as $r)
            {
                $db_datetime = DateTime::createFromFormat('Y-m-d H:i:s', $r['datetime']);

                $weather_rain[] = array('time_mm' => $now->diff($db_datetime, true)->format('%i 分後'), 
                                        'rainfall' => $r['rainfall'],
                                        'rainfall_color' => Color::GetRainfallColor($r['rainfall']));
            }
        } else {
            $weather_rain = [];
        }

        // switch bot --------------------------------------------------------
        $swbt = new SwitchBotApi;
        // $swbt_devices = $swbt->GetDevices();


        $swbt_lists = $swbt->ShowSwitchBotMeter();


        // openWeather ------------------------------------------------------

        // $openweather = new OpenWeatherApi;

        $wc = new WeatherController;
        $weather_forecast = $wc->Get3hForecastData();
        $weather_from_today = $wc->Get3hForecastDataFromToday();

        return view('test1.index', compact('swbt_lists', 'weather_rain', 'weather_forecast', 'weather_from_today'));
    }

}

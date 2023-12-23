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

    private $weather_rain;
    private $swbt_lists;
    private $weather_forecast;
    private $weather_from_today;

    // public function index(Request $request)
    // {
    //     $lists = Test1::all();

    //     return view('test1.index')->with('lists', $lists);
    // }


    public function index2(Request $request)
    {
        self::getAllData();

        $swbt_lists = $this->swbt_lists;
        $weather_rain = $this->weather_rain;
        $weather_forecast = $this->weather_forecast;
        $weather_from_today = $this->weather_from_today;

        return view('test1.index', compact('swbt_lists', 'weather_rain', 'weather_forecast', 'weather_from_today'));
    }

    public function getTemp(Request $request) 
    {
        self::getSwitchbotData();

        return json_encode($this->swbt_lists);
    }

    private function getAllData() 
    {
        // 位置情報 -----------------------------------------------------------
        self::getLocationData();

        // yahoo 雨雲-------------------------------------------------------------
        self::getRainData();

        // switch bot --------------------------------------------------------
        self::getSwitchbotData();

        // openWeather ------------------------------------------------------
        self::getWeatherData();
    }

    private function getLocationData() 
    {
        $lc = new LocationController;
        $this->location = $lc->GetLocation();
        // $lat = $lc->GetLat();
        // $lon = $lc->GetLon();        
    }

    private function getRainData() 
    {
        $yahoo_weather = new YahooWeatherApi;

        // １時間降水量を取得
        $rain = $yahoo_weather->GetRainForecast();

        $list = [];
        foreach ($rain as $i => $w)
        {
            $list[] = array('datetime' => $w['date'], 'rainfall' => $w['rainfall']);
        }

        // DB登録
        $db_rain = new Rain($this->location);
        $db_rain->SetData($list);

        // 表示用データ作成
        $now = new DateTime();

        $this->weather_rain = [];
        if ($db_rain->IsRainForecast())
        {
            $rain_data = $db_rain->GetData();

            foreach ($rain_data as $r)
            {
                $db_datetime = DateTime::createFromFormat('Y-m-d H:i:s', $r['datetime']);

                $this->weather_rain[] = array('time_mm' => $now->diff($db_datetime, true)->format('%i 分後'), 
                                        'rainfall' => $r['rainfall'],
                                        'rainfall_color' => Color::GetRainfallColor($r['rainfall']));
            }
        } else {
            $this->weather_rain = [];
        }
    }

    private function getSwitchbotData() 
    {
        $swbt = new SwitchBotApi;
        $this->swbt_lists = $swbt->ShowSwitchBotMeter();        
    }

    private function getWeatherData() 
    {
        $wc = new WeatherController;

        // 3時間天気予報用
        $this->weather_forecast = $wc->Get3hForecastData();

        // グラフ用
        // $weather_from_today = $wc->Get3hForecastDataFromToday();
        $this->weather_from_today = $wc->Get3hForecastDataFrom('-1 days');
    }


}


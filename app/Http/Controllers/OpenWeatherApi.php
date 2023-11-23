<?php
 
namespace App\Http\Controllers;
 
use Illuminate\Http\Request;
use App\Http\Controllers\LocationController;

use DateTime;
use DateTimeZone;
use DateInterval;

class OpenWeatherApi
{
    private $api_key;
    private $lat;
    private $lon;

    public function __construct()
    {
        $this->api_key = config('const.open_weather_key');

        $lc= new LocationController;
        $this->lat = $lc->GetLat();
        $this->lon = $lc->GetLon();
    }

    function GetCurrentData()
    {
        $weather_array = self::FetchCurrentData();

        //必要情報を変数に格納
        $weather = $weather_array["weather"]["0"]["main"];
        $temp = $weather_array["main"]["temp"];
        $temp_min = $weather_array["main"]["temp_min"];
        $temp_max = $weather_array["main"]["temp_max"];
        $cloud = $weather_array["clouds"]["all"];

        // $list = array('lat'=>$lat, 'lon'=>$lon, 'name'=>$name);
        // return $list;
    }

    function GetForecastData()
    {
        $weather_array = self::FetchForecastData();

        $list = [];
        //必要情報を変数に格納
        $data = $weather_array['list'];
        foreach ($data as $d)
        {
            $utc = $d['dt'];
            $t = new DateTime();
            $t->setTimestamp($utc)->setTimezone(new DateTimezone('Asia/Tokyo'));

            $date = $t->format('Y/m/d H:i');
            $time = $t->format('H:i');

            $now = new DateTime();

            if ($t->format('Y/m/d') == $now->format('Y/m/d'))
            {
                $date_ja = '今日';
            }
            
            // if ($t->format('Y/m/d') == $now->add(new DateInterval('P1D'))->format('Y/m/d'))
            if ($t->format('Y/m/d') == $now->add(DateInterval::createFromDateString('1 day'))->format('Y/m/d'))
            {
                $date_ja = '明日';
            }
            
            // echo $date;
            // echo $date_ja;
            // echo '<br>';

            // echo '<br>';
            // echo "日時:" . $date . "\n";
            // // echo "、現在気温:" . $list['main']['temp'] . "℃\n";
            // echo "、最高気温:" . $d['main']['temp_max'] . "℃\n";
            // echo "、最低気温:" . $d['main']['temp_min'] . "℃\n";

            // echo "、風:" . $d['wind']['speed'] . "mm\n";
            // // echo "、雨:" . $list['rain']['3h'] . "mm\n";
            // echo "、天気:" . $d['weather'][0]['main'] . "\n";
            // echo "、天気詳細:" . $d['weather'][0]['description'] . "\n";
            if (array_key_exists('rain', $d))
            {
                $rain = $d['rain']['3h'];
            } else {
                $rain = 0;
            }

            $list[] = array('date' => $date, 
                            'date_ja' => $date_ja,
                            'time' => $time,
                            'weather1' => $d['weather'][0]['main'],
                            'weather2' => $d['weather'][0]['description'],
                            'temp' => round($d['main']['temp'], 0), // 小数点第1位を四捨五入
                            'rain' => (ceil($rain * 10) / 10),  // 小数点第２位を切り上げ
                            'wind' => $d['wind']['speed'],
                            'wind_int' => (int)$d['wind']['speed']);
        }
        // $list = array('lat'=>$lat, 'lon'=>$lon, 'name'=>$name);
        return $list;
    }

    private function FetchCurrentData()
    {
        $url = 'http://api.openweathermap.org/data/2.5/weather?'
               . 'lat=' . $this->lat 
               . '&lon=' . $this->lon
               . '&appid=' . $this->api_key
               . '&units=metric&lang=ja';

        $weather_json = file_get_contents($url);
        $weather_array = json_decode($weather_json, true);
        
        return $weather_array;
    }

    private function FetchForecastData()
    {
        $url = 'http://api.openweathermap.org/data/2.5/forecast?'
                . 'lat=' . $this->lat 
                . '&lon=' . $this->lon
                . '&appid=' . $this->api_key
                . '&units=metric&lang=ja';

        $weather_json = file_get_contents($url);
        $weather_array = json_decode($weather_json, true);  
        
        return $weather_array;
    }


}
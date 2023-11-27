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
        $data = $weather_array;

        $list = [];
        //必要情報を変数に格納

        $list = self::makeArray('current', $list, $data);

        return $list;
    }

    function GetForecastData()
    {
        $weather_array = self::FetchForecastData();
        $data = $weather_array['list'];

        //必要情報を変数に格納
        $list = [];
        foreach ($data as $d)
        {
            $list = self::makeArray('forecast', $list, $d);
        }

        return $list;
    }

    private function MakeArray($mode, $array, $data)
    {
        $datetime = self::GetDatetime($data);
        $rain = self::GetRain($mode, $data);

        $array[] = array(
            'datetime' => $datetime,
            'weather1' => $data['weather'][0]['main'],
            'weather2' => $data['weather'][0]['description'],
            'temp' => round($data['main']['temp'], 0), // 小数点第1位を四捨五入
            'rain' => (ceil($rain * 10) / 10),  // 小数点第２位を切り上げ
            'wind' => $data['wind']['speed'],
            'pressure' => $data['main']['pressure']
        );

        return $array;
    }

    private function GetDatetime($data)
    {
        $utc = $data['dt'];
        $t = new DateTime();
        $t->setTimestamp($utc)->setTimezone(new DateTimezone('Asia/Tokyo'));

        $datetime = $t->format('Y/m/d H:i').':00';

        return $datetime;
    }

    private function GetRain($mode, $data)
    {
        if (array_key_exists('rain', $data))
        {
            if ($mode == 'current')
            {
                $rain = $data['rain']['1h'];
            } else {
                $rain = $data['rain']['3h'];
            }
        } else {
            $rain = 0;
        }

        return $rain;
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
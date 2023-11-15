<?php
 
namespace App\Http\Controllers;
 
use Illuminate\Http\Request;
use App\Models\Test1;//追記
 
class Test1Controller extends Controller
{
    public function index(Request $request)
    {
        $lists = Test1::all();

        return view('test1.index')->with('lists', $lists);
    }


    public function index2(Request $request)
    {
        $swbt = new SwitchBotController;
        $devices = $swbt->GetDevices();

        // $lists = SwitchBotController::GetDevices();

        $lists = json_decode($devices, true);

        // echo('<pre>');
        // var_dump($lists);
        // echo('</pre>');

        // $lists = Test1::all();

        // echo('<br>');
        // echo('<br>');
        // echo('<pre>');
        // var_dump($lists);
        // echo('</pre>');

        $lists = $swbt->ShowSwitchBotMeter();

        // echo('<br>');
        // echo('<br>');
        // echo('<pre>');
        // var_dump($ret);
        // echo('</pre>');


        $weather = new YahooWeatherController;
        $weather_ret = $weather->GetRain();



        // return view('test1.index')->with('lists', $ret);
        return view('test1.index', compact('lists', 'weather_ret'));
    }

}

class SwitchBotController
{
    function GetDevices() {     

        $headers = [
          'Content-Type: application/json; charset=utf-8',
          'Authorization: ' . config('const.swbt_token'),
        ];
      
        $options = array(
          'http' => array(
          'method'=> 'GET',
          'header'=> implode("\r\n", $headers),
          )
        );
          
        $context = stream_context_create($options);
        return file_get_contents('https://api.switch-bot.com/v1.0/devices', false, $context);
      }

      function GetDeviceStatus($deviceId) {
      
        $headers = [
          'Content-Type: application/json; charset=utf-8',
          'Authorization: ' . config('const.swbt_token'),
        ];
      
        $options = array(
          'http' => array(
          'method'=> 'GET',
          'header'=> implode("\r\n", $headers),
          )
        );
          
        $context = stream_context_create($options);
        return file_get_contents('https://api.switch-bot.com/v1.0/devices/' . $deviceId . '/status', false, $context);
      }
      
      function SearchDeviceByName($devices, $name) {
        foreach($devices['body']['deviceList'] as $d) {
          if ($d['deviceName'] == $name) {
            return $d['deviceId'];
          }
        }
        return null;
      }
      
    function ShowSwitchBotMeter() {

        $color = new Color;

        $devices = self::GetDevices();
        $devices_list = json_decode($devices, true);


        $list = array();
        
        foreach (config('const.swbt_device_names') as $i => $deviceName)
        {
            $displayName = config('const.swbt_device_dispnames')[$i];   // 表示名を取得

            $deviceId = self::SearchDeviceByName($devices_list, $deviceName);

            $stat = self::GetDeviceStatus($deviceId);
            $stat_list = json_decode($stat, true);
            $temperature = $stat_list['body']['temperature'];
            $humidity = $stat_list['body']['humidity'];

            $listtemp = array('name' => $displayName, 
                              'id' => $deviceId, 
                              'temperature' => $temperature, 
                              't_color' => $color->GetTemperatureColor($temperature),
                              'humidity' => $humidity, 
                              'h_color' => $color->GetHumidityColor($humidity));
            $list[] = $listtemp;


            // array_push($list, $name);
            // $ret .= "デバイス名:" . $name . "、ID:" . $deviceId . "。";
        }

        return $list;
      }
}

class Color
{
    function GetTemperatureColor($temperature)
    {
        $r = 155;
        $g = 200;
        $b = 200;

        $t = $temperature;


        $mean = 15;

        if ($t > $mean)
        {
            $max = 35;
            $min = $mean;

            $t = min($max, $t); // 温度の最大値は35℃
            $t = max($min, $t);    // 温度の最小値は0℃
            $t_per = ($t - $min) / ($max - $min);   // 正規化（0～1）

            $r = 255;
            $g = 255 - 255 * $t_per;
            $b = 255 - 255 * $t_per;
        } else {
            $max = $mean;
            $min = 0;

            $t = min($max, $t); // 温度の最大値は35℃
            $t = max($min, $t);    // 温度の最小値は0℃
            $t_per = ($t - $min) / ($max - $min);

            $r = 255 - 255 * $t_per;
            $g = 255 - 255 * $t_per;
            $b = 255;
        }


        $rgb = dechex($r).dechex($b).dechex($b);
        return $rgb;

    }

    function GetHumidityColor($humidity)
    {
        $t = $humidity;

        $max = 80;
        $min = 30;

        $t = min($max, $t);
        $t = max($min, $t);
        $t_per = ($t - $min) / ($max - $min);

        $r = 255 - 255 * $t_per;
        $g = 255 - 255 * $t_per;
        $b = 255;

        $rgb = dechex($r).dechex($b).dechex($b);
        return $rgb;
    }

    function GetRainfallColor($rainfall)
    {
        $t = $rainfall;

        $max = 7;
        $min = 0;

        $t = min($max, $t);
        $t = max($min, $t);
        $t_per = ($t - $min) / ($max - $min);

        $r = 255 - 255 * $t_per;
        $g = 255 - 255 * $t_per;
        $b = 255;

        $rgb = dechex($r).dechex($b).dechex($b);
        return $rgb;
    }

}

class YahooWeatherController
{
    function GetRain()
    {
        $location = self::GetLocation();

        //Yahoo weather APIにて気象情報を取得する
        $api = 'https://map.yahooapis.jp/weather/V1/place?';


        $coordinate_string = $location['lat'].",".$location['lon'];

        //パラメータをセット
        $params = array(
            "appid" => config('const.yahoo_client_id'),
            "coordinates" => $coordinate_string,
            "output" =>  "json",
            "date" => date("YmdHi", strtotime("now")),
        );

        $url = $api . 'appid=' . $params['appid'] . "&coordinates=" . $params["coordinates"] . "&output=" . $params["output"];
        $weather_json = file_get_contents($url);
        $weather_array = json_decode($weather_json, true);

        //降水強度実測値を変数に格納
        $date = $weather_array["Feature"]["0"]["Property"]["WeatherList"]["Weather"]["0"]["Date"];
        $rainfall=$weather_array["Feature"]["0"]["Property"]["WeatherList"]["Weather"]["0"]["Rainfall"];

        $weatherList =  $weather_array["Feature"]["0"]["Property"]["WeatherList"]["Weather"];


        $color = new Color;

        $list = array();
        foreach ($weatherList as $w)
        {
            $date_string = $w['Date'];                        
            $date = strtotime($date_string);
            $now = time();
            $time = (int)date('i', $date - $now).'分後';


            $listtemp = array('type' => $w['Type'], 
                              'date' => $w['Date'], 
                              'time_mm' => $time, 
                              'location' => $location['name'],
                              'rainfall' => $w['Rainfall'], 
                              'rainfall_color' => $color->GetRainfallColor($w['Rainfall']));
            $list[] = $listtemp;
        }


        return $list;

    }

    function GetLocation()
    {
        //住所（梅田スカイビル）を入れて緯度経度を求める。
        // $address = "大阪府大阪市北区大淀中１丁目１−８７";
        $address = config('const.home_address');
        $apikey = config('const.yahoo_client_id');
        $address = urlencode($address);
        $url = "https://map.yahooapis.jp/geocode/V1/geoCoder?output=json&recursive=true&appid=" . $apikey . "&query=" . $address ;
        $contents = file_get_contents($url);
        $contents = json_decode($contents);

        $Coordinates = $contents ->Feature[0]->Geometry->Coordinates;
        $name = $contents ->Feature[0]->Name;
        $geo = explode(",", $Coordinates);

        $lat = $geo[0];
        $lon = $geo[1];


        $list = array('lat'=>$lat, 'lon'=>$lon, 'name'=>$name);
        return $list;
    }

}
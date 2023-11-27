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

        // 緯度経度を取得
        // $yahoo_location_list = $yahoo_weather->GetLocation();
        // $this->location = $yahoo_weather->GetLocation();
        // $lat = $yahoo_weather->GetLat();
        // $lon = $yahoo_weather->GetLon();

        // DB登録
        // $db_location = new Coordinate();
        // $db_location->SetData($this->location, $lat, $lon);

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

        $openweather = new OpenWeatherApi;
        // $openweather->GetCurrentData();
        // $weather_forecast = $openweather->GetForecastData();
        // $weather_current = $openweather->GetCurrentData();

        // echo '<pre>';
        // print_r($weather_current);
        // echo '</pre>';

        $wc = new WeatherController;
        $weather_forecast = $wc->Get3hForecastData();
        $weather_from_today = $wc->Get3hForecastDataFromToday();

        // $list = [];
        // foreach ($weather_forecast as $i => $w)
        // {
        //     $list[] = array('datetime' => $w['datetime'], 
        //                     'weather1' => $w['weather1'],
        //                     'weather2' => $w['weather2'],
        //                     'temperature' => $w['temp'],
        //                     'rainfall' => $w['rain'],
        //                     'wind' => $w['wind']
        //                 );
        // }

        // $list[] = array('date' => $date, 
        // 'date_ja' => $date_ja,
        // 'time' => $time,
        // 'weather1' => $d['weather'][0]['main'],
        // 'weather2' => $d['weather'][0]['description'],
        // 'temp' => round($d['main']['temp'], 0), // 小数点第1位を四捨五入
        // 'rain' => (ceil($rain * 10) / 10),  // 小数点第２位を切り上げ
        // 'wind' => $d['wind']['speed'],
        // 'wind_int' => (int)$d['wind']['speed']);


        // DB登録
        // $db_rain = new Weather($this->location);
        // $db_rain->SetData($list);


        // return view('test1.index')->with('lists', $ret);
        return view('test1.index', compact('swbt_lists', 'weather_rain', 'weather_forecast', 'weather_from_today'));
    }

}

// class SwitchBot
// {
//     function GetDevices() {     

//         $headers = [
//           'Content-Type: application/json; charset=utf-8',
//           'Authorization: ' . config('const.swbt_token'),
//         ];
      
//         $options = array(
//           'http' => array(
//           'method'=> 'GET',
//           'header'=> implode("\r\n", $headers),
//           )
//         );
          
//         $context = stream_context_create($options);
//         return file_get_contents('https://api.switch-bot.com/v1.0/devices', false, $context);
//     }

//       function GetDeviceStatus($deviceId) {
      
//         $headers = [
//           'Content-Type: application/json; charset=utf-8',
//           'Authorization: ' . config('const.swbt_token'),
//         ];
      
//         $options = array(
//           'http' => array(
//           'method'=> 'GET',
//           'header'=> implode("\r\n", $headers),
//           )
//         );
          
//         $context = stream_context_create($options);
//         return file_get_contents('https://api.switch-bot.com/v1.0/devices/' . $deviceId . '/status', false, $context);
//     }
      
//       function SearchDeviceByName($devices, $name) {
//         foreach($devices['body']['deviceList'] as $d) {
//           if ($d['deviceName'] == $name) {
//             return $d['deviceId'];
//           }
//         }
//         return null;
//     }
      
//     function ShowSwitchBotMeter() {

//         $color = new Color;

//         $devices = self::GetDevices();
//         $devices_list = json_decode($devices, true);


//         $list = array();
        
//         foreach (config('const.swbt_device_names') as $i => $deviceName)
//         {
//             $displayName = config('const.swbt_device_dispnames')[$i];   // 表示名を取得

//             $deviceId = self::SearchDeviceByName($devices_list, $deviceName);

//             $stat = self::GetDeviceStatus($deviceId);
//             $stat_list = json_decode($stat, true);
//             $temperature = $stat_list['body']['temperature'];
//             $humidity = $stat_list['body']['humidity'];

//             $listtemp = array('name' => $displayName, 
//                               'id' => $deviceId, 
//                               'temperature' => $temperature, 
//                               't_color' => $color->GetTemperatureColor($temperature),
//                               'humidity' => $humidity, 
//                               'h_color' => $color->GetHumidityColor($humidity));
//             $list[] = $listtemp;


//             // array_push($list, $name);
//             // $ret .= "デバイス名:" . $name . "、ID:" . $deviceId . "。";
//         }

//         return $list;
//     }
// }

// class Color
// {

//     function hsv2rgb($h, $s, $v) {
//         // 引数処理
//         $h = ($h < 0 ? 360 + fmod($h, 360) : fmod($h, 360)) / 60;
//         $s = max(0, min(1, $s));
//         $v = max(0, min(1, $v));
      
//         // HSV to RGB 変換
//         foreach([5, 3, 1] as $k => $val) {
//           $c[$k] = round(($v - max(0, min(1, 2 - abs(2 - fmod($h + $val, 6)))) * $s * $v) * 255);
//         }
      
//         // 戻り値
//         // return [
//         //     'hex' => sprintf('#%02x%02x%02x', $c[0], $c[1], $c[2]),
//         //     'rgb' => $c,
//         //   'r' => $c[0],
//         //   'g' => $c[1],
//         //   'b' => $c[2],
//         // ];

//         return sprintf('%02x%02x%02x', $c[0], $c[1], $c[2]);
//       }

//     function GetTemperatureColor($temperature)
//     {

//         $h = 0; // 色相
//         $s = 0.7; // 彩度
//         $v = 1; // 輝度

//         $t = $temperature;

//         $max = 35;
//         $min = 0;

//         $t = min($max, $t); // 温度の最大値は35℃
//         $t = max($min, $t);    // 温度の最小値は0℃
//         $t_per = ($t - $min) / ($max - $min);   // 正規化（0～1）

//         $h = 260 * (1 - $t_per);  // 色相（最大を300の青色とする（本当は360まである））
//         return self::hsv2rgb($h, $s, $v);


//         // ----


//         // $r = 155;
//         // $g = 200;
//         // $b = 200;

//         // $t = $temperature;


//         // $mean = 20;

//         // if ($t > $mean)
//         // {
//         //     $max = 35;
//         //     $min = $mean;

//         //     $t = min($max, $t); // 温度の最大値は35℃
//         //     $t = max($min, $t);    // 温度の最小値は0℃
//         //     $t_per = ($t - $min) / ($max - $min);   // 正規化（0～1）

//         //     $r = 255;
//         //     $g = 255 - 255 * $t_per;
//         //     $b = 255 - 255 * $t_per;
//         // } else {
//         //     $max = $mean;
//         //     $min = 0;

//         //     $t = min($max, $t); // 温度の最大値は35℃
//         //     $t = max($min, $t);    // 温度の最小値は0℃
//         //     $t_per = ($t - $min) / ($max - $min);

//         //     $r = 255 * $t_per;
//         //     $g = 255;
//         //     $b = 255;
//         // }


//         // $rgb = str_pad(strval(dechex($r)), 2, 0, STR_PAD_LEFT).dechex($b).dechex($b);
//         // return $rgb;

//     }

//     function GetHumidityColor($humidity)
//     {
//         $t = $humidity;

//         $max = 80;
//         $min = 30;

//         $t = min($max, $t);
//         $t = max($min, $t);
//         $t_per = ($t - $min) / ($max - $min);

//         $r = 255 - 255 * $t_per;
//         $g = 255;
//         $b = 255;

//         $rgb = str_pad(strval(dechex($r)), 2, 0, STR_PAD_LEFT).dechex($g).dechex($b);


//         return $rgb;
//     }

//     function GetRainfallColor($rainfall)
//     {
//         $t = $rainfall;

//         $max = 7;
//         $min = 0;

//         $t = min($max, $t);
//         $t = max($min, $t);
//         $t_per = ($t - $min) / ($max - $min);

//         $r = 255 - 255 * $t_per;
//         $g = 255 - 255 * $t_per;
//         $b = 255;

//         $rgb = dechex($r).dechex($b).dechex($b);
//         return $rgb;
//     }

// }

// class YahooWeather
// {

//     function GetRainForecast()
//     {
//         $list = self::GetRain();

//         $sum_rain = 0;

//         foreach ($list as $i => $w)
//         {
//             if ($w['type'] == 'forecast')
//             {
//                 $sum_rain += $w['rainfall'];
//             } else {
//                 unset($list[$i]);
//             }
//         }

//         if ($sum_rain == 0)
//         {
//             // echo '雨無し';
//             $list = array();
//         }

//         return $list;
//     }

//     function GetRain()
//     {
//         $location = self::GetLocation();

//         //Yahoo weather APIにて気象情報を取得する
//         $api = 'https://map.yahooapis.jp/weather/V1/place?';


//         $coordinate_string = $location['lon'].",".$location['lat'];

//         //パラメータをセット
//         $params = array(
//             "appid" => config('const.yahoo_client_id'),
//             "coordinates" => $coordinate_string,
//             "output" =>  "json",
//             "date" => date("YmdHi", strtotime("now")),
//         );

//         $url = $api . 'appid=' . $params['appid'] . "&coordinates=" . $params["coordinates"] . "&output=" . $params["output"];
//         $weather_json = file_get_contents($url);
//         $weather_array = json_decode($weather_json, true);

//         //降水強度実測値を変数に格納
//         $date = $weather_array["Feature"]["0"]["Property"]["WeatherList"]["Weather"]["0"]["Date"];
//         $rainfall=$weather_array["Feature"]["0"]["Property"]["WeatherList"]["Weather"]["0"]["Rainfall"];

//         $weatherList =  $weather_array["Feature"]["0"]["Property"]["WeatherList"]["Weather"];


//         $color = new Color;

//         $list = array();
//         $sum_rain = 0;
//         foreach ($weatherList as $w)
//         {
//             $date_string = $w['Date'];                        
//             $date = strtotime($date_string);
//             $now = time();
//             $time = (int)date('i', $date - $now).'分後';

//             $listtemp = array('type' => $w['Type'], 
//                               'date' => $w['Date'], 
//                               'time_mm' => $time, 
//                               'location' => $location['name'],
//                               'rainfall' => (ceil($w['Rainfall'] * 10) / 10),   // 小数点第２位を切り上げ 
//                               'rainfall_color' => $color->GetRainfallColor($w['Rainfall']));
//             $list[] = $listtemp;
//         }

//         return $list;

//     }

//     function GetLocation()
//     {
//         $address = config('const.home_address');
//         $apikey = config('const.yahoo_client_id');
//         $address = urlencode($address);
//         $url = "https://map.yahooapis.jp/geocode/V1/geoCoder?output=json&recursive=true&appid=" . $apikey . "&query=" . $address ;
//         $contents = file_get_contents($url);
//         $contents = json_decode($contents);

//         $Coordinates = $contents ->Feature[0]->Geometry->Coordinates;
//         $name = $contents ->Feature[0]->Name;
//         $geo = explode(",", $Coordinates);

//         $lat = $geo[1];
//         $lon = $geo[0];

//         $list = array('lat'=>$lat, 'lon'=>$lon, 'name'=>$name);

//         // DBに登録する
//         // self::insertLocation($list);

//         return $list;
//     }

//     // function insertLocation($list)
//     // {
//     //     $this->db->newData($list);
//     // }

// }

// class OpenWeather
// {

//     function GetCurrentData()
//     {

//         $weather_config = array(
//             'appid' => config('const.open_weather_key'),
//             'lat' => '36.60718688',
//             'lon' => '138.16984275',
//         );
//         $weather_json = file_get_contents('http://api.openweathermap.org/data/2.5/weather?lat=' . $weather_config['lat'] . '&lon=' . $weather_config['lon'] . '&appid=' . $weather_config['appid'] . '&units=metric&lang=ja');
//         $weather_array = json_decode($weather_json, true);


//         //必要情報を変数に格納
//         $weather = $weather_array["weather"]["0"]["main"];
//         $temp = $weather_array["main"]["temp"];
//         $temp_min = $weather_array["main"]["temp_min"];
//         $temp_max = $weather_array["main"]["temp_max"];
//         $cloud = $weather_array["clouds"]["all"];

//         // echo '<br>';
//         // echo "天気" . $weather . "\n";
//         // echo "気温:" . $temp . "\n";
//         // echo "最低気温:" . $temp_min . "\n";
//         // echo "最高気温:" . $temp_max . "\n";
//         // echo "雲量:" . $cloud . "\n";

//         // $list = array('lat'=>$lat, 'lon'=>$lon, 'name'=>$name);
//         // return $list;
//     }

//     function GetForecastData()
//     {

//         $weather_config = array(
//             'appid' => '0c1312a166d422cbdbbf110e1c32bf49',
//             'lat' => '36.60718688',
//             'lon' => '138.16984275',
//         );
//         $weather_json = file_get_contents('http://api.openweathermap.org/data/2.5/forecast?lat=' . $weather_config['lat'] . '&lon=' . $weather_config['lon'] . '&appid=' . $weather_config['appid'] . '&units=metric&lang=ja');
//         $weather_array = json_decode($weather_json, true);


//         $list = array();
//         //必要情報を変数に格納
//         $data = $weather_array['list'];
//         foreach ($data as $d)
//         {
//             $utc = $d['dt'];
//             $t = new DateTime();
//             $t->setTimestamp($utc)->setTimezone(new DateTimezone('Asia/Tokyo'));

//             $date = $t->format('Y/m/d H:i');
//             $time = $t->format('H:i');

//             $now = new DateTime();

//             if ($t->format('Y/m/d') == $now->format('Y/m/d'))
//             {
//                 $date_ja = '今日';
//             }
            
//             // if ($t->format('Y/m/d') == $now->add(new DateInterval('P1D'))->format('Y/m/d'))
//             if ($t->format('Y/m/d') == $now->add(DateInterval::createFromDateString('1 day'))->format('Y/m/d'))
//             {
//                 $date_ja = '明日';
//             }
            
//             // echo $date;
//             // echo $date_ja;
//             // echo '<br>';

//             // echo '<br>';
//             // echo "日時:" . $date . "\n";
//             // // echo "、現在気温:" . $list['main']['temp'] . "℃\n";
//             // echo "、最高気温:" . $d['main']['temp_max'] . "℃\n";
//             // echo "、最低気温:" . $d['main']['temp_min'] . "℃\n";

//             // echo "、風:" . $d['wind']['speed'] . "mm\n";
//             // // echo "、雨:" . $list['rain']['3h'] . "mm\n";
//             // echo "、天気:" . $d['weather'][0]['main'] . "\n";
//             // echo "、天気詳細:" . $d['weather'][0]['description'] . "\n";
//             if (array_key_exists('rain', $d))
//             {
//                 $rain = $d['rain']['3h'];
//             } else {
//                 $rain = 0;
//             }

//             $list[] = array('date' => $date, 
//                             'date_ja' => $date_ja,
//                             'time' => $time,
//                             'weather1' => $d['weather'][0]['main'],
//                             'weather2' => $d['weather'][0]['description'],
//                             'temp' => round($d['main']['temp'], 0), // 小数点第1位を四捨五入
//                             'rain' => (ceil($rain * 10) / 10),  // 小数点第２位を切り上げ
//                             'wind' => $d['wind']['speed'],
//                             'wind_int' => (int)$d['wind']['speed']);
//         }
//         // $list = array('lat'=>$lat, 'lon'=>$lon, 'name'=>$name);
//         return $list;
//     }

// }

// class DB 
// {
//     // protected $db;

//     public function __construct()
//     {
//         $this->db = new Test1;
//     }

//     function NewData($list)
//     {
//         // $db = new Test1;
//         if (! $this->db->ExistsData($list['name']))
//         {
//             $this->db->InsertNewData();
//             // $this->db->create(['location' => $list['name'], 'lat' => $list['lat'], 'lon' => $list['lon']]);
//         }
//     }

//     // function existsData($location)
//     // {
//     //     // return $this->db::where('location', $location)->exists();
//     // }
// }
<?php
 
namespace App\Http\Controllers;
 
use Illuminate\Http\Request;

// use App\Models\Location;//追記
// use App\Models\Coordinate;//追記
use App\Models\Weather;
// use App\Models\Rain;

use App\Http\Controllers\LocationController;

use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use DateInterval;
 
class WeatherController extends Controller
{
    private $location;
    private $data;
    private $db_weather;

    public function __construct()
    {
        // 住所
        $lc = new LocationController;
        $this->location = $lc->GetLocation();

        // open weather
        $this->db_weather = new Weather($this->location);
        if ($this->db_weather->ExistsData())
        {
            $now = new DateTimeImmutable;

            $updated = $this->db_weather->GetUpdatedAt();

            $for_datetime_max = new DateTimeImmutable;
            $for_datetime_max->createFromFormat('Y/m/d H:i:s', $this->db_weather->GetForecastMaxDatetime());


            // 最終更新から1時間経過したら更新する
            if ($now >= $updated->add(DateInterval::createFromDateString('1 hour')))
            {
                self::UpdateDbForecastData();
            }

            //　現在時刻が予報時刻を超えたら更新する
            if ($now > $for_datetime_max)
            {
                self::UpdateDbForecastData();
            }

            // 現在と同時刻の実績データがあれば書き換えない
            if ($this->db_weather->ExistsDataByYYYYMMDDHH('current', $now->format('Y'), $now->format('m'), $now->format('d'), $now->format('H')))
            {

            } else {
                // 現在と同時刻の予報データがあれば書き換える
                if ($this->db_weather->ExistsDataByYYYYMMDDHH('forecast', $now->format('Y'), $now->format('m'), $now->format('d'), $now->format('H')))
                {
                    self::UpdateDbCurrentData();
                }
            }

        } else {
            self::UpdateDbForecastData();
        }

        // DBから取得し直す
        $this->data = $this->db_weather->GetAllData();
    }

    public function Get3hForecastData()
    {

        $now = new DateTimeimmutable();

        $list = [];
        $date_ja_prev = '';
        $pressure_prev = 0;
        $count = 0;
        $datetime_prev = new DatetimeImmutable;

        foreach ($this->data as $i => $d)
        {
            if ($count > 6)
            {
                break;
            }

            $datetime = new DateTimeImmutable($d['datetime']);
            $mode = $d['mode'];

            if ($now->add(DateInterval::createFromDateString('-3 hour')) <= $datetime 
                && $mode == 'current'
                && $count == 0)
            {
                $count++;

                $date_ja = '';
                if ($count == 1)
                {
                    $date_ja = '今日';
                }

                // if ($datetime->format('Y/m/d') == $now->add(DateInterval::createFromDateString('1 day'))->format('Y/m/d'))
                // {
                //     if ($datetime->format('H:i') == '00:00')
                //     {
                //         $date_ja = '明日';
                //     }
                // }
                // $date_ja_prev = $date_ja;
                    
                // if ($count == 1)
                // {
                    $pressure_diff = 0;
                // } else {
                //     $pressure_diff = $d['pressure'] - $pressure_prev;
                // }
                $pressure_prev = $d['pressure'];

                $list = self::MakeArray($list, $datetime, '', $d, $pressure_diff);

                $date_ja_prev = $date_ja;
            }

            if ($now <= $datetime 
                && $mode == 'forecast'
                && $datetime->getTimestamp() > $datetime_prev->getTimestamp())
            {
                $count++;

                $date_ja = '';
                if ($count == 1)
                {
                    $date_ja = '今日';
                }

                if ($datetime->format('Y/m/d') == $now->add(DateInterval::createFromDateString('1 day'))->format('Y/m/d'))
                {
                    if ($datetime->format('H:i') == '00:00')
                    {
                        $date_ja = '明日';
                    }
                }
                // $date_ja_prev = $date_ja;
                    
                if ($count == 1)
                {
                    $pressure_diff = 0;
                } else {
                    $pressure_diff = $d['pressure'] - $pressure_prev;
                }
                $pressure_prev = $d['pressure'];

                $list = self::MakeArray($list, $datetime, '', $d, $pressure_diff);

                $date_ja_prev = $date_ja;
            }
            $datetime_prev = $datetime;
        }

        return $list;
    }

    public function Get3hForecastDataFromToday()
    {
        $today = new DateTimeImmutable();
        $today = DateTimeImmutable::createFromFormat('Y/m/d H:i:s', $today->format('Y/m/d' . ' 00:00:00'));

        $list = [];
        $date_ja_prev = '';
        $pressure_prev = 0;
        $count = 0;
        foreach ($this->data as $i => $d)
        {
            $datetime = new DateTime($d['datetime']);

            if ($today <= $datetime)
            {
                $count++;
                    
                if ($count == 1)
                {
                    $pressure_diff = 0;
                } else {
                    $pressure_diff = $d['pressure'] - $pressure_prev;
                }
                $pressure_prev = $d['pressure'];


                $list = self::MakeArray($list, $datetime, '', $d, $pressure_diff);
            }
        }

        return $list;
    }

    public function GetForecastData()
    {
        $list = [];
        $date_ja_prev = '';
        $pressure_prev = 0;
        foreach ($this->data as $i => $d)
        {

            $now = new DateTime();
            $datetime = new DateTime($d['datetime']);

            $date_ja = '';
            if ($i == 0)
            {
                $date_ja = '今日';
            }
            
            // if ($t->format('Y/m/d') == $now->add(new DateInterval('P1D'))->format('Y/m/d'))
            if ($datetime->format('Y/m/d') == $now->add(DateInterval::createFromDateString('1 day'))->format('Y/m/d'))
            {
                if ($datetime->format('H:i') == '00:00')
                {
                    $date_ja = '明日';
                }
            }
            // $date_ja_prev = $date_ja;
                
            if ($i == 0)
            {
                $pressure_diff = 0;
            } else {
                $pressure_diff = $d['pressure'] - $pressure_prev;
            }
            $pressure_prev = $d['pressure'];

            $list = self::MakeArray($list, $datetime, $date_ja, $d, $pressure_diff);
        }

        return $list;
    }

    private function MakeArray($array, $datetime, $date_ja, $d, $pressure_diff)
    {
        $array[] = array('date' => $datetime->format('Y/m/d H:i'), 
                         'date_ja' => $date_ja,
                         'date_d' => $datetime->format('d'),
                         'date_j' => $datetime->format('j'),
                         'time' => $datetime->format('H:i'),
                         'hour' => $datetime->format('H'),
                         'datetime' => $datetime,
                         'mode' => $d['mode'],
                         'weather1' => $d['weather1'],
                         'weather2' => $d['weather2'],
                         'temp' => round($d['temperature'], 0), // 小数点第1位を四捨五入
                         'rain' => (ceil($d['rainfall'] * 10) / 10),  // 小数点第２位を切り上げ
                         'wind' => $d['wind'],
                         'wind_int' => (int)$d['wind'],
                         'pressure' => $d['pressure'],
                         'pressure_diff' => $pressure_diff
                        );        

        return $array;
    }

    private function UpdateDbForecastData()
    {
        $open_weather_api = new OpenWeatherApi;
        $weather_forecast = $open_weather_api->GetForecastData();

        $this->data = [];
        foreach ($weather_forecast as $i => $w)
        {
            $this->data[] = array('datetime' => $w['datetime'], 
                                  'weather1' => $w['weather1'],
                                  'weather2' => $w['weather2'],
                                  'temperature' => $w['temp'],
                                  'rainfall' => $w['rain'],
                                  'wind' => $w['wind'],
                                  'pressure' => $w['pressure']
                                );
        }

        // DB登録
        $this->db_weather->SetForecastData($this->data);

        // DBから取得し直す
        $this->data = $this->db_weather->GetAllData();
    }

    private function UpdateDbCurrentData()
    {
        $open_weather_api = new OpenWeatherApi;
        $weather_current = $open_weather_api->GetCurrentData();

        $w = $weather_current[0];

        $datetime_3h = new DateTimeImmutable;
        $datetime_3h->createFromFormat('Y/m/d H:i:s', $w['datetime']);

        $this->data = [];
        // foreach ($weather_forecast as $i => $w)
        // {
            $this->data[] = array('datetime' => $datetime_3h->format('Y/m/d H:') . '00:00', 
                                  'weather1' => $w['weather1'],
                                  'weather2' => $w['weather2'],
                                  'temperature' => $w['temp'],
                                  'rainfall' => $w['rain'],
                                  'wind' => $w['wind'],
                                  'pressure' => $w['pressure']
                                );
        // }

        // DB登録
        $this->db_weather->SetCurrentData($this->data);

        // DBから取得し直す
        $this->data = $this->db_weather->GetAllData();
    }

}
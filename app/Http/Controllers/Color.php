<?php
 
namespace App\Http\Controllers;
 
use Illuminate\Http\Request;

class Color
{

    private static function hsv2rgb($h, $s, $v) {
        // 引数処理
        $h = ($h < 0 ? 360 + fmod($h, 360) : fmod($h, 360)) / 60;
        $s = max(0, min(1, $s));
        $v = max(0, min(1, $v));
      
        // HSV to RGB 変換
        foreach([5, 3, 1] as $k => $val) {
          $c[$k] = round(($v - max(0, min(1, 2 - abs(2 - fmod($h + $val, 6)))) * $s * $v) * 255);
        }
      
        // 戻り値
        // return [
        //     'hex' => sprintf('#%02x%02x%02x', $c[0], $c[1], $c[2]),
        //     'rgb' => $c,
        //   'r' => $c[0],
        //   'g' => $c[1],
        //   'b' => $c[2],
        // ];

        return sprintf('%02x%02x%02x', $c[0], $c[1], $c[2]);
      }

    static function GetTemperatureColor($temperature)
    {

        $h = 0; // 色相
        $s = 0.7; // 彩度
        $v = 1; // 輝度

        $t = $temperature;

        $max = 35;
        $min = 0;

        $t = min($max, $t); // 温度の最大値は35℃
        $t = max($min, $t);    // 温度の最小値は0℃
        $t_per = ($t - $min) / ($max - $min);   // 正規化（0～1）

        $h = 260 * (1 - $t_per);  // 色相（最大を300の青色とする（本当は360まである））
        return self::hsv2rgb($h, $s, $v);


        // ----


        // $r = 155;
        // $g = 200;
        // $b = 200;

        // $t = $temperature;


        // $mean = 20;

        // if ($t > $mean)
        // {
        //     $max = 35;
        //     $min = $mean;

        //     $t = min($max, $t); // 温度の最大値は35℃
        //     $t = max($min, $t);    // 温度の最小値は0℃
        //     $t_per = ($t - $min) / ($max - $min);   // 正規化（0～1）

        //     $r = 255;
        //     $g = 255 - 255 * $t_per;
        //     $b = 255 - 255 * $t_per;
        // } else {
        //     $max = $mean;
        //     $min = 0;

        //     $t = min($max, $t); // 温度の最大値は35℃
        //     $t = max($min, $t);    // 温度の最小値は0℃
        //     $t_per = ($t - $min) / ($max - $min);

        //     $r = 255 * $t_per;
        //     $g = 255;
        //     $b = 255;
        // }


        // $rgb = str_pad(strval(dechex($r)), 2, 0, STR_PAD_LEFT).dechex($b).dechex($b);
        // return $rgb;

    }

    static function GetHumidityColor($humidity)
    {
        $t = $humidity;

        $max = 85;
        $min = 35;

        $t = min($max, $t);
        $t = max($min, $t);
        $t_per = ($t - $min) / ($max - $min);

        $r = 255 - 255 * $t_per;
        $g = 255;
        $b = 255;

        $rgb = str_pad(strval(dechex($r)), 2, 0, STR_PAD_LEFT).dechex($g).dechex($b);


        return $rgb;
    }

    static function GetRainfallColor($rainfall)
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
<?php
 
namespace App\Http\Controllers;
 
use Illuminate\Http\Request;

class SwitchBotApi
{
    private $api_key;
    private $devices;

    public function __construct()
    {
        $this->api_key = config('const.swbt_token');
        $this->devices = self::GetDevices();
    }

    public function ShowSwitchBotMeter()
    {
        $list = [];
        foreach (config('const.swbt_device_names') as $i => $deviceName)
        {
            $displayName = config('const.swbt_device_dispnames')[$i];   // 表示名を取得

            $deviceId = self::SearchDeviceIdByName($deviceName);
            $status = self::GetDeviceStatus($deviceId);

            $temperature = $status['body']['temperature'];
            $humidity = $status['body']['humidity'];

            $list[] = array('name' => $displayName, 
                            'id' => $deviceId, 
                            'temperature' => $temperature, 
                            'temperature_color' => Color::GetTemperatureColor($temperature),
                            'humidity' => $humidity, 
                            'humidity_color' => Color::GetHumidityColor($humidity));
        }

        return $list;
    }

    private function GetDevices() 
    {     
        $url = 'https://api.switch-bot.com/v1.0/devices';
        $contents = self::GetContentsFrom($url);

        return json_decode($contents, true);
    }

    private function GetDeviceStatus($deviceId) 
    {
        $url = 'https://api.switch-bot.com/v1.0/devices/' . $deviceId . '/status';
        $contents = self::GetContentsFrom($url);

        return json_decode($contents, true);
    }
      
    private function SearchDeviceIdByName($name) 
    {
        foreach($this->devices['body']['deviceList'] as $d) {
            if ($d['deviceName'] == $name) {
                return $d['deviceId'];
            }
        }
        return null;
    }

    private function GetContentsFrom($url)
    {
        $context = stream_context_create(self::GetOptions());
        return file_get_contents($url, false, $context);
    }

    private function GetOptions()
    {
        $headers = self::GetHeaders();
        return array(
                'http' => array(
                            'method'=> 'GET',
                            'header'=> implode("\r\n", $headers),
                        )
                );
    }

    private function GetHeaders()
    {
        return [
                'Content-Type: application/json; charset=utf-8',
                'Authorization: ' . $this->api_key,
            ];
    }

}
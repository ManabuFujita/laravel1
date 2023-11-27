<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Http\Controllers\YahooWeatherApi;

class Location extends Model
{
    use HasFactory;
    protected $fillable = ['request', 'location']; //保存したいカラム名

    private $address;
    private $location;

    public function __construct($address = [])
    {
        parent::__construct($attributes = []);

        $this->address = $address;
    }

    public function GetLocation()
    {
        return $this->FetchLocationFromDb();
    }

    public function SetLocation($location)
    {
        $this->location = $location;

        self::UpsertData();
    }

    public function ExistsData()
    {
        return $this->where('request', $this->address)->exists();
    }
    // public function GetLat()
    // {
    //     return $this->lat;
    // }

    // public function GetLon()
    // {
    //     return $this->lon;
    // }

    // public function SetData($location, $lat, $lon)
    // {
    //     $this->location = $location;
    //     $this->lat = $lat;
    //     $this->lon = $lon;

    //     self::NewData();
    // }

    // private function NewData()
    // {
    //     if (! $this->ExistsData())
    //     {
    //         $this->InsertData();
    //         // $this->db->create(['location' => $list['name'], 'lat' => $list['lat'], 'lon' => $list['lon']]);
    //     }
    // }


    private function FetchLocationFromDb()
    {
        return $this->where('request', $this->address)->value('location');
    }




    private function UpsertData()
    {
        // $this->create(['location' => $this->location, 'lat' => $this->lat, 'lon' => $this->lon]);

        $this->upsert([
            ['request' => $this->address, 'location' => $this->location]
        ], ['request'], ['location']);
    }



}

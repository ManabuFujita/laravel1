<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Http\Controllers\YahooWeatherApi;

class Coordinate extends Model
{
    use HasFactory;
    protected $fillable = ['location', 'lat', 'lon']; //保存したいカラム名

    private $location;
    private $lat;
    private $lon;

    public function __construct($location = [])
    {
        parent::__construct($attributes = []);

        // $db_location = new Location;
        $this->location = $location;
    }

    public function GetLat()
    {
        return $this->FetchLatFromDb();
    }

    public function GetLon()
    {
        return $this->FetchLonFromDb();
    }

    public function SetData($lat, $lon)
    {
        $this->lat = $lat;
        $this->lon = $lon;

        self::UpsertData();
    }

    public function ExistsData()
    {
        return $this->where('location', $this->location)->exists();
    }

    
    // private function NewData()
    // {
    //     if (! $this->ExistsData())
    //     {
    //         $this->InsertData();
    //         // $this->db->create(['location' => $list['name'], 'lat' => $list['lat'], 'lon' => $list['lon']]);
    //     }
    // }



    private function FetchLatFromDb()
    {
        return $this->where('location', $this->location)->value('lat');
    }

    private function FetchLonFromDb()
    {
        return $this->where('location', $this->location)->value('lon');
    }

    // private function InsertData()
    // {
    //     $this->create(['location' => $this->location, 'lat' => $this->lat, 'lon' => $this->lon]);
    // }


    private function UpsertData()
    {
        // $this->create(['location' => $this->location, 'lat' => $this->lat, 'lon' => $this->lon]);

        $this->upsert([
            ['location' => $this->location, 'lat' => $this->lat, 'lon' => $this->lon]
        ], ['location'], ['lat', 'lon']);
    }

}

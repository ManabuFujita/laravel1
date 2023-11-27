<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use DateTime;
use DateTimeImmutable;

class Weather extends Model
{
    use HasFactory;
    protected $table = 'weathers';
    protected $fillable = ['location', 'no', 'datetime', 'rainfall']; //保存したいカラム名

    private $location;

    public function __construct($location = '')
    {
        parent::__construct($attributes = []);

        $this->location = $location;
    }

    public function GetUpdatedAt()
    {
        return $this->where('location', $this->location)->where('mode', 'forecast')->orderBy('updated_at', 'desc')->first()->value('updated_at');
    }

    public function GetForecastMinDateTime()
    {
        return $this->where('location', $this->location)->where('mode', 'forecast')->orderBy('datetime', 'asc')->first()->value('datetime');
    }

    public function GetForecastMaxDateTime()
    {
        return $this->where('location', $this->location)->where('mode', 'forecast')->orderBy('datetime', 'desc')->first()->value('datetime');
    }

    public function GetCurrentMinDateTime()
    {
        return $this->where('location', $this->location)->where('mode', 'current')->orderBy('datetime', 'asc')->first()->value('datetime');
    }

    public function GetAllData()
    {
        return self::SelectData();
    }

    public function ExistsData()
    {
        return $this->where('location', $this->location)->exists();
    }

    // public function ExistsCurrentData()
    // {
    //     return $this->where('location', $this->location)->where('mode', 'current')->exists();
    // }

    public function ExistsDataByYYYYMMDDHH($mode, $year, $month, $day, $hour)
    {
        $datetime = new DateTime;
        $datetime->setDate($year, $month, $day);
        $datetime->setTime($hour, 0, 0, 0);

        return self::ExistsDataByDatetime($mode, $datetime);
    }

    public function SetForecastData($data)
    {
        self::DeleteYesterdayData();

        self::UpdateData('forecast', $data);
    }

    public function SetCurrentData($data)
    {
        self::UpdateData('current', $data);
    }

    // private

    private function ExistsDataByDatetime($mode, $datetime)
    {
        return $this->where('location', $this->location)->where('mode', $mode)->where('datetime', $datetime->format('Y-m-d H:i:s'))->exists();
    }


    private function SelectData()
    {
        return $this->where('location', $this->location)->orderBy('datetime', 'asc')->get();
    }

    private function deleteYesterdayData()
    {
        $today = new DateTime();
        $today->setTime(0, 0, 0, 0);

        $this->where('location', $this->location)
             ->where('mode', 'forecast')
             ->where('datetime', '<', $today)
             ->delete();

        $this->where('location', $this->location)
             ->where('mode', 'current')
             ->where('datetime', '<', $today)
             ->delete();
    }

    // private function UpdateForecastData($data)
    // {
    //     foreach ($data as $i => $d)
    //     {
    //         $this->upsert([
    //             ['location' => $this->location, 
    //              'no' => 1, 
    //              'mode' => 'forecast',
    //              'datetime' => $d['datetime'], 
    //              'weather1' => $d['weather1'],
    //              'weather2' => $d['weather2'],
    //              'temperature' => $d['temperature'],
    //              'rainfall' => $d['rainfall'],
    //              'wind' => $d['wind'],
    //              'pressure' => $d['pressure']
    //             ],
    //         ], ['location', 'datetime'], ['mode', 'weather1', 'weather2', 'temperature', 'rainfall', 'wind', 'pressure']);
    //     }
    // }

    private function UpdateData($mode, $data)
    {
        foreach ($data as $i => $d)
        {
            $this->upsert([
                ['location' => $this->location, 
                 'no' => 0, 
                 'mode' => $mode,
                 'datetime' => $d['datetime'], 
                 'weather1' => $d['weather1'],
                 'weather2' => $d['weather2'],
                 'temperature' => $d['temperature'],
                 'rainfall' => $d['rainfall'],
                 'wind' => $d['wind'],
                 'pressure' => $d['pressure']
                ],
            ], ['location', 'mode', 'datetime'], ['weather1', 'weather2', 'temperature', 'rainfall', 'wind', 'pressure']);
        }
    }

}

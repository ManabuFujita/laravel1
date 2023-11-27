<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use DateTime;

class Rain extends Model
{
    use HasFactory;
    protected $table = 'rains';
    protected $fillable = ['location', 'no', 'datetime', 'rainfall']; //保存したいカラム名

    private $location;

    public function __construct($location = '')
    {
        parent::__construct($attributes = []);

        $this->location = $location;
    }

    public function SetData($data)
    {
        self::UpdateData($data);

        self::IsRainForecast();
    }

    public function GetData()
    {
        return self::SelectData();
    }

    public function IsRainForecast()
    {
        $sum = $this
               ->selectRaw('SUM(rainfall) AS sum_rainfall')
               ->groupBy('location')
               ->where('location', $this->location)
               ->sole();

        return ($sum->sum_rainfall > 0);
    }

    private function SelectData()
    {
        $list = [];
        for ($i = 1; $i <= 6; $i++)
        {
            $data = $this->where('location', $this->location)->where('no', $i)->sole();
            $list[] = array('datetime' => $data->datetime, 'rainfall' => $data->rainfall);
        }

        return $list;
    }

    private function UpdateData($data)
    {
        $this->upsert([
            ['location' => $this->location, 'no' => 1, 'datetime' => DateTime::createFromFormat('YmdGis', $data[0]['datetime'].'00'), 'rainfall' => $data[0]['rainfall']],
            ['location' => $this->location, 'no' => 2, 'datetime' => DateTime::createFromFormat('YmdGis', $data[1]['datetime'].'00'), 'rainfall' => $data[1]['rainfall']],
            ['location' => $this->location, 'no' => 3, 'datetime' => DateTime::createFromFormat('YmdGis', $data[2]['datetime'].'00'), 'rainfall' => $data[2]['rainfall']],
            ['location' => $this->location, 'no' => 4, 'datetime' => DateTime::createFromFormat('YmdGis', $data[3]['datetime'].'00'), 'rainfall' => $data[3]['rainfall']],
            ['location' => $this->location, 'no' => 5, 'datetime' => DateTime::createFromFormat('YmdGis', $data[4]['datetime'].'00'), 'rainfall' => $data[4]['rainfall']],
            ['location' => $this->location, 'no' => 6, 'datetime' => DateTime::createFromFormat('YmdGis', $data[5]['datetime'].'00'), 'rainfall' => $data[5]['rainfall']]
        ], ['location', 'no'], ['datetime', 'rainfall']);
    }
}

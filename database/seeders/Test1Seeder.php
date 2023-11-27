<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; //追記

class Test1Seeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    DB::table('test1s')->insert(
      [
        [
          'location' => '東京都',
          'created_at' => now(),
          'updated_at' => now(),
        ],
        [
          'location' => '長野市',
          'created_at' => now(),
          'updated_at' => now(),
        ],
        [
          'location' => 'テスト3',
          'created_at' => now(),
          'updated_at' => now(),
        ],
      ]
    );
  }
}
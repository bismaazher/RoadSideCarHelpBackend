<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->addActivity();
    }

    public function addActivity()
    {
       \DB::table('activity')->insert([
            [
                'title'          => 'Exercise',
                'is_other'    => '0',
                'description'    => ' ',
                'status'         => '1',
                'created_at'     => Carbon::now()
            ],[
                'title'         => 'Sports',
               'is_other'    => '0',
                'description'   => ' ',
                'status'        => '1',
                'created_at' => Carbon::now()
            ],[
                'title'         => 'Music Therapy',
               'is_other'    => '0',
                'description'   => ' ',
                'status'        => '1',
                'created_at' => Carbon::now()
            ],[
                'title'         => 'Drinking Water',
                'is_other'    => '0',
                'description'   => ' ',
                'status'        => '1',
                'created_at' => Carbon::now()
            ],[
                'title'         => 'Sleep Well',
                'is_other'    => '0',
                'description'   => ' ',
                'status'        => '1',
                'created_at' => Carbon::now()
            ],[
                'title'         => 'Playing Game',
                'is_other'    => '0',
                'description'   => ' ',
                'status'        => '1',
                'created_at' => Carbon::now()
            ],[
                'title'         => 'Read a Book',
                'is_other'    => '0',
                'description'   => ' ',
                'status'        => '0',
                'created_at' => Carbon::now()
            ]
        ]);
    }
}

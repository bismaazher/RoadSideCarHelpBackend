<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HabitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->addHabit();
    }

    public function addHabit()
    {
       \DB::table('habits')->insert([
            [
                'title'          => 'Life',
                'description'    => ' ',
                'status'         => '1',
                'created_at'     => Carbon::now()
            ],[
                'title'         => 'Career',
                'description'   => ' ',
                'status'        => '1',
                'created_at' => Carbon::now()
            ],[
                'title'         => 'Finance',
                'description'   => ' ',
                'status'        => '1',
                'created_at' => Carbon::now()
            ],[
                'title'         => 'Health & Wellness',
                'description'   => ' ',
                'status'        => '1',
                'created_at' => Carbon::now()
            ],[
                'title'         => 'Relationship',
                'description'   => ' ',
                'status'        => '1',
                'created_at' => Carbon::now()
            ]
        ]);
    }
}

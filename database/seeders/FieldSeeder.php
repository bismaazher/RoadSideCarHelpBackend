<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FieldSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->addField();
    }

    public function addField()
    {
       \DB::table('fields')->insert([
            [
                'title'          => 'Gym Trainer',
                'description'    => ' ',
                'status'         => '1',
                'created_at'     => Carbon::now()
            ],[
                'title'         => 'Nutritionist',
                'description'   => ' ',
                'status'        => '1',
                'created_at' => Carbon::now()
            ],[
                'title'         => 'Psychologist',
                'description'   => ' ',
                'status'        => '1',
                'created_at' => Carbon::now()
            ]
        ]);
    }
}

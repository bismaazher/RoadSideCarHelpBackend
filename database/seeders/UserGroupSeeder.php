<?php

namespace Database\Seeders;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->addRoles();
    }

    public function addRoles()
    {
       \DB::table('user_groups')->insert([
            [
                'title'          => 'Super Admin',
                'type'           => 'admin',
                'is_super_admin' => '1',
                'created_at'     => Carbon::now()
            ],[
                'title'         => 'Student',
                'type'          => 'user',
                'is_super_admin'=> '0',
                'created_at' => Carbon::now()
            ],[
                'title'         => 'Coach',
                'type'          => 'user',
                'is_super_admin'=> '0',
                'created_at' => Carbon::now()
            ]
        ]);
    }
}

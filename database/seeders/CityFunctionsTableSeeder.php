<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CityFunctionsTableSeeder extends Seeder
{
    public function run()
    {
        // Disable foreign key checks so we can truncate without the grid cells constraint blocking us
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('city_functions')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        DB::table('city_functions')->insert([
            [
                'name' => 'Police Station',
                'category' => 'Public Safety',
                'qol_score' => 8,
                'livability' => 3,
                'safety'     => 10,
                'economy'    => 1,
                'environment'=> 0,
                'welfare'    => 4,
                'image_path' => 'images/city_functions/police_station.png',
                'description' => 'A facility where police officers work and coordinate law enforcement operations.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Fire Station',
                'category' => 'Public Safety',
                'qol_score' => 7,
                'livability' => 2,
                'safety'     => 8,
                'economy'    => 1,
                'environment'=> -1,
                'welfare'    => 3,
                'image_path' => 'images/city_functions/fire_station.png',
                'description' => 'A building housing fire engines and the personnel who respond to fire emergencies.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'School',
                'category' => 'Education',
                'qol_score' => 10,
                'livability' => 5,
                'safety'     => 2,
                'economy'    => 3,
                'environment'=> 1,
                'welfare'    => 8,
                'image_path' => 'images/city_functions/school.png',
                'description' => 'An educational institution for teaching students.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Park',
                'category' => 'Recreation',
                'qol_score' => 12,
                'livability' => 8,
                'safety'     => 1,
                'economy'    => 1,
                'environment'=> 10,
                'welfare'    => 5,
                'image_path' => 'images/city_functions/park.png',
                'description' => 'A green space for relaxation, outdoor activities, and family gatherings.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Shopping Mall',
                'category' => 'Commercial',
                'qol_score' => -5,
                'livability' => 2,
                'safety'     => -2,
                'economy'    => 10,
                'environment'=> -5,
                'welfare'    => 1,
                'image_path' => 'images/city_functions/shopping_mall.png',
                'description' => 'A commercial complex hosting multiple retail stores and services.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Housing',
                'category' => 'Residential',
                'qol_score' => 5,
                'livability' => 6,
                'safety'     => 1,
                'economy'    => 2,
                'environment'=> -1,
                'welfare'    => 4,
                'image_path' => 'images/city_functions/housing.png',
                'description' => 'Residential areas providing homes and living spaces for the community.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sports Area',
                'category' => 'Recreation',
                'qol_score' => 9,
                'livability' => 7,
                'safety'     => 1,
                'economy'    => 2,
                'environment'=> 3,
                'welfare'    => 6,
                'image_path' => 'images/city_functions/sports_area.png',
                'description' => 'Facilities dedicated to sports and physical activities.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Hotel',
                'category' => 'Hospitality',
                'qol_score' => 3,
                'livability' => 3,
                'safety'     => 0,
                'economy'    => 7,
                'environment'=> -2,
                'welfare'    => 1,
                'image_path' => 'images/city_functions/hotel.png',
                'description' => 'Places providing lodging for residents or visitors, such as hotels or hostels.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
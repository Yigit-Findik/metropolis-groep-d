<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CityFunctionsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('city_functions')->truncate();

        DB::table('city_functions')->insert([
            [
                'name' => 'Police Station',
                'category' => 'Public Safety',
                'image_path' => 'images/city_functions/police_station.png',
                'description' => 'A facility where police officers work and coordinate law enforcement operations.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Fire Station',
                'category' => 'Public Safety',
                'image_path' => 'images/city_functions/fire_station.png',
                'description' => 'A building housing fire engines and the personnel who respond to fire emergencies.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'School',
                'category' => 'Education',
                'image_path' => 'images/city_functions/school.png',
                'description' => 'An educational institution for teaching students.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Park',
                'category' => 'Recreation',
                'image_path' => 'images/city_functions/park.png',
                'description' => 'A green space for relaxation, outdoor activities, and family gatherings.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Shopping Mall',
                'category' => 'Commercial',
                'image_path' => 'images/city_functions/shopping_mall.png',
                'description' => 'A commercial complex hosting multiple retail stores and services.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Housing',
                'category' => 'Residential',
                'image_path' => 'images/city_functions/housing.png',
                'description' => 'Residential areas providing homes and living spaces for the community.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sports Area',
                'category' => 'Recreation',
                'image_path' => 'images/city_functions/sports_area.png',
                'description' => 'Facilities dedicated to sports and physical activities.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Hotel',
                'category' => 'Hospitality',
                'image_path' => 'images/city_functions/hotel.png',
                'description' => 'Places providing lodging for residents or visitors, such as hotels or hostels.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
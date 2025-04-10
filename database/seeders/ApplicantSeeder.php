<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Applicant;
use Faker\Factory as Faker;

class ApplicantSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();
        $statuses = ['applied', 'stage 1', 'stage 2', 'stage 3'];
        // Only initial stages for new applicants; none with background check done yet
        foreach(range(1, 50) as $i) {
            Applicant::create([
                'first_name' => $faker->firstName,
                'last_name'  => $faker->lastName,
                'title'      => $faker->title,
                'email'      => 'tom+' . $faker->firstName . $faker->lastName . '@amiqus.co',
                'status'     => $faker->randomElement($statuses),
            ]);
        }
    }
}

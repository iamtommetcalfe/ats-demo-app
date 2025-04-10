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

        $titles = ['miss', 'ms', 'mrs', 'mr', 'dr', 'sir', 'master', 'mx', 'dame', 'lord', 'lady', 'prof'];
        $statuses = ['applied', 'stage 1', 'stage 2', 'stage 3'];

        foreach (range(1, 50) as $i) {
            $firstName = $faker->firstName;
            $lastName = $faker->lastName;

            Applicant::create([
                'first_name' => $faker->firstName,
                'last_name'  => $lastName = $faker->lastName,
                'title'      => $faker->randomElement($titles),
                'email'      => 'tom+' . strtolower($firstName . $lastName) . '@amiqus.co',
                'status'     => $faker->randomElement($statuses),
            ]);
        }
    }
}

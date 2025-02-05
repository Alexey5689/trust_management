<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class ManagerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('ru_RU'); 

        for ($i = 0; $i < 5; $i++) {
            $firstNameCyr = $faker->firstName;
            $lastNameCyr = $faker->lastName;
            $firstNameLat = $this->transliterate($firstNameCyr);
            $lastNameLat = $this->transliterate($lastNameCyr);

            $email = strtolower($firstNameLat . $lastNameLat . '@mail.ru');

            User::create([
                'last_name' => $lastNameCyr,
                'first_name' => $firstNameCyr,
                'middle_name' => $faker->middleName, 
                'email' => $email,
                'phone_number' => '+7' . $faker->numberBetween(9000000000, 9999999999),
                'password' => bcrypt('password'), 
                'active' => true,
                'token' => Str::random(60),
                'refresh_token' => Str::random(60),
                'role_id' => 2, 
            ]);
        }
    }

    
    private function transliterate($text)
    {
        $cyr = [
            'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П',
            'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я',
            'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п',
            'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я'
        ];

        $lat = [
            'A', 'B', 'V', 'G', 'D', 'E', 'E', 'Zh', 'Z', 'I', 'Y', 'K', 'L', 'M', 'N', 'O', 'P',
            'R', 'S', 'T', 'U', 'F', 'H', 'Ts', 'Ch', 'Sh', 'Sch', '', 'Y', '', 'E', 'Yu', 'Ya',
            'a', 'b', 'v', 'g', 'd', 'e', 'e', 'zh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p',
            'r', 's', 't', 'u', 'f', 'h', 'ts', 'ch', 'sh', 'sch', '', 'y', '', 'e', 'yu', 'ya'
        ];

        return str_replace($cyr, $lat, $text);
    }
}

<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Customer::class, function (Faker $faker) {
    return [
	'customer_name' => $faker->name,	
	'contact_name' => $faker->name,	
	'contact_phone' => $faker->phoneNumber,	
	'memo' => $faker->name,	
    ];
});

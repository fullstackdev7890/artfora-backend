<?php

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\Models\User::class, function (Faker\Generator $faker) {
    static $password;

    return [
        'username' => $faker->name,
        'tagname' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => $password ?: $password = Hash::make('secret'),
        'remember_token' => Str::random(10),
        'role_id' => \App\Models\Role::USER
    ];
});

$factory->define(App\Models\Role::class, function () {
    return [
        'name' => 'user'
    ];
});

$factory->define(App\Models\Media::class, function (Faker\Generator $faker) {
    return [
        'link' => $faker->word,
        'name' => $faker->word
    ];
});
$factory->define(App\Models\Setting::class, function (Faker\Generator $faker) {
    return [
        'key' => $faker->word,
        'value' => $faker->word
    ];
});


$factory->define(App\Models\TwoFactorAuthEmail::class, function (Faker\Generator $faker) {
    return [
        'email' => $faker->word,
        'code' => $faker->word,
    ];
});

$factory->define(App\Models\PasswordReset::class, function (Faker\Generator $faker) {
    return [
        'email' => $faker->word,
        'token' => $faker->word,
    ];
});

$factory->define(App\Models\Category::class, function (Faker\Generator $faker) {
    return [
        'title' => $faker->word,
    ];
});
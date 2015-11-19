<?php

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

$factory->define(CodeProject\Entities\User::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->email,
        'password' => bcrypt(str_random(10)),
        'remember_token' => str_random(10),
    ];
});

$factory->define(CodeProject\Entities\Client::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'responsible' => $faker->name,
        'email' => $faker->email,
        'phone' => $faker->phoneNumber,
        'address' => $faker->address,
        'obs' => $faker->sentence,
    ];
});

$factory->define(CodeProject\Entities\Project::class, function (Faker\Generator $faker) {
    $u = count(\CodeProject\Entities\User::all());
    $c = count(\CodeProject\Entities\Client::all());
    return [
        'owner_id' => rand(1, $u),
        'client_id' => rand(1, $c),
        'name' => $faker->name,
        'description' => $faker->sentence,
        'progress' => rand(1,100),
        'status' => rand(1,3),
        'due_date' => $faker->dateTimeBetween($startDate = '-30days', $endDate = 'now')
    ];
});

$factory->define(CodeProject\Entities\ProjectNote::class, function (Faker\Generator $faker) {
    return [
        'project_id' => rand(1, 10),
        'title' => $faker->word,
        'note' => $faker->paragraph
    ];
});

$factory->define(\CodeProject\Entities\ProjectTask::class, function (\Faker\Generator $faker) {
    return [
        'project_id' => rand(1, 10),
        'name' => $faker->word,
        'start_date' => $faker->dateTime,
        'due_date' => $faker->dateTime,
        'status' => rand(1,3)
    ];
});

$factory->define(\CodeProject\Entities\OauthClient::class, function (\Faker\Generator $faker){
    return [
        'id' => 'appID1',
        'secret' => 'secret',
        'name' => 'AngularAPP'
    ];
});

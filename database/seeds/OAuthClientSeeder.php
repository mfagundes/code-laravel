<?php

use Illuminate\Database\Seeder;

class OAuthClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \CodeProject\Entities\OauthClient::truncate();
        factory(\CodeProject\Entities\OauthClient::class, 1)->create();

    }
}

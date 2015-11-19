<?php

namespace CodeProject\Entities;

use Illuminate\Database\Eloquent\Model;


class OauthClient extends Model
{
    protected $table = 'oauth_clients';
    protected $guarded = [];
    protected $fillable = [
        'id',
        'secret',
        'name'
    ];
}
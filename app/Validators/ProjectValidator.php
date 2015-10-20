<?php

namespace CodeProject\Validators;


use Prettus\Validator\LaravelValidator;

class ProjectValidator extends LaravelValidator
{
    protected $rules = [
        'owner_id' => 'required|integer',
        'client_id' => 'required|integer',
        'name' => 'required|max:255',
        'progress' => 'required|integer|min:0|max:100',
        'status'=> 'required|integer|min:1|max:3',
        'due_date' => 'required|date'
    ];
}
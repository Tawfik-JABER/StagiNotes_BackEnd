<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;

class Director extends Authenticatable
{
    use HasFactory,HasApiTokens;
    protected $fillable = [
        'cin',
        'nom',
        'prenom',
        'email',
        'password',
        'sexe',
        'email_verify',
    ];
}

<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Tutteur extends Authenticatable
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
        'login_at',
        'image_url',
    ];
}

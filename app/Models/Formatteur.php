<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Formatteur extends Authenticatable
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
    ];
    public function formateurFilliereModule()
    {
        return $this->hasMany(Formateur_filliere_module::class);
    }
}

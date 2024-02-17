<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Stagiaire extends Authenticatable
{
    use HasFactory,HasApiTokens;
    protected $fillable = [
        'cin',
        'nom',
        'prenom',
        'email',
        'password',
        'fill_id',
        'numero',
        'cef',
        'group',
        'annee',
        'niveau',
        'sexe',
        'email_verify',
        'login_at',
    ];
    public function modules()
    {
        return $this->belongsToMany(Module::class, 'stagiaire_modules')->withPivot('note_general');
    }
    public function filliere()
    {
        return $this->belongsTo(Filliere::class);
    }

    public function premierControles()
    {
        return $this->hasMany(PremierControle::class, 'stagiaire_id');
    }

    public function deuxiemeControles()
    {
        return $this->hasMany(DeuxiemControle::class, 'stagiaire_id');
    }
    public function troisiemControles()
    {
        return $this->hasMany(TroisiemControle::class, 'stagiaire_id');
    }
    public function efm()
    {
        return $this->hasMany(Efm::class, 'stagiaire_id');
    }
    public function absence()
    {
        return $this->hasMany(Absence::class, 'stagiaire_id');
    }
}

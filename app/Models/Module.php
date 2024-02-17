<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;
    protected $fillable = [
        'nom',
        'code',
        'coefficient'
    ];
    public function formateurFilliereModule()
    {
        return $this->hasMany(Formateur_filliere_module::class);
    }

    public function stagiaires()
    {
        return $this->belongsToMany(Stagiaire::class, 'stagiaire_modules')->withPivot('note_general');
    }

    public function premierControles()
    {
        return $this->hasMany(PremierControle::class, 'module_id');
    }

    public function deuxiemeControles()
    {
        return $this->hasMany(DeuxiemControle::class, 'module_id');
    }

    public function troisiemControles()
    {
        return $this->hasMany(TroisiemControle::class, 'module_id');
    }

    public function efm()
    {
        return $this->hasMany(Efm::class, 'module_id');
    }
}

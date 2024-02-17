<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Filliere extends Model
{
    use HasFactory;
    protected $fillable = [
        'nom',
        'description'
    ];
    public function stagiaire()
    {
        return $this->hasMany(Stagiaire::class);
    }

    public function formateurFilliereModule()
    {
        return $this->hasMany(Formateur_filliere_module::class);
    }
}

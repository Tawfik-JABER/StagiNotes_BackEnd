<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absence extends Model
{
    use HasFactory;

    protected $fillable = [
        'stagiaire_id',
        'durrée',
        'date',
        'justifié'
    ];
    public function stagiaire()
    {
        return $this->belongsTo(Stagiaire::class,'stagiaire_id');
    }
}

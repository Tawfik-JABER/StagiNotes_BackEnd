<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Efm extends Model
{
    use HasFactory;
    protected $fillable = [
        'stagiaire_id',
        'module_id',
        'annee_schol',
        'note'
    ];
    public function stagiaire()
    {
        return $this->belongsTo(Stagiaire::class, 'stagiaire_id');
    }

    public function module()
    {
        return $this->belongsTo(Module::class, 'module_id');
    }
}

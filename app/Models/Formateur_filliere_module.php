<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Formateur_filliere_module extends Model
{
    use HasFactory;


    protected $fillable = [
        'formateur_id',
        'filliere_id',
        'module_id',
    ];
    public function formatteure()
    {
        return $this->belongsTo(Formatteur::class,'formateur_id');
    }

    public function filliere()
    {
        return $this->belongsTo(Filliere::class,'filliere_id');
    }

    public function module()
    {
        return $this->belongsTo(Module::class, 'module_id');
    }
}

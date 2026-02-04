<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dao extends Model
{
    protected $table = 'daos';

    protected $fillable = [
        'nom',
        'description',
        'email',
        'telephone',
        'adresse',
        'ville',
        'code_postal',
        'actif',
    ];

    protected $casts = [
        'actif' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}

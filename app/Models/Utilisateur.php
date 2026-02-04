<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Utilisateur extends Authenticatable
{
    use Notifiable;

    protected $table = 'utilisateurs';

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'mot_de_passe',
        'role',
        'actif'
    ];

    protected $hidden = [
        'mot_de_passe',
    ];

    /**
     * Get the name of the unique identifier for the model.
     */
    public function getAuthIdentifierName()
    {
        return 'id';
    }

    /**
     * Get the unique identifier for the model.
     */
    public function getAuthIdentifier()
    {
        return $this->getAttribute($this->getAuthIdentifierName());
    }

    /**
     * Get the password for the model.
     */
    public function getAuthPassword()
    {
        return $this->mot_de_passe;
    }

    /**
     * Get the token value for the "remember me" functionality.
     */
    public function getRememberToken()
    {
        return null;
    }

    /**
     * Set the token value for the "remember me" functionality.
     */
    public function setRememberToken($value)
    {
        // Not implemented for this model
    }

    /**
     * Get the column name for the "remember me" token.
     */
    public function getRememberTokenName()
    {
        return null;
    }
}

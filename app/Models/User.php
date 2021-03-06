<?php
/**
 * Models/User.php
 * 
 * @author Hans Medina <twd2206@gmail.com>
 */
namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Modelo de la tabla users
 * 
 * @category Models
 */
class User extends Authenticatable
{
    use Notifiable;


    /**
     * Los campos que solo serán alterados en la base de datos
     *
     * @var array
     */
    protected $fillable = [
        'nombres', 'email', 'password', "ape_paterno", "ape_materno", "nombre_completo"
    ];

    /**
     * Los campos que no se mostrarán
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public function roles() 
    {
        return $this->belongsToMany(Role::class, "role_user");
    }

    public function modulos()
    {
        return $this->belongsToMany(Modulo::class, 'modulo_user');
    }
    
}

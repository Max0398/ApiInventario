<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = ['nombre',
    'icono',
    'url',];


    public function rols()
{
    return $this->belongsToMany(Role::class, 'menu_rol', 'menu_id', 'rol_id');
}

}

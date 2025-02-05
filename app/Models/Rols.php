<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rols extends Model
{

    protected $fillable = ['name'];
    protected $table = 'rols'; //Aqui puse el nombre porque daba error al ponerle Role la buscaba como roles y es rols
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'menu_rol', 'rol_id', 'menu_id');
    }
}

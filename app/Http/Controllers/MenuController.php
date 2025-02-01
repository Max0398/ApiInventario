<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Responses\ApiResponse;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MenuController extends Controller
{
    public function menuUser($id)
    {
        try {
            $user = DB::table('users')->where('id', $id)->first();
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'value' => null,
                    'message' => 'Usuario no encontrado'
                ], 404);
            }
            // consulta con join para cargar el rol y los menu del usuario
            $menus = DB::table('users as u')
                ->join('rols as r', 'u.rol_id', '=', 'r.id')
                ->join('menu_rol as mr', 'mr.rol_id', '=', 'r.id')
                ->join('menus as mn', 'mn.id', '=', 'mr.menu_id')
                ->where('u.id', '=', $id)  // Filtramos por el ID del usuario
                ->select('mn.nombre', 'mn.icono', 'mn.url')
                ->get();

            // consultar para ver que no este vacio
            if (!$menus) {
                ApiResponse::NotFound('Menu not found',[],404);
            }

            return ApiResponse::Success('isSuccess',$menus,200);

        } catch (\Exception $e) {
            logger('Error en menuUser: ' . $e->getMessage());
            return ApiResponse::Error($e->getMessage(), [], 500);
        }
    }
}

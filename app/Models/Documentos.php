<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Documentos extends Model
{
    use HasFactory;

    protected $table = 'documentos';
    protected $fillable = [
      'nombre'
    ];

    public function getDocumentos(){
        return DB::table('documentos')->select('nombre')->orderBy('nombre')->get();
    }

    public function getDocumento($id)
    {
        return DB::table('documentos')->select('nombre')->where('id', '=', $id)->first();
    }
    public function createDocumentos($payload){
        $documento = Documentos::create([
            'nombre' => $payload->nombre
        ]);
        if ($documento){
            return true;
        }
        return false;
    }

    public function updateDocumentos($id, $payload){
        $documento = Documentos::find($id);
        if ($documento){
            $update = $documento->update([
                'nombre' => $payload->nombre
            ]);
            if ($update){
                return true;
            }
            return false;
        }
        return false;
    }

    public function deleteDocumentos($id){
        $documento = Documentos::find($id);

        if ($documento){
            $delete = $documento->delete();
            if ($delete){
                return true;
            }
            return false;
        }
        return false;
    }

    public function clave_documento(){
        return $this->hasOne(ClaveDocumentos::class, 'documento_id', 'id');
    }
}

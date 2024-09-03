<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ClaveDocumentos extends Model
{
    use HasFactory;

    protected $table = 'clave_documentos';
    protected $fillable = [
        'clave', 'documento_id'
    ];

    public function documento(){
        return $this->belongsTo(Documentos::class, 'id', 'documento_id');
    }

    public function saveClaveDocumentos($clave, $documento_id){
        $c = ClaveDocumentos::create([
            'clave' => $clave,
            'documento_id' => $documento_id
        ]);
        if ($c){
            return true;
        }
        return false;
    }

    public function getClaveDocumentos($id){
        return DB::table('clave_documentos')->where('clave', $id)->first();
    }
    public function updateclaveDocumentos($id, $payload){
        $c = ClaveDocumentos::where('documento_id', $id)->first();
        if ($c){
            $update = $c->update([
               'clave' => $payload,
            ]);
            if ($update){
                return true;
            }
            return false;
        }
        return false;
    }
    public function deleteClaveDocumentos($id){
        $clave = ClaveDocumentos::where('documento_id', $id)->first();
        if ($clave){
            $delete = $clave->delete();
            if ($delete){
                return true;
            }
            return false;
        }
        return false;
    }

    public function getClaveDocumento($data){
        return DB::table('clave_documentos')
            ->join('documentos', 'clave_documentos.documento_id', '=', 'documentos.id')
            ->where('documentos.nombre', '=', $data)
            ->select('clave_documentos.clave')
            ->first();
    }
}

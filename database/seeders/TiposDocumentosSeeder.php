<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TiposDocumentosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('documentos')->insert([
            'nombre' => 'Deteccion de necesidades',
        ]);
        DB::table('documentos')->insert([
            'nombre' => 'Programa institucional de formación docente y actualización profesional',
        ]);
        DB::table('documentos')->insert([
            'nombre' => 'Cédula de inscripción'
        ]);
        DB::table('documentos')->insert([
            'nombre' => 'Lista de asistencia'
        ]);

        DB::table('clave_documentos')->insert([
            'clave' => 'ITTG-AC-PO-006-01',
            'documento_id' => 1
        ]);
        DB::table('clave_documentos')->insert([
            'clave' => 'ITTG-AC-PO-006-02',
            'documento_id' => 2
        ]);
        DB::table('clave_documentos')->insert([
            'clave' => 'ITTG-AC-PO-006-04',
            'documento_id' => 3
        ]);
        DB::table('clave_documentos')->insert([
            'clave' => 'ITTG-AC-PO-006-05',
            'documento_id' => 4
        ]);
    }
}

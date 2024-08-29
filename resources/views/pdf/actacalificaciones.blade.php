<html >
<head>

    <!-- <title></title> -->
    <!-- Styles -->

    <style>
        @page {
            size: A4;
            margin: 0
        }

        body {
            background-image: url({{ public_path('/storage/Membretado/'.$year.'/img_acta_calificaciones.jpg') }});
            background-size: cover; /* O ajusta según tus necesidades (p. ej., contain) */
            background-position: center; /* Ajusta según tus necesidades */
            background-repeat: no-repeat;
            min-height: 100vh; /* Asegura que el cuerpo tenga al menos la altura de la ventana gráfica (viewport height) */
            display: flex;
            flex-direction: column;
            position: sticky;
            /*margin: 40px*/
        }
        .contenido {
            /*margin: 50px 50px 250px 50px;*/
            /*position: relative;*/
            margin: 50px 50px;
        }
        .header {
            display: flex;
            position: relative;
            top: 100px;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            font-weight: bold;
            font-family: "Arial", "Helvetica", sans-serif;
            font-size: 10pt;
        }
        .text-position {
            display: flex;
            position: relative;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: start;
            margin-left: 100px;
            font-family: "Arial", "Helvetica", sans-serif;
            font-size: 10pt;
            top: 100px;
        }
        .custom_table {
            table-layout: auto;
            width: 75%;
            border-collapse: collapse;
            border: 1px solid black;
            margin-top: 130px;
            margin-left: 100px;
        }


        .custom_table td,
        .custom_table th {
            border: 1px solid #000;
            padding: 8px; /* Ajusta según tus necesidades */
        }

        .custom_table th {
            text-align: center;
            font-size: 8pt;
            font-weight: bold;
            background-color: #9ca3af;
        }

        .custom_table tbody {
            text-align: center;
            font-size: 8pt;
            padding: 8px; /* Ajusta según tus necesidades */
        }
        .firmas {
            margin-left: 80px;
        }
        .final_part {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 140px;
        }
        .words_strong {
            font-weight: bold;
            font-size: 8pt;
        }
        .margen-derecho {
            margin-right: 200px;
            margin-top: 1px;
        }
        .margen-izquierdo {
            margin-left: 50px;
            margin-top: 1px;
        }
    </style>

</head>

<body>
    <div class="contenido">
        <div class="header">
            <p>ACTA DE CALIFICACIONES</p>
        </div>
        <div class="text-position">
            <p><b>NOMBRE DEL CURSO/TALLER</b>: {{$curso->nombreCurso}}</p>
            <p><b>FECHA DE REALIZACIÓN</b>: {{$curso->fecha_I}} - {{$curso->fecha_F}}</p>
            <p><b>ÁREA ACADÉMICA</b>: {{$curso->carrera->nameCarrera}}</p>
        </div>

        <table class="custom_table">
            <thead>
            <tr>
                <th>NÚM.</th>
                <th>NOMBRE DEL DOCENTE</th>
                <th>APROBADO /
                    NO APROBADO
                </th>
            </tr>
            </thead>
            @php
                $count = 1;
            @endphp
            @foreach($teachers as $docente)
                <tbody>
                <tr>
                    <td>{{$count++}}</td>
                    <td>{{$docente->nombre_completo}}</td>
                    <td>
                        @if($docente->calificacion == 0)
                            NO APROBADO
                        @else
                            APROBADO
                        @endif
                    </td>
                </tr>
                </tbody>
            @endforeach
        </table>
        <div style="page-break-before: always"></div>
        <div class="final_part">
            @if(count($facilitadores) > 0)
                <div style="text-align: center;" class="">
                    @for ($i = 0; $i < min(3, count($facilitadores)); $i++)
                        <div style="display: inline-block; margin: 0 10px;">
                            <p class="">FACILITADOR {{ $i + 1 }}</p>
                            <p class="">{{ mb_strtoupper($facilitadores[$i]->nombre_completo, 'UTF-8') }}</p>
                            <p class="">NOMBRE Y FIRMA</p>
                        </div>
                    @endfor
                </div>
            @else
                <div style="display: inline-block; margin: 0 10px;" class="">
                    <p class="">FACILITADOR</p>
                    <p class="">{{ mb_strtoupper($curso->facilitador_externo, 'UTF-8') }}</p>
                    <p class="">NOMBRE Y FIRMA</p>
                </div>
            @endif
        </div>
    </div>
</body>

</html>

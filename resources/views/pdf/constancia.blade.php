<html>
<head>

    <!-- <title></title> -->
    <!-- Styles -->
{{--    <link rel="preconnect" href="https://fonts.googleapis.com">--}}
{{--    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>--}}
{{--    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@800&display=swap" rel="stylesheet">--}}
   <style>
        @font-face {
            font-family: 'Montserrat-ExtraBold', sans-serif;
            src: url('/storage/fonts/Montserrat-ExtraBold.ttf') format("truetype");
            font-style: normal;
        }
        @page {
            size: A4 portrait;
            margin: 0;
        }
        body {
            background-image: url({{ public_path('/storage/Membretado/'.$year.'/img_constancia.jpg') }});
            background-size: cover; /* O ajusta según tus necesidades (p. ej., contain) */
            background-position: center; /* Ajusta según tus necesidades */
            background-repeat: no-repeat;
            min-height: 100vh; /* Asegura que el cuerpo tenga al menos la altura de la ventana gráfica (viewport height) */
            display: flex;
            flex-direction: column;
            font-family: 'Montserrat-ExtraBold', sans-serif;
        }
        table {
            width: 100%;
        }
        .final_part {
            display: flex;
            justify-content: center;
            align-items: center;
            top: 400px;
            margin-top: 100px;
        }
    </style>

</head>

<body style="">
    <div style="margin-top: 160px"></div>
    <table width="100%" style="">
        <tr>
            <td style="text-align: center; vertical-align: middle; font-size: 18pt; font-weight: bolder">
                EL TECNOLÓGICO NACIONAL DE MÉXICO
            </td>
        </tr>
    </table>
    <table width="100%" style="">
        <tr>
            <td style="text-align: center; vertical-align: middle; font-size: 14pt; font-weight: bold">
                A TRAVÉS DEL {{$instituto[0]->name}}
            </td>
        </tr>
    </table>
    <div style="margin-top: 20px"></div>
    <table width="100%" style="">
        <tr>
            <td style="text-align: center; vertical-align: middle; font-size: 14pt;">
                OTORGA LA PRESENTE
            </td>
        </tr>
    </table>
    <div style="margin-top: 20px"></div>
    <table width="100%" style="margin-left: 4px">
        <tr>
            <td style="text-align: center; vertical-align: middle;  font-size: 22pt; font-weight: bold">
                CONSTANCIA
            </td>
        </tr>
    </table>
    <div style="margin-top: 20px"></div>
    <table width="100%" style="margin-left: 4px">
        <tr>
            <td style="text-align: center; vertical-align: middle; font-size: 14pt;">
                A
            </td>
        </tr>
    </table>
    <div style="margin-top: 20px"></div>
    <table width="100%" style="margin-left: 4px">
        <tr>
            <td style="text-align: center; vertical-align: middle; font-size: 24pt; font-weight: bold;">
                {{$docente->nombre_completo}}
            </td>
        </tr>
    </table>
    <div style="margin-top: 50px"></div>
    <table width="100%" style="margin-left: 4px">
        <tr>
            <td style="text-align: center; vertical-align: middle; sans-serif; font-size: 14pt;">
                Por su destacada participación en el
                @if($curso->tipo_actividad == 1)
                    Taller
                @elseif($curso->tipo_actividad == 2)
                    Curso
                @elseif($curso->tipo_actividad == 3)
                    Curso/taller
                @elseif($curso->tipo_actividad == 4)
                    Foro
                @elseif($curso->tipo_actividad == 5)
                    Seminario
                @elseif($curso->tipo_actividad == 6)
                    Diplomado
                @endif
            </td>
        </tr>
    </table>
    <div style="margin-top: 10px"></div>
    <table width="100%" style="margin-left: 4px">
        <tr>
            <td style="text-align: center; vertical-align: middle; font-size: 16pt; font-weight: bold;">
                "{{$curso->nombreCurso}}"
            </td>
        </tr>
    </table>
    <table style="margin-left: 15px">
        <tr>
            <td style="text-align: justify; font-size: 10pt; width: 20%; padding: 20px 140px 20px 120px">
                Que dentro del programa Institucional de Formación Docente y Actualización Profesional @if($curso->periodo === 1)
                    Enero-Junio
                @else
                    Agosto-Diciembre
                @endif
                {{$year}} se llevó a cabo en este instituto, del {{$formatFechasI[2]}} al {{$formatFechasF[2]}} de {{$month[0]}} de {{$formatFechasF[0]}}, con una duración de {{$docente->inscrito[0]->total_horas}} horas.
            </td>
        </tr>
    </table>
    <div style="margin-top: 15px"></div>
    <table>
        <tr>
            <td style="text-align: justify; font-size: 8pt; padding-left: 450px">
                Tuxtla Gutierrez, Chiapas; {{$month[2]}} {{$month[1]}} de {{$formatFechasF[0]}}
            </td>
        </tr>
    </table>
    <div style="margin-top: 120px"></div>
{{--    <div class="final_part">--}}
{{--        <div style="text-align: center;" class="">--}}
{{--            <div style="display: inline-block; margin: 0 10px;" class="">--}}
{{--                <p style="font-weight: bold" class="">Director</p>--}}
{{--                <p class="">{{mb_strtoupper($director[0]->nameDirector, 'UTF-8')}}</p>--}}
{{--                <p class="">NOMBRE Y FIRMA</p>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--        @if(count($facilitador) > 0)--}}
{{--            <div style="text-align: center;" class="">--}}
{{--                @for ($i = 0; $i < min(3, count($facilitador)); $i++)--}}
{{--                    <div style="display: inline-block; margin: 0 10px;">--}}
{{--                        <p class="">FACILITADOR {{ $i + 1 }}</p>--}}
{{--                        <p class="">{{ mb_strtoupper($facilitador[$i]->nombre_completo, 'UTF-8') }}</p>--}}
{{--                        <p class="">NOMBRE Y FIRMA</p>--}}
{{--                    </div>--}}
{{--                @endfor--}}
{{--            </div>--}}
{{--        @else--}}
{{--            <div style="display: inline-block; margin: 0 10px;" class="">--}}
{{--                <p class="">FACILITADOR</p>--}}
{{--                <p class="">{{ mb_strtoupper($curso->facilitador_externo, 'UTF-8') }}</p>--}}
{{--                <p class="">NOMBRE Y FIRMA</p>--}}
{{--            </div>--}}
{{--        @endif--}}
{{--    </div>--}}
    <table>
        <tr>
            <td style="text-align: center; vertical-align: middle; font-size: 8pt;">
                {{mb_strtoupper($director[0]->nameDirector, 'UTF-8')}}
            </td>
            <td style="text-align: center; vertical-align: middle; font-size: 8pt;">
                            @if(count($facilitador) != 0)
                                {{mb_strtoupper($facilitador[0]->nombre_completo, 'UTF-8')}}
                            @else
                                {{mb_strtoupper($curso->facilitador_externo, 'UTF-8')}}
                            @endif
            </td>
            @if (count($facilitador) > 1)
                <td style="text-align: center; vertical-align: middle; font-size: 8pt;">
                    {{ mb_strtoupper($facilitador[1]->nombre_completo, 'UTF-8') }}
                </td>
            @endif
            @if (count($facilitador) > 2)
                <td style="text-align: center; vertical-align: middle; font-size: 8pt;">
                    {{ mb_strtoupper($facilitador[2]->nombre_completo, 'UTF-8') }}
                </td>
            @endif
        </tr>
        <tr>
            <td style="text-align: center; vertical-align: middle; font-size: 7pt;  font-weight: bold;">
                DIRECTOR
            </td>
            <td style="text-align: center; vertical-align: middle; font-size: 7pt;  font-weight: bold;" >
                FACILITADOR (A)
            </td>
            @if (count($facilitador) > 1)
                <td style="text-align: center; vertical-align: middle; font-size: 7pt;  font-weight: bold;">
                    <!-- Firma del facilitador -->
                    FACILITADOR (A)
                    <!-- Puedes agregar la imagen de la firma u otro contenido aquí -->
                </td>
            @endif
            @if (count($facilitador) > 2)
                <td style="text-align: center; vertical-align: middle; font-size: 7pt;  font-weight: bold;">
                    <!-- Firma del facilitador -->
                    FACILITADOR (A)
                    <!-- Puedes agregar la imagen de la firma u otro contenido aquí -->
                </td>
            @endif
        </tr>
    </table>
</body>

</html>

<table>
    <thead>
    <tr>
        <th>Constancia/Reconocimiento</th>
        <th>Nombre del participante</th>
        <th>Actividad (Curso/Taller. Curso, diplomado)</th>
        <th>Periodo</th>
        <th>Fecha de imparticion</th>
        <th>Lugar y fecha de registro</th>
        <th>Nombre Facilitador 1</th>
        <th>Género 1</th>
        <th>Nombre facilitador 2</th>
        <th>Género 2</th>
        <th>Nombre facilitador 3</th>
        <th>Género 3</th>
        <th>Nivel educativo</th>
        <th>Forma de imparticion del curso</th>
        <th>Duración del Curso</th>
        <th>Escolaridad del paricipante</th>
        <th>Área de adscripción</th>
        <th>Puesto</th>
        <th>Clave de registro tecnm</th>
        @php
            $i = 1;
        @endphp
        @foreach($data[0] as $clave => $valor)
            @if(strpos($clave, 'nombre_tema_') === 0 || strpos($clave, 'numero_tema_') === 0)
                <th>{{$i++}}</th>
                <th>tema</th>
            @endif
        @endforeach
    </tr>
    </thead>
    <tbody>
     @foreach($data as $curso)
        <tr>
            <td>{{ $curso->tipo }}</td>
            <td>{{ $curso->nombre_completo }}</td>
            <td>{{ $curso->nombreCurso }}</td>
            <td>{{ $curso->periodo }}</td>
            <td>{{ $curso->fecha_imparticion }}</td>
            <td>{{ $curso->lugar_registro }}</td>
            <td>{{ $curso->facilitador_1 }}</td>
            <td>{{ $curso->genero_1 }}</td>
            @if (isset($curso->facilitador_2))
                <td>{{ $curso->facilitador_2 }}</td>
            @else
                <td></td>
            @endif
            @if (isset($curso->genero_2))
                <td>{{ $curso->genero_2 }}</td>
            @else
                <td></td>
            @endif
            @if (isset($curso->facilitador_3))
                <td>{{ $curso->facilitador_3 }}</td>
            @else
                <td></td>
            @endif
            @if (isset($curso->genero_3))
                <td>{{ $curso->genero_3 }}</td>
            @else
                <td></td>
            @endif
            <td>{{ $curso->nivel_educativo }}</td>
            <td>{{ $curso->modalidad }}</td>
            <td>{{ $curso->duracion }}</td>
            <td>{{ $curso->posgrado }}</td>
            <td>{{ $curso->carrera }}</td>
            <td>{{ $curso->puesto }}</td>
            <td>{{ $curso->clave_registro }}</td>
            @foreach($data as $clave => $valor)
                @if(strpos($clave, 'nombre_tema_') === 0 || strpos($clave, 'numero_tema_') === 0)
                    <td>{{ $valor }}</td>
                @endif
            @endforeach
        </tr>
    @endforeach
    </tbody>
</table>
{{--  --}}
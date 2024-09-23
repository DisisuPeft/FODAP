<table>
    <thead>
    <tr>
        <th>Nombre</th>
        <th>Posgrado</th>
        <th>Plaza</th>
        <th>Puesto</th>
        <th>Carrera al frente</th>
        <th>Departamento adscrito</th>
        <th>Total de cursos</th>
{{--        <th>Género 1</th>--}}
{{--        <th>Nombre facilitador 2</th>--}}
{{--        <th>Género 2</th>--}}
{{--        <th>Nombre facilitador 3</th>--}}
{{--        <th>Género 3</th>--}}
{{--        <th>Nivel educativo</th>--}}
{{--        <th>Forma de imparticion del curso</th>--}}
{{--        <th>Duración del Curso</th>--}}
{{--        <th>Escolaridad del paricipante</th>--}}
{{--        <th>Área de adscripción</th>--}}
{{--        <th>Puesto</th>--}}
{{--        <th>Clave de registro tecnm</th>--}}
    </tr>
    </thead>
    <tbody>
     @foreach($data as $capacitados)
        <tr>
            <td>{{ $capacitados->nombre_completo }}</td>
            <td>{{ $capacitados->posgrado }}</td>
            <td>{{ $capacitados->plaza }}</td>
            <td>{{ $capacitados->puesto }}</td>
            <td>{{ $capacitados->carrera }}</td>
            <td>{{ $capacitados->departamento }}</td>
            <td>{{ $capacitados->total_cursos }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
{{--  --}}

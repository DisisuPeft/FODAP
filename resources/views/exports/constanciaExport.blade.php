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
        <th>Nivel educativo</th>
        <th>Forma de imparticion del curso</th>
        <th>Duración del Curso</th>
        <th>Escolaridad del paricipante</th>
        <th>Área de adscripción</th>
        <th>Puesto</th>
        <th>Clave de registro tecnm</th>
        <th>número</th>
        <th>tema</th>
    </tr>
    </thead>
    <tbody>
     @foreach($data as $curso)
        <tr>
            <td>{{ $curso->tipo }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

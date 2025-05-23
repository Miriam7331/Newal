<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ficha de Estudiante</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.5;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .header h1 {
            color: #2c3e50;
            margin-bottom: 5px;
        }
        .student-info {
            margin-bottom: 30px;
        }
        .student-info h2 {
            color: #3498db;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .info-item {
            margin-bottom: 10px;
        }
        .info-label {
            font-weight: bold;
            color: #555;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
            color: #333;
            font-weight: bold;
            text-align: left;
            padding: 8px;
        }
        td {
            padding: 8px;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #777;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Ficha de Estudiante</h1>
        <p>Fecha de generación: {{ date('d/m/Y H:i') }}</p>
    </div>

    <div class="student-info">
        <h2>Datos Personales</h2>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Nombre:</span> {{ $student->name }}
            </div>
            <div class="info-item">
                <span class="info-label">DNI:</span> {{ $student->dni ?: 'No disponible' }}
            </div>
            <div class="info-item">
                <span class="info-label">Email:</span> {{ $student->email }}
            </div>
            <div class="info-item">
                <span class="info-label">Teléfono:</span> {{ $student->phone }}
            </div>
            <div class="info-item">
                <span class="info-label">Dirección:</span> {{ $student->address ?: 'No disponible' }}
            </div>
            <div class="info-item">
                <span class="info-label">Ciudad:</span> {{ $student->city ?: 'No disponible' }}
            </div>
            <div class="info-item">
                <span class="info-label">Provincia:</span> {{ $student->province ?: 'No disponible' }}
            </div>
            <div class="info-item">
                <span class="info-label">Código Postal:</span> {{ $student->cp ?: 'No disponible' }}
            </div>
            <div class="info-item">
                <span class="info-label">Isla:</span> {{ $student->island ?: 'No disponible' }}
            </div>
            <div class="info-item">
                <span class="info-label">Género:</span> {{ $student->gender ?: 'No disponible' }}
            </div>
            <div class="info-item">
                <span class="info-label">Fecha de nacimiento:</span> {{ $student->birthdate ? date('d/m/Y', strtotime($student->birthdate)) : 'No disponible' }}
            </div>
            <div class="info-item">
                <span class="info-label">Nivel:</span> {{ $student->level ?: 'No disponible' }}
            </div>
        </div>
    </div>

    <div class="formative-actions">
        <h2>Acciones Formativas</h2>
        @if(count($student->formativeActions) > 0)
            <table>
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Curso/Módulo</th>
                        <th>Centro</th>
                        <th>Fecha Inicio</th>
                        <th>Fecha Fin</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($student->formativeActions as $action)
                        <tr>
                            <td>{{ $action->code }}</td>
                            <td>
                                @if($action->course)
                                    {{ $action->course->name }}
                                @elseif($action->module)
                                    {{ $action->module->name }}
                                @else
                                    No disponible
                                @endif
                            </td>
                            <td>{{ $action->center ? $action->center->name : 'No disponible' }}</td>
                            <td>{{ $action->start_date ? date('d/m/Y', strtotime($action->start_date)) : 'No disponible' }}</td>
                            <td>{{ $action->end_date ? date('d/m/Y', strtotime($action->end_date)) : 'No disponible' }}</td>
                            <td>{{ $action->pivot->status }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>El estudiante no está inscrito en ninguna acción formativa.</p>
        @endif
    </div>

    @if(count($student->notes) > 0)
    <div class="notes">
        <h2>Notas</h2>
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Usuario</th>
                    <th>Nota</th>
                </tr>
            </thead>
            <tbody>
                @foreach($student->notes as $note)
                    <tr>
                        <td>{{ date('d/m/Y', strtotime($note->created_at)) }}</td>
                        <td>{{ $note->user ? $note->user->name : 'Sistema' }}</td>
                        <td>{{ $note->content }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="footer">
        <p>Documento generado automáticamente por el sistema de gestión de estudiantes.</p>
    </div>
</body>
</html>
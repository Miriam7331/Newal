<?php

namespace App\Exports;

use App\Models\Internship;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class InternshipExcel implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Internship::with(['formativeActionHasStudent.student', 'company'])->get()->map(function ($internship) {
            $student = $internship->formativeActionHasStudent->student ?? null;
            $company = $internship->company ?? null;
            $formativeAction = $internship->formativeActionHasStudent->formativeAction ?? null;
            $course = $internship->formativeActionHasStudent->formativeAction->course ?? null;

            return [
                'Inicio' => $internship->start_date,
                'Fin' => $internship->end_date,
                'Horario' => $internship->schedule,
                'Estudiante' => $student?->name,
                'DNI' => $student?->dni,
                'Email Estudiante' => $student?->email,
                'Teléfono Estudiante' => $student?->phone,
                'Empresa' => $company?->name,
                'Email Empresa' => $company?->email,
                'Teléfono Empresa' => $company?->phone,
                'Código Acción Formativa' => $formativeAction?->code,
                'Curso Acción Formativa' => $course?->name,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Fecha inicio',
            'Fecha fin',
            'Horario',
            'Nombre del estudiante',
            'DNI estudiante',
            'Email estudiante',
            'Teléfono estudiante',
            'Nombre de la empresa',
            'Email empresa',
            'Teléfono empresa',
            'Código acción formativa',
            'Curso acción formativa'
        ];
    }
}

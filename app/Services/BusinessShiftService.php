<?php

namespace App\Services;

use Carbon\Carbon;

class BusinessShiftService
{
    /**
     * Determina la fecha de la jornada operativa actual.
     * Si la hora actual es antes de las 08:00 AM (madrugada 00:00 a 07:59),
     * la jornada corresponde al día anterior.
     */
    public static function getCurrentBusinessDate(): string
    {
        $now = Carbon::now();
        return $now->hour < 8 ? $now->copy()->subDay()->format('Y-m-d') : $now->format('Y-m-d');
    }

    /**
     * Determina el turno activo según la hora actual.
     * - De 08:00 a 16:00: 'manana'
     * - De 16:01 a 02:59: 'tarde' (16:01 a 03:00 AM)
     * - Madrugada intermedia (03:00 a 07:59): 'completo'
     */
    public static function getCurrentActiveShift(): string
    {
        $now = Carbon::now();
        if ($now->hour >= 8 && ($now->hour < 16 || ($now->hour == 16 && $now->minute == 0 && $now->second == 0))) {
            return 'manana';
        }
        if (($now->hour == 16 && $now->minute >= 1) || $now->hour > 16 || $now->hour < 3) {
            return 'tarde';
        }
        return 'completo';
    }

    /**
     * Obtiene el rango de fechas Carbon para una fecha y turno específico.
     *
     * Turnos:
     * - 'completo': 08:00:00 del día hasta 03:00:00 del día siguiente.
     * - 'manana': 08:00:00 del día hasta 16:00:00 del mismo día.
     * - 'tarde' / 'noche': 16:01:00 del día hasta 03:00:00 del día siguiente.
     *
     * @return array{start: Carbon, end: Carbon, label: string, shift: string}
     */
    public static function getShiftRange(string $date, ?string $shift = 'completo'): array
    {
        $base = Carbon::parse($date);
        $shift = in_array($shift, ['manana', 'tarde', 'noche', 'completo']) ? $shift : 'completo';

        switch ($shift) {
            case 'manana':
                $start = $base->copy()->setTime(8, 0, 0);
                $end = $base->copy()->setTime(16, 0, 0);
                $label = 'Turno Mañana (08:00 a 16:00 hs)';
                break;

            case 'tarde':
            case 'noche':
                $start = $base->copy()->setTime(16, 1, 0);
                $end = $base->copy()->addDay()->setTime(3, 0, 0);
                $label = 'Turno Tarde (16:01 a 03:00 hs)';
                break;

            case 'completo':
            default:
                $start = $base->copy()->setTime(8, 0, 0);
                $end = $base->copy()->addDay()->setTime(3, 0, 0);
                $label = 'Jornada Completa (08:00 a 03:00 hs)';
                break;
        }

        return [
            'start' => $start,
            'end' => $end,
            'label' => $label,
            'shift' => $shift,
        ];
    }
}

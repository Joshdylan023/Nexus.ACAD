<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AniversariantesController extends Controller
{
    /**
     * Retorna os aniversariantes do dia, da semana e do mês.
     */
    public function index(Request $request)
    {
        Log::info('🎂 ========== ANIVERSARIANTES INDEX ==========');
        Log::info('📥 Request:', $request->all());

        $query = User::select(
            'users.id',
            'users.name as nome_completo',
            'users.data_nascimento',
            'colaboradores.foto_perfil_sistema as foto_perfil_url'
        )
        ->join('colaboradores', 'users.id', '=', 'colaboradores.user_id')
        ->where('colaboradores.status', 'Ativo')
        ->whereNotNull('users.data_nascimento');

        $periodo = $request->input('periodo', 'hoje');
        
        Log::info('📅 Período selecionado: ' . $periodo);
        Log::info('📆 Data atual: ' . now());
        Log::info('📆 Mês atual: ' . date('m'));
        Log::info('📆 Dia atual: ' . date('d'));

        switch ($periodo) {
            case 'hoje':
                $dia = date('d');
                $mes = date('m');
                Log::info("🔍 HOJE - Filtrando: dia={$dia}, mes={$mes}");
                $query->whereRaw("EXTRACT(DAY FROM data_nascimento) = ?", [$dia])
                      ->whereRaw("EXTRACT(MONTH FROM data_nascimento) = ?", [$mes]);
                break;
                
            case 'semana':
                $startOfWeek = Carbon::now()->startOfWeek();
                $endOfWeek = Carbon::now()->endOfWeek();
                $startFormat = $startOfWeek->format('md');
                $endFormat = $endOfWeek->format('md');
                
                Log::info("🔍 SEMANA - Filtrando: {$startFormat} até {$endFormat}");

                if ($startFormat > $endFormat) {
                    $query->where(function ($q) use ($startFormat, $endFormat) {
                        $q->whereRaw("TO_CHAR(data_nascimento, 'MMDD') >= ?", [$startFormat])
                          ->orWhereRaw("TO_CHAR(data_nascimento, 'MMDD') <= ?", [$endFormat]);
                    });
                } else {
                    $query->whereRaw("TO_CHAR(data_nascimento, 'MMDD') BETWEEN ? AND ?", [$startFormat, $endFormat]);
                }
                break;
                
            case 'mes':
                $mes = date('m');
                Log::info("🔍 MÊS - Filtrando: mes={$mes}");
                $query->whereRaw("EXTRACT(MONTH FROM data_nascimento) = ?", [$mes]);
                break;
        }

        // Log da SQL query ANTES de executar
        $sqlQuery = $query->toSql();
        $bindings = $query->getBindings();
        Log::info('📝 SQL Query: ' . $sqlQuery);
        Log::info('📝 Bindings: ' . json_encode($bindings));

        // Executar a query
        $aniversariantes = $query->orderByRaw("TO_CHAR(data_nascimento, 'MMDD')")->paginate(10);

        Log::info('✅ Total encontrado: ' . $aniversariantes->total());
        Log::info('📦 Registros na página: ' . $aniversariantes->count());
        Log::info('📋 IDs retornados: ' . $aniversariantes->pluck('id')->toJson());
        Log::info('📋 Dados completos: ' . json_encode($aniversariantes->items()));
        Log::info('🎂 ========== FIM ==========');

        return response()->json($aniversariantes);
    }

    /**
     * Retorna estatísticas sobre os aniversariantes.
     */
    public function stats()
    {
        $hoje = Carbon::now();

        $totalHoje = User::whereHas('colaborador', function ($q) {
                $q->where('status', 'Ativo');
            })
            ->whereNotNull('data_nascimento')
            ->whereDay('data_nascimento', $hoje->day)
            ->whereMonth('data_nascimento', $hoje->month)
            ->count();

        $startOfWeek = $hoje->copy()->startOfWeek();
        $endOfWeek = $hoje->copy()->endOfWeek();
        $startFormat = $startOfWeek->format('md');
        $endFormat = $endOfWeek->format('md');

        $totalSemanaQuery = User::whereHas('colaborador', function ($q) {
            $q->where('status', 'Ativo');
        })->whereNotNull('data_nascimento');

        if ($startFormat > $endFormat) {
            $totalSemanaQuery->where(function ($q) use ($startFormat, $endFormat) {
                $q->whereRaw("TO_CHAR(data_nascimento, 'MMDD') >= ?", [$startFormat])
                  ->orWhereRaw("TO_CHAR(data_nascimento, 'MMDD') <= ?", [$endFormat]);
            });
        } else {
            $totalSemanaQuery->whereRaw("TO_CHAR(data_nascimento, 'MMDD') BETWEEN ? AND ?", [$startFormat, $endFormat]);
        }
        $totalSemana = $totalSemanaQuery->count();

        $totalMes = User::whereHas('colaborador', function ($q) {
                $q->where('status', 'Ativo');
            })
            ->whereNotNull('data_nascimento')
            ->whereMonth('data_nascimento', $hoje->month)
            ->count();

        return response()->json([
            'hoje' => $totalHoje,
            'semana' => $totalSemana,
            'mes' => $totalMes,
        ]);
    }

    /**
     * Retorna os próximos aniversariantes (próximos 7 dias).
     */
    public function proximos()
    {
        $today = Carbon::now();
        $endDate = Carbon::now()->addDays(7);
        $startFormat = $today->format('md');
        $endFormat = $endDate->format('md');

        $query = User::select(
            'users.id',
            'users.name as nome_completo',
            'users.data_nascimento',
            'colaboradores.foto_perfil_sistema as foto_perfil_url'
        )
        ->join('colaboradores', 'users.id', '=', 'colaboradores.user_id')
        ->where('colaboradores.status', 'Ativo')
        ->whereNotNull('users.data_nascimento');

        if ($startFormat > $endFormat) {
            $query->where(function ($q) use ($startFormat, $endFormat) {
                $q->whereRaw("TO_CHAR(data_nascimento, 'MMDD') >= ?", [$startFormat])
                  ->orWhereRaw("TO_CHAR(data_nascimento, 'MMDD') <= ?", [$endFormat]);
            });
        } else {
            $query->whereRaw("TO_CHAR(data_nascimento, 'MMDD') BETWEEN ? AND ?", [$startFormat, $endFormat]);
        }

        $aniversariantes = $query->orderByRaw("TO_CHAR(data_nascimento, 'MMDD')")
        ->take(10)
        ->get();

        return response()->json($aniversariantes);
    }
}

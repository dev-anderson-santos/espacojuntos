<?php

namespace App\Http\Controllers\Admin;

use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\ScheduleModel;
use App\Models\SettingsModel;
use App\Models\SettingsModelLog;
use App\Http\Controllers\Controller;

class AdministratorController extends Controller
{
    public function dashboard()
    {
        $totalSchedulesInMonth = ScheduleModel::whereHas('user', function($query) {
            $query->where('email', '!=', 'danielamontechiaregentil@gmail.com');
        })
        ->whereBetween('date', [
            now()->startOfMonth()->format('Y-m-d'),
            now()->endOfMonth()->format('Y-m-d')
        ])->get()->count();

        $activeUsers = ScheduleModel::distinct()->select('user_id')
        ->whereBetween('date', [
            now()->startOfMonth()->format('Y-m-d'),
            now()->endOfMonth()->format('Y-m-d')
        ])->get()->count();

        $users = User::all();

        $schedules = ScheduleModel::where([
            'faturado' => 1,
        ])
        // ->leftJoin('users', 'users.id', 'schedules.user_id')
        ->whereBetween('date', [
            now()->subMonth()->startOfMonth()->format('Y-m-d'),
            now()->subMonth()->endOfMonth()->format('Y-m-d')
        ])
        ->get();

        return view('administrator.dashboard', [
            'totalSchedulesInMonth' => $totalSchedulesInMonth,
            'activeUsers' => $activeUsers,
            'users' => $users,
            'schedules' => $schedules,
            'clientesMesAnterior' => $this->fechamentosDoMesAnterior(),
            'clientesMesAtual' => $this->fechamentosParcialDoMes()
        ]);
    }

    public function fechamentosParcialDoMes()
    {
        $setting = SettingsModel::first();
        $valorFixo = $setting->valor_fixo;
        $valorAvulso = $setting->valor_avulso;

        $clientes = User::where('is_admin', '!=', 1)->get()->map(function($cliente) use ($valorAvulso, $valorFixo) {

            $concluidosParcialAtivoFixo = ScheduleModel::where([
                            'user_id' => $cliente->id,
                            'status' => 'Ativo',
                            'tipo' => 'Fixo'
                        ])
                        ->where('faturado', 0)
                        ->whereMonth('date', Carbon::now()->format('m'))
                        ->whereNull('data_nao_faturada_id')
                        ->get();
    
            $concluidosParcialAtivoAvulso = ScheduleModel::where([
                            'user_id' => $cliente->id,
                            'status' => 'Ativo',
                            'tipo' => 'Avulso'
                        ])
                        ->where('faturado', 0)
                        ->whereMonth('date', Carbon::now()->format('m'))
                        ->whereNull('data_nao_faturada_id')
                        ->get();
    
            $concluidosParcialFinalizadoFixo = ScheduleModel::where([
                            'user_id' => $cliente->id,
                            'status' => 'Finalizado',
                            'tipo' => 'Fixo'
                        ])
                        ->where('faturado', 0)
                        ->whereMonth('date', Carbon::now()->format('m'))
                        ->whereNull('data_nao_faturada_id')
                        ->get();
    
            $concluidosParcialFinalizadoAvulso = ScheduleModel::where([
                            'user_id' => $cliente->id,
                            'status' => 'Finalizado',
                            'tipo' => 'Avulso'
                        ])
                        ->where('faturado', 0)
                        ->whereMonth('date', Carbon::now()->format('m'))
                        ->whereNull('data_nao_faturada_id')
                        ->get();
    
            $cliente->concluidosParcialAgendamentos = $concluidosParcialAtivoFixo->count() + $concluidosParcialAtivoAvulso->count() + $concluidosParcialFinalizadoFixo->count() + $concluidosParcialFinalizadoAvulso->count();
    
            // $totalParcialAgendamentos = ScheduleModel::where([
            //                 'user_id' => $cliente->id,                        
            //                 'status' => 'Ativo',
            //             ])
            //             ->whereMonth('date', Carbon::now()->format('m'))
            //             ->get();
            
            // é preciso calcular com o valor fixo e o valor avulso
            // Veirificar se algums horario foi escolhido como avulso
            $totalAvulso = 0;
            if ($concluidosParcialAtivoAvulso->count() > 0) {
                $totalAvulso = $concluidosParcialAtivoAvulso->count() * $valorAvulso;
            }
            if ($concluidosParcialFinalizadoAvulso->count() > 0) {
                $totalAvulso += $concluidosParcialFinalizadoAvulso->count() * $valorAvulso;
            }
    
            $totalFixo = 0;
            if ($concluidosParcialAtivoFixo->count() > 0) {
                $totalFixo = $concluidosParcialAtivoFixo->count() * $valorFixo;
            }
            if ($concluidosParcialFinalizadoFixo->count() > 0) {
                $totalFixo += $concluidosParcialFinalizadoFixo->count() * $valorFixo;
            }
    
            $cliente->totalParcialValor = $totalAvulso + $totalFixo;

            return $cliente;
        });

        return $clientes;
    }

    private function fechamentosDoMesAnterior()
    {
        $setting = SettingsModel::first();
        $valorFixo = $setting->valor_fixo;
        $valorAvulso = $setting->valor_avulso;

        $clientes = User::where('is_admin', '!=', 1)->get()->map(function($cliente) use ($valorAvulso, $valorFixo) {
            $concluidosMesAnteriorAvulso = ScheduleModel::where([
                'user_id' => $cliente->id,
                'status' => 'Finalizado',
                'tipo' => 'Avulso',
                'faturado' => 1
            ])
            ->whereIn('tipo', ['Fixo', 'Avulso'])
            ->whereMonth('date', Carbon::now()->firstOfMonth()->subMonths()->format('m'))
            ->whereNull('data_nao_faturada_id')
            ->get();

            $concluidosMesAnteriorFixo = ScheduleModel::where([
                        'user_id' => $cliente->id,
                        'status' => 'Finalizado',
                        'tipo' => 'Fixo',
                        'faturado' => 1
                    ])
                    ->whereMonth('date', Carbon::now()->firstOfMonth()->subMonths()->format('m'))
                    ->whereNull('data_nao_faturada_id')
                    ->get();

            $cliente->concluidosAgendamentosMesAnterior = $concluidosMesAnteriorAvulso->count() + $concluidosMesAnteriorFixo->count();

            // é preciso calcular com o valor fixo e o valor avulso
            // Veirificar se algums horario foi escolhido como avulso
            $totalAvulsoMesAnterior = 0;
            if ($concluidosMesAnteriorAvulso->count() > 0) {
            $totalAvulsoMesAnterior = $concluidosMesAnteriorAvulso->count() * $valorAvulso;
            }

            $totalFixoMesAnterior = 0;
            if ($concluidosMesAnteriorFixo->count() > 0) {
            $totalFixoMesAnterior = $concluidosMesAnteriorFixo->count() * $valorFixo;
            }

            $cliente->totalMesAnterior = $totalAvulsoMesAnterior + $totalFixoMesAnterior;

            return $cliente;
        });

        return $clientes;
    }
}
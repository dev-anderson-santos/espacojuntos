<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SettingsModel extends Model
{
    protected $table = 'settings';
    protected $fillable = [
        'valor_fixo',
        'valor_avulso',
        'hora_fechamento',
        'dia_fechamento',
        'data_vencimento',
    ];
}

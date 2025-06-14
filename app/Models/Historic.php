<?php

namespace App\Models;

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Historic extends Model
{
    protected $id = 'id';
    protected $table = 'historic';
    protected $fillable = [
        'action',
        'user_id',
        'room_id',
        'created_by',
        'hour_id',
        'date',
        'status',
        'tipo',
        'faturado',
        'finalizado_em',
        'data_nao_faturada_id',
        'last_login_time',
        'last_login_ip',
        'deleted_by',
        'scheduleForNextMonth'
    ];
    public $with = ['userHasDelete', 'user'];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function userHasDelete()
    {
        return $this->hasOne(User::class, 'id', 'deleted_by');
    }

    public function userCreatedBy()
    {
        return $this->hasOne(User::class, 'id', 'created_by');
    }

    public function room()
    {
        return $this->hasOne(RoomModel::class, 'id', 'room_id');
    }

    public function roomDeleted()
    {
        return $this->hasOne(RoomModel::class, 'id', 'room_id')->withTrashed();
    }

    public function hour()
    {
        return $this->hasOne(HourModel::class, 'id', 'hour_id');
    }

    public function getLastLoginAttribute()
    {
        return Carbon::parse($this->last_login_time)->format('d/m/Y H:i:s');
    }

    public function getDataAttribute()
    {
        return Carbon::parse($this->date)->format('d/m/Y');
    }

    public function getCriadoEmAttribute()
    {
        return Carbon::parse($this->created_at)->format('d/m/Y H:i:s');
    }
}

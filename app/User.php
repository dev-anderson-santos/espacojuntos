<?php

namespace App;

use Carbon\Carbon;
use App\Models\ChargeModel;
use App\Jobs\QueuedPasswordResetJob;
use App\Models\UserHasAddressesModel;
use Illuminate\Notifications\Notifiable;
use App\Notifications\Auth\ResetPassword;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable, SoftDeletes;

    protected $table = 'users';
    protected $fillable = [
        'id',
        'name',
        'email',
        'password',
        'username',
        'phone',
        'email_verified_at',
        'cpf',
        'inscricao_crp_crm',
        'birth_date',
        'academic_formations',
        'syndromes_special_situations_experience',
        'age_range_service',
        'approach_lines',
        'is_admin',
        'status',
        'last_login_time',
        'last_login_ip'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function hasAddress(): HasOne
    {
        return $this->hasOne(UserHasAddressesModel::class, 'user_id', 'id');
    }

    public function charge()
    {
        return $this->hasOne(ChargeModel::class, 'user_id', 'id');
    }

    public function sendPasswordResetNotification($token)
    {
        $when = Carbon::now()->addSeconds(10);

        $this->notify((new ResetPassword($token))->delay($when));
    }
}

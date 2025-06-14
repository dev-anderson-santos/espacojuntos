<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserHasAddressesModel extends Model
{
    protected $table = 'users_has_addresses';
    protected $fillable = ['user_id', 'address_id'];
    public $with = ['address'];

    public function user()
    {
        return $this->belongsTo(UserModel::class, 'user_id');
    }

    public function address()
    {
        return $this->belongsTo(AddressModel::class, 'address_id');
    }
}

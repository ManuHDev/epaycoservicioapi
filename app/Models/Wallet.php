<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $fillable = array('user_id', 'type', 'amount', 'token', 'status');
}

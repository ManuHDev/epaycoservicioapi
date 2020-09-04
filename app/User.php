<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use App\Models\Wallet;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'document', 'cellphone'
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

    public function validateDataUser($document, $cellphone)
    {
        return $this->where('document', $document)
                    ->where('cellphone', $cellphone)
                    ->first();
    }

    public function transactions()
    {
        return $this->hasMany(Wallet::class);
    }

    public function transaction($id) 
    {
        return $this->transactions()->where('id', $id);
    }

    public function validTransactions()
    {
        return $this->transactions()->where('status', 1);
    }

    public function credit()
    {
        return $this->validTransactions()
                    ->where('type', 'credit')
                    ->sum('amount');
    }

    public function debit()
    {
        return $this->validTransactions()
                    ->where('type', 'debit')
                    ->sum('amount');
    }

    public function balance()
    {
        return $this->credit() - $this->debit();
    }

    public function allowPurchase($amount) : bool
    {
        return $this->balance() >= $amount;
    }

    public function confirmPayment($token)
    {
        return $this->transactions()
                    ->where('type', 'debit')
                    ->where('status', 0)
                    ->where('token', $token)
                    ->first();
    }
}

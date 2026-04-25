<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'payment_method_id',
        'transaction_number',
        'name',
        'email',
        'phone',
        'address',
        'notes',
        'subtotal',
        'diskon',
        'total',
        'cash_received',
        'change',
        'is_bpjs',
        'user_id',
        'jasa_dokter',
        'jasa_tindakan',
    ];

    protected $casts = [
        'is_bpjs' => 'boolean',
        'total' => 'integer',
        'cash_received' => 'integer',
        'change' => 'integer',
        'subtotal' => 'integer',
        'diskon' => 'integer',
        'jasa_dokter' => 'integer',
        'jasa_tindakan' => 'integer',
    ];

    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function products()
    {
        return $this->transactionItems()->with('product');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

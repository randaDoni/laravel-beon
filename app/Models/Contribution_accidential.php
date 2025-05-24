<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contribution_accidential extends Model
{
    protected $fillable = [
        'house_id',
        'bulan',
        'tahun',
        'item_id',
        'tipe_pembayaran',
        'status_pembayaran',
        'contribution_total',
    ];

    public function masterDataMonthlyPayment()
    {
        return $this->belongsTo(AccidentialDataMonthlyPayment::class, 'master_data_monthly_payment_id');
    }
}

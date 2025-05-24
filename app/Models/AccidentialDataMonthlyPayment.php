<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccidentialDataMonthlyPayment extends Model
{
    protected $fillable = ['name','price','bulan','tahun'];
}

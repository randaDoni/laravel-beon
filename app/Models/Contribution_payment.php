<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

    class Contribution_payment extends Model
    {
        protected $fillable = ['house_id', 'payment_type','payment_status','notes', 'date','total'];
    }

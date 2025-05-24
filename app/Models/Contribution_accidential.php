<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contribution_accidential extends Model
{
    protected $fillable = ['house_id','date','payment_type', 'payment_status','contribution_total'];
}

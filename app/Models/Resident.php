<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resident extends Model
{
    protected $fillable = ['name', 'ktp','contract_status','married_status'];

    public function houseResidents()
    {
        return $this->hasMany(House_resident::class);
    }

}

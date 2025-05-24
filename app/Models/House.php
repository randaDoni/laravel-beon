<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class House extends Model
{
    protected $fillable = ['alamat','resident_status'];
    public function houseResidents()
    {
        return $this->hasMany(House_resident::class);
    }
}

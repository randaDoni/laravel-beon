<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class House_resident extends Model
{

    protected $fillable = ['bulan','tahun','house_id','resident_id','tipe_hunian','date_of_entry','exit_date'];

    public function resident()
    {
        return $this->belongsTo(Resident::class);
    }

    public function house()
    {
        return $this->belongsTo(House::class);
    }
}

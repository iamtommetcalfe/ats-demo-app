<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackgroundCheckStep extends Model
{
    protected $fillable = [
        'background_check_id',
        'amiqus_step_id',
        'type',
        'cost',
    ];

    public function backgroundCheck()
    {
        return $this->belongsTo(BackgroundCheck::class);
    }
}

<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BackgroundCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'amiqus_record_id', 'perform_url', 'status', 'applicant_id'
    ];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }
}

<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Applicant extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name', 'last_name', 'title', 'email', 'status', 'amiqus_client_id'
    ];

    public function backgroundChecks()
    {
        return $this->hasMany(BackgroundCheck::class);
    }
}

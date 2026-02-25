<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/* 2023 SilverDust) S. Maceren */

class OfficialReceipt extends Model
{
    protected $table = 'tblofficialreceipt';
    public $timestamps = false;

    use HasFactory;
    protected $fillable = [
        'orbatchid',
        'ornumber',
        'status',
        'remarks'
    ];

    // Relationship with OrBatch
    public function orBatch()
    {
        return $this->belongsTo(OrBatch::class, 'orbatchid', 'id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanWaiver extends Model
{
    use HasFactory;

    protected $table = 'loan_waivers';
    public $timestamps = true;

    protected $fillable = [
        'loan_request_id',
        'signed_date',
        'signature_data',
        'client_name',
        'contract_number'
    ];

    protected $casts = [
        'signed_date' => 'datetime'
    ];

    /**
     * Relationship: Loan Request
     */
    public function loanRequest()
    {
        return $this->belongsTo(LoanRequest::class, 'loan_request_id');
    }
}

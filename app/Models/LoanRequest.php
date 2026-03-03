<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanRequest extends Model
{
    protected $table = 'tblloanrequest';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    use HasFactory;
    protected $fillable = [
        'clientid',
        'contract_id',
        'amount',
        'net_loan_amount',
        'processing_fee',
        'interest_rate',
        'monthlyamount',
        'term_months',
        'total_repayable',
        'daterequested',
        'status',
        'remarks',
        'code',
        'waiver_signed',
        'waiver_signed_date',
        'premium_paid_percent',
        'datecreated',
        'createdby',
        'approvedby',
        'approveddate',
        'completeddate'
    ];

    /**
     * Scope: Loans due within specified days
     */
    public function scopeDueInDays($query, int $days)
    {
        $targetDate = now()->addDays($days);
        return $query->where('status', 'Approved')
                     ->whereNull('completeddate')
                     ->whereDate('approveddate', '<=', $targetDate);
    }

    /**
     * Scope: Lapsed loans (91+ days overdue)
     */
    public function scopeLapsed($query)
    {
        $lapseDate = now()->subDays(91);
        return $query->where('status', 'Approved')
                     ->whereNull('completeddate')
                     ->whereDate('approveddate', '<=', $lapseDate);
    }

    /**
     * Scope: Filter by branch
     */
    public function scopeByBranch($query, int $branchId)
    {
        return $query->whereHas('client', function($q) use ($branchId) {
            $q->where('branchid', $branchId);
        });
    }

    /**
     * Scope: Active loans (approved, not completed)
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'Approved')
                     ->whereNull('completeddate');
    }

    /**
     * Relationship: Client
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'clientid', 'Id');
    }

    /**
     * Relationship: Contract
     */
    public function contract()
    {
        return $this->belongsTo(Contract::class, 'contract_id', 'Id');
    }

    /**
     * Relationship: Loan Payments
     */
    public function loanPayments()
    {
        return $this->hasMany(LoanPayment::class, 'loanrequestid', 'Id');
    }

    /**
     * Relationship: Waiver
     */
    public function waiver()
    {
        return $this->hasOne(LoanWaiver::class, 'loan_request_id');
    }

    /**
     * Calculate total payments made
     */
    public function getTotalPaymentsAttribute(): float
    {
        return $this->loanPayments()->sum('amount') ?? 0;
    }

    /**
     * Calculate remaining balance
     */
    public function getRemainingBalanceAttribute(): float
    {
        return max(0, $this->total_repayable - $this->total_payments);
    }

    /**
     * Check if loan is fully paid
     */
    public function getIsFullyPaidAttribute(): bool
    {
        return $this->remaining_balance <= 0;
    }

    /**
     * Get days until next payment due
     */
    public function getDaysUntilDueAttribute(): ?int
    {
        if (!$this->approveddate || $this->status !== 'Approved') {
            return null;
        }

        $lastPayment = $this->loanPayments()->latest('paymentdate')->first();
        $referenceDate = $lastPayment ? $lastPayment->paymentdate : $this->approveddate;
        $nextDueDate = \Carbon\Carbon::parse($referenceDate)->addMonth();

        return now()->diffInDays($nextDueDate, false);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sms extends Model
{
    protected $table = 'tblsms';
    public $timestamps = true;
    
    // Match actual column names (case-sensitive)
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    
    use HasFactory;
    
    protected $fillable = [
        'ContactNo',
        'Message',
        'SendTo',
        'Status',
        'gateway_response',
        'sent_at',
        'reference_type',
        'reference_id',
    ];
    
    protected $casts = [
        'sent_at' => 'datetime',
    ];
    
    // Status constants
    const STATUS_PENDING = 0;
    const STATUS_SENT = 1;
    const STATUS_FAILED = 2;
    
    /**
     * Scope for pending messages
     */
    public function scopePending($query)
    {
        return $query->where('Status', self::STATUS_PENDING);
    }
    
    /**
     * Scope for sent messages
     */
    public function scopeSent($query)
    {
        return $query->where('Status', self::STATUS_SENT);
    }
    
    /**
     * Scope for failed messages
     */
    public function scopeFailed($query)
    {
        return $query->where('Status', self::STATUS_FAILED);
    }
    
    /**
     * Check if message is pending
     */
    public function isPending(): bool
    {
        return $this->Status === self::STATUS_PENDING;
    }
    
    /**
     * Check if message was sent
     */
    public function isSent(): bool
    {
        return $this->Status === self::STATUS_SENT;
    }
    
    /**
     * Check if message failed
     */
    public function isFailed(): bool
    {
        return $this->Status === self::STATUS_FAILED;
    }
}

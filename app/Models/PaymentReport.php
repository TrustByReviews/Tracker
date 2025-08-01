<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\HasUuid;

class PaymentReport extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'user_id',
        'week_start_date',
        'week_end_date',
        'total_hours',
        'hourly_rate',
        'total_payment',
        'completed_tasks_count',
        'in_progress_tasks_count',
        'task_details',
        'status',
        'approved_by',
        'approved_at',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'week_start_date' => 'date',
        'week_end_date' => 'date',
        'total_hours' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'total_payment' => 'decimal:2',
        'task_details' => 'array',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    // Relaciones
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForWeek($query, $startDate, $endDate)
    {
        return $query->where('week_start_date', $startDate)
                    ->where('week_end_date', $endDate);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    // Métodos
    public function approve($approvedByUserId)
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $approvedByUserId,
            'approved_at' => now(),
        ]);
    }

    public function markAsPaid()
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }

    public function cancel()
    {
        $this->update(['status' => 'cancelled']);
    }

    public function getFormattedTotalPaymentAttribute()
    {
        return '$' . number_format($this->total_payment, 2);
    }

    public function getFormattedTotalHoursAttribute()
    {
        return number_format($this->total_hours, 2) . ' hrs';
    }

    public function getWeekRangeAttribute()
    {
        return $this->week_start_date->format('M d') . ' - ' . $this->week_end_date->format('M d, Y');
    }
}

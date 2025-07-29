<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class WeeklyReport extends Model
{
    use HasUuid;

    protected $table = 'weekly_reports';

    protected $fillable = [
        'start_date',
        'end_date',
        'total_payment',
        'developers_count',
        'report_data',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_payment' => 'decimal:2',
        'developers_count' => 'integer',
        'report_data' => 'array',
    ];

    /**
     * Get the formatted total payment
     */
    public function getFormattedTotalPaymentAttribute(): string
    {
        return '$' . number_format($this->total_payment, 2);
    }

    /**
     * Get the period as a string
     */
    public function getPeriodAttribute(): string
    {
        return $this->start_date->format('M d') . ' - ' . $this->end_date->format('M d, Y');
    }

    /**
     * Scope to get reports for a specific date range
     */
    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('start_date', [$startDate, $endDate]);
    }

    /**
     * Scope to get recent reports
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
} 
<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    protected $table = 'tasks';

    use softDeletes, HasUuid;

    protected $fillable = [
        'name',
        'description',
        'status',
        'priority',
        'category',
        'story_points',
        'sprint_id',
        'project_id',
        'user_id',
        'estimated_start',
        'estimated_finish',
        'estimated_hours',
        'actual_start',
        'actual_finish',
        'actual_hours',
        'rejection_reason',
        'rejected_by',
        'rejected_at',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'name' => 'string',
        'description' => 'string',
        'status' => 'string',
        'priority' => 'string',
        'category' => 'string',
        'story_points' => 'integer',
        'sprint_id' => 'string',
        'project_id' => 'string',
        'user_id' => 'string',
        'estimated_start' => 'date',
        'estimated_finish' => 'date',
        'estimated_hours' => 'integer',
        'actual_start' => 'date',
        'actual_finish' => 'date',
        'actual_hours' => 'integer',
        'rejected_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sprint(): BelongsTo
    {
        return $this->belongsTo(Sprint::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}

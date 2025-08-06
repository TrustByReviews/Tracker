<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BugComment extends Model
{
    protected $table = 'bug_comments';

    use HasUuid;

    protected $fillable = [
        'bug_id',
        'user_id',
        'content',
        'attachments',
        'is_internal',
        'comment_type',
    ];

    protected $casts = [
        'bug_id' => 'string',
        'user_id' => 'string',
        'content' => 'string',
        'attachments' => 'array',
        'is_internal' => 'boolean',
        'comment_type' => 'string',
    ];

    // Tipos de comentarios
    const TYPE_GENERAL = 'general';
    const TYPE_RESOLUTION = 'resolution';
    const TYPE_VERIFICATION = 'verification';
    const TYPE_REPRODUCTION = 'reproduction';
    const TYPE_INTERNAL = 'internal';

    public function bug(): BelongsTo
    {
        return $this->belongsTo(Bug::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtener el color del tipo de comentario
     */
    public function getCommentTypeColor(): string
    {
        return match($this->comment_type) {
            self::TYPE_RESOLUTION => 'bg-green-100 text-green-800',
            self::TYPE_VERIFICATION => 'bg-purple-100 text-purple-800',
            self::TYPE_REPRODUCTION => 'bg-blue-100 text-blue-800',
            self::TYPE_INTERNAL => 'bg-gray-100 text-gray-800',
            default => 'bg-white text-gray-800',
        };
    }

    /**
     * Obtener el icono del tipo de comentario
     */
    public function getCommentTypeIcon(): string
    {
        return match($this->comment_type) {
            self::TYPE_RESOLUTION => 'check-circle',
            self::TYPE_VERIFICATION => 'shield-check',
            self::TYPE_REPRODUCTION => 'refresh-cw',
            self::TYPE_INTERNAL => 'lock',
            default => 'message-circle',
        };
    }
} 
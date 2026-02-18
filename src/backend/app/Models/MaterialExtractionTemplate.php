<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * MaterialExtractionTemplate - Stores AI-extracted material candidates
 * 
 * Status workflow: pending → approved/rejected
 */
class MaterialExtractionTemplate extends Model
{
    protected $fillable = [
        'source_type',
        'source_url',
        'raw_data',
        'extracted_concepts',
        'material_template',
        'status',
        'validation_result',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'raw_data' => 'array',
        'extracted_concepts' => 'array',
        'material_template' => 'array',
        'validation_result' => 'array',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the user who approved this template.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope: Pending templates.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Approved templates.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope: Rejected templates.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Approve this template.
     */
    public function approve(string $userId, ?string $notes = null): void
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => now(),
            'notes' => $notes,
        ]);
    }

    /**
     * Reject this template.
     */
    public function reject(string $userId, ?string $notes = null): void
    {
        $this->update([
            'status' => 'rejected',
            'approved_by' => $userId,
            'approved_at' => now(),
            'notes' => $notes,
        ]);
    }

    /**
     * Check if template is valid.
     */
    public function isValid(): bool
    {
        return $this->validation_result['valid'] ?? false;
    }

    /**
     * Get validation errors.
     */
    public function getValidationErrors(): array
    {
        return $this->validation_result['errors'] ?? [];
    }

    /**
     * Get validation warnings.
     */
    public function getValidationWarnings(): array
    {
        return $this->validation_result['warnings'] ?? [];
    }
}

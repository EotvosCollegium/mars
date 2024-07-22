<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationWorkshop extends Pivot
{
    use HasFactory;

    protected $table = 'application_workshops';

    protected $fillable = [
        'workshop_id',
        'application_id',
        'called_in',
        'admitted'
    ];

    protected $casts = [
        'called_in' => 'bool',
        'admitted' => 'bool'
    ];

    /**
     * The original workshop model that the applicant applies to.
     * @return BelongsTo
     */
    public function workshop(): BelongsTo
    {
        return $this->belongsTo(Workshop::class);
    }

    /**
     * The application that applies to workshops.
     * @return BelongsTo
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model to keep track of the users' free pages.
 * Changes are logged in print_account_history table. See FreePagesObserver.
 * @property mixed $user_id
 */
class FreePages extends Model
{
    use HasFactory;

    protected $table = 'printing_free_pages';
    protected $primaryKey = 'id';
    public $incrementing = false;
    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'amount',
        'deadline',
        'last_modified_by',
        'comment',
    ];

    protected $casts = [
        'deadline' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function printAccount()
    {
        return $this->belongsTo(PrintAccount::class, 'user_id', 'user_id');
    }

    protected function getAvailableAttribute()
    {
        return now()->isBefore($this->deadline);
    }

    public function modifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_modified_by');
    }
}

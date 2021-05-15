<?php


namespace App\Models\Eloquent;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportLog extends Model
{
    protected $table = 'import_logs';

    protected $guarded = [];

    protected $casts = [
        'context' => 'json'
    ];

    /**
     * @return BelongsTo
     */
    public function importJob() : BelongsTo
    {
        return $this->belongsTo(ImportJob::class, 'import_jobs_id');
    }
}

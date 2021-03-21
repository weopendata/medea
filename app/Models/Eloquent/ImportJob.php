<?php


namespace App\Models\Eloquent;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportJob extends Model
{
    protected $table = 'import_jobs';

    protected $guarded = [];

    /**
     * @return BelongsTo
     */
    public function fileUpload() : BelongsTo
    {
        return $this->belongsTo(FileUpload::class, 'import_files_id');
    }
}

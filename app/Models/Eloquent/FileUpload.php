<?php


namespace App\Models\Eloquent;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FileUpload extends Model
{
    protected $table = 'import_files';

    protected $guarded = [];

    protected $casts = [
      'last_imported' => 'datetime:Y-m-d H:m:s'
    ];

    /**
     * @return HasMany
     */
    public function importJobs() : HasMany
    {
        return $this->hasMany(ImportJob::class, 'import_files_id');
    }
}

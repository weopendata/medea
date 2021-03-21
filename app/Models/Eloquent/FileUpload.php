<?php


namespace App\Models\Eloquent;


use Illuminate\Database\Eloquent\Model;

class FileUpload extends Model
{
    protected $table = 'import_files';

    protected $guarded = [];

    protected $casts = [
      'last_imported' => 'datetime:Y-m-d H:m:s'
    ];
}

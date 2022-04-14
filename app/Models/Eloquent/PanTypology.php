<?php


namespace App\Models\Eloquent;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PanTypology extends Model
{
    protected $table = 'pan_reference_typology';

    protected $guarded = [];

    protected $casts = [
        'meta' => 'json',
    ];

    /**
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(PanTypology::class, 'parent_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(PanTypology::class, 'parent_id', 'id');
    }
}

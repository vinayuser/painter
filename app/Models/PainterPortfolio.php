<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['painter_id', 'title', 'image_path', 'description'])]
class PainterPortfolio extends Model
{
    public function painter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'painter_id');
    }
}

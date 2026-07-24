<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable(['category_id', 'vendor_id', 'name', 'slug', 'description', 'price', 'stock_quantity', 'is_active', 'is_featured'])]
class Product extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock_quantity' => 'integer',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Product $product): void {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });

        // Soft delete keeps order history (FK RESTRICT). Clear carts so shoppers don't keep dead items.
        static::deleting(function (Product $product): void {
            if ($product->isForceDeleting()) {
                return;
            }

            $product->cartItems()->delete();
            $product->is_active = false;
            // Free slug so a new product can reuse the same name/slug.
            $product->slug = $product->slug.'__deleted_'.$product->id;
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage(): HasMany
    {
        return $this->hasMany(ProductImage::class)->where('is_primary', true);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function primaryListingImage(): ?ProductImage
    {
        if (! $this->relationLoaded('images')) {
            $this->load('images');
        }

        return $this->images->firstWhere('is_primary', true) ?? $this->images->first();
    }
}

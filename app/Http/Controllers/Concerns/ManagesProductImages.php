<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait ManagesProductImages
{
    /**
     * @param  array<int, UploadedFile>|UploadedFile|null  $legacySingle
     */
    protected function syncProductImages(Request $request, Product $product): void
    {
        $removeIds = array_filter(array_map('intval', (array) $request->input('remove_image_ids', [])));

        if ($removeIds !== []) {
            $product->images()
                ->whereIn('id', $removeIds)
                ->get()
                ->each(function (ProductImage $image): void {
                    Storage::disk('public')->delete($image->image_path);
                    $image->delete();
                });
        }

        $files = [];

        foreach ((array) $request->file('images', []) as $file) {
            if ($file instanceof UploadedFile) {
                $files[] = $file;
            }
        }

        // Backward compatible single-file field.
        if ($request->hasFile('image') && $request->file('image') instanceof UploadedFile) {
            $files[] = $request->file('image');
        }

        $sort = (int) $product->images()->max('sort_order');
        $hasPrimary = $product->images()->where('is_primary', true)->exists();

        foreach ($files as $index => $file) {
            $sort++;

            ProductImage::query()->create([
                'product_id' => $product->id,
                'image_path' => $file->store('products', 'public'),
                'is_primary' => ! $hasPrimary && $index === 0,
                'sort_order' => $sort,
            ]);

            $hasPrimary = true;
        }

        if ($request->filled('primary_image_id')) {
            $primaryId = (int) $request->input('primary_image_id');
            if ($product->images()->whereKey($primaryId)->exists()) {
                $product->images()->update(['is_primary' => false]);
                $product->images()->whereKey($primaryId)->update(['is_primary' => true]);
            }
        }

        if (! $product->images()->where('is_primary', true)->exists()) {
            $first = $product->images()->orderBy('sort_order')->first();
            $first?->update(['is_primary' => true]);
        }
    }
}

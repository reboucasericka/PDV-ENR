<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ProductService
{
    public function list(array $filters = []): Collection
    {
        $query = Product::query()->with('category');

        if (array_key_exists('is_active', $filters) && $filters['is_active'] !== null && $filters['is_active'] !== '') {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', (int) $filters['category_id']);
        }

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('id', $search);
            });
        }

        if (array_key_exists('is_favorite', $filters) && $filters['is_favorite'] !== null && $filters['is_favorite'] !== '') {
            $query->where('is_favorite', filter_var($filters['is_favorite'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query
            ->orderByDesc('is_favorite')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function listActiveForPos(?int $categoryId = null, ?string $search = null): Collection
    {
        $query = Product::query()
            ->with('category')
            ->where('is_active', true)
            ->whereHas('category', fn ($builder) => $builder->where('is_active', true));

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($search) {
            $term = trim($search);
            $query->where(function ($builder) use ($term) {
                $builder
                    ->where('name', 'like', "%{$term}%")
                    ->orWhere('sku', 'like', "%{$term}%")
                    ->orWhere('id', $term);
            });
        }

        return $query
            ->orderByDesc('is_favorite')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function find(int $id): Product
    {
        return Product::query()->with('category')->findOrFail($id);
    }

    public function create(array $data): Product
    {
        $data['is_active'] = $data['is_active'] ?? true;
        $data['is_favorite'] = $data['is_favorite'] ?? false;
        $data['sort_order'] = $data['sort_order'] ?? $this->nextSortOrder($data['category_id'] ?? null);
        $data['stock'] = $data['stock'] ?? 0;

        return Product::query()->create($data)->load('category');
    }

    public function update(int $id, array $data): Product
    {
        $product = $this->find($id);
        $product->update($data);

        return $product->fresh()->load('category');
    }

    public function setActive(int $id, bool $active): Product
    {
        $product = $this->find($id);
        $product->update(['is_active' => $active]);

        return $product->fresh()->load('category');
    }

    public function delete(int $id): void
    {
        $product = $this->find($id);

        if ($product->orderItems()->exists()) {
            throw new InvalidArgumentException('Nao e possivel excluir este produto porque existem vendas vinculadas. Desative-o para preservar o historico.');
        }

        DB::transaction(fn () => $product->delete());
    }

    private function nextSortOrder(?int $categoryId): int
    {
        return ((int) Product::query()
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->max('sort_order')) + 1;
    }
}

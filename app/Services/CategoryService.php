<?php

namespace App\Services;

use App\Models\Category;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CategoryService
{
    public function list(array $filters = []): Collection
    {
        $query = Category::query()->withCount('products');

        if (array_key_exists('is_active', $filters) && $filters['is_active'] !== null && $filters['is_active'] !== '') {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where('name', 'like', "%{$search}%");
        }

        return $query
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function listActive(): Collection
    {
        return Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function find(int $id): Category
    {
        return Category::query()->withCount('products')->findOrFail($id);
    }

    public function create(array $data): Category
    {
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? $data['name']);
        $data['sort_order'] = $data['sort_order'] ?? $this->nextSortOrder();
        $data['is_active'] = $data['is_active'] ?? true;

        return Category::query()->create($data);
    }

    public function update(int $id, array $data): Category
    {
        $category = $this->find($id);

        if (array_key_exists('name', $data) && empty($data['slug'])) {
            $data['slug'] = $this->uniqueSlug($data['name'], $category->id);
        } elseif (! empty($data['slug'])) {
            $data['slug'] = $this->uniqueSlug($data['slug'], $category->id);
        }

        $category->update($data);

        return $category->fresh()->loadCount('products');
    }

    public function setActive(int $id, bool $active): Category
    {
        $category = $this->find($id);
        $category->update(['is_active' => $active]);

        return $category->fresh()->loadCount('products');
    }

    public function delete(int $id): void
    {
        $category = $this->find($id);

        $hasOrderHistory = OrderItem::query()
            ->whereHas('product', fn ($query) => $query->where('category_id', $category->id))
            ->exists();

        if ($hasOrderHistory) {
            throw new InvalidArgumentException('Nao e possivel excluir esta categoria porque existem vendas vinculadas. Desative-a em vez de excluir.');
        }

        if ($category->products()->exists()) {
            throw new InvalidArgumentException('Nao e possivel excluir esta categoria enquanto houver produtos associados. Mova ou exclua os produtos primeiro.');
        }

        DB::transaction(fn () => $category->delete());
    }

    private function nextSortOrder(): int
    {
        return ((int) Category::query()->max('sort_order')) + 1;
    }

    private function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: 'categoria';
        $slug = $base;
        $suffix = 1;

        while (
            Category::query()
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}

<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class SettingService
{
    public function list(): Collection
    {
        return Setting::query()->latest('id')->get();
    }

    public function find(int $id): Setting
    {
        $setting = Setting::query()->find($id);

        if (! $setting) {
            throw new InvalidArgumentException('Configuracao nao encontrada.');
        }

        return $setting;
    }

    /**
     * Retorna a configuracao atual (singleton). Cria registo vazio se nao existir.
     */
    public function current(): Setting
    {
        $setting = Setting::query()->latest('id')->first();

        if ($setting) {
            return $setting;
        }

        return Setting::query()->create([
            'currency' => 'EUR',
            'timezone' => 'Europe/Lisbon',
            'vat' => 23,
            'is_open' => true,
        ]);
    }

    public function create(array $data): Setting
    {
        $payload = $this->normalizePayload($data);
        $logo = $data['logo'] ?? null;

        if ($logo instanceof UploadedFile) {
            $payload['logo'] = $this->storeLogo($logo);
        }

        return Setting::query()->create($payload);
    }

    public function update(int $id, array $data): Setting
    {
        $setting = $this->find($id);
        $payload = $this->normalizePayload($data);
        $logo = $data['logo'] ?? null;

        if ($logo instanceof UploadedFile) {
            $this->deleteLogoFile($setting->logo);
            $payload['logo'] = $this->storeLogo($logo);
        }

        if (array_key_exists('remove_logo', $data) && filter_var($data['remove_logo'], FILTER_VALIDATE_BOOLEAN)) {
            $this->deleteLogoFile($setting->logo);
            $payload['logo'] = null;
        }

        $setting->update($payload);

        return $setting->fresh();
    }

    public function updateCurrent(array $data): Setting
    {
        $current = $this->current();

        return $this->update($current->id, $data);
    }

    public function delete(int $id): void
    {
        $setting = $this->find($id);
        $this->deleteLogoFile($setting->logo);
        $setting->delete();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizePayload(array $data): array
    {
        $allowed = [
            'company_name',
            'trade_name',
            'tax_number',
            'phone',
            'email',
            'website',
            'address',
            'city',
            'postal_code',
            'country',
            'currency',
            'timezone',
            'receipt_footer',
            'printer_name',
            'vat',
            'is_open',
        ];

        $payload = [];

        foreach ($allowed as $field) {
            if (! array_key_exists($field, $data)) {
                continue;
            }

            $value = $data[$field];

            if ($field === 'is_open') {
                $payload[$field] = filter_var($value, FILTER_VALIDATE_BOOLEAN);

                continue;
            }

            if ($field === 'vat') {
                $payload[$field] = $value === null || $value === '' ? null : (float) $value;

                continue;
            }

            if (is_string($value)) {
                $value = trim($value);
                $payload[$field] = $value === '' ? null : $value;

                continue;
            }

            $payload[$field] = $value;
        }

        return $payload;
    }

    private function storeLogo(UploadedFile $file): string
    {
        return $file->store('settings/logos', 'public');
    }

    private function deleteLogoFile(?string $path): void
    {
        if (! $path) {
            return;
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}

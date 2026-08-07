<?php

namespace Database\Seeders;

use App\Models\Store;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    public function run(): void
    {
        $stores = [
            [
                'name' => 'Dona Joana Centro',
                'slug' => 'dona-joana-centro',
                'company_name' => 'Dona Joana Cafe Lda',
                'tax_number' => 'PT501000001',
                'email' => 'centro@donajoana.pt',
                'phone' => '+351210000001',
                'street' => 'Rua Augusta 120',
                'city' => 'Lisboa',
                'state' => 'Lisboa',
                'postal_code' => '1100-048',
                'country' => 'Portugal',
                'logo' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Dona Joana Norte',
                'slug' => 'dona-joana-norte',
                'company_name' => 'Dona Joana Cafe Lda',
                'tax_number' => 'PT501000002',
                'email' => 'norte@donajoana.pt',
                'phone' => '+351220000002',
                'street' => 'Rua de Santa Catarina 45',
                'city' => 'Porto',
                'state' => 'Porto',
                'postal_code' => '4000-441',
                'country' => 'Portugal',
                'logo' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Dona Joana Coimbra Shopping',
                'slug' => 'dona-joana-coimbra-shopping',
                'company_name' => 'Dona Joana Cafe Lda',
                'tax_number' => 'PT501000003',
                'email' => 'coimbra@donajoana.pt',
                'phone' => '+351239000003',
                'street' => 'Av. Sa da Bandeira 80',
                'city' => 'Coimbra',
                'state' => 'Coimbra',
                'postal_code' => '3000-351',
                'country' => 'Portugal',
                'logo' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Dona Joana Sul',
                'slug' => 'dona-joana-sul',
                'company_name' => 'Dona Joana Cafe Lda',
                'tax_number' => 'PT501000004',
                'email' => 'sul@donajoana.pt',
                'phone' => '+351289000004',
                'street' => 'Rua de Santo Antonio 15',
                'city' => 'Faro',
                'state' => 'Faro',
                'postal_code' => '8000-283',
                'country' => 'Portugal',
                'logo' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Dona Joana Parque',
                'slug' => 'dona-joana-parque',
                'company_name' => 'Dona Joana Cafe Lda',
                'tax_number' => 'PT501000005',
                'email' => 'parque@donajoana.pt',
                'phone' => '+351253000005',
                'street' => 'Av. Central 200',
                'city' => 'Braga',
                'state' => 'Braga',
                'postal_code' => '4710-229',
                'country' => 'Portugal',
                'logo' => null,
                'is_active' => true,
            ],
        ];

        foreach ($stores as $store) {
            Store::query()->updateOrCreate(
                ['slug' => $store['slug']],
                $store
            );
        }
    }
}

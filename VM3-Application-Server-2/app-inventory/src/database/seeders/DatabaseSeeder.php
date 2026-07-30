<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * A small, fixed catalogue so the application is never an empty screen at a
     * presentation. updateOrCreate keeps the seeder safe to re-run on every
     * deployment.
     */
    public function run(): void
    {
        $products = [
            ['sku' => 'SRV-1120', 'name' => 'Rack server 1U',      'quantity' => 14,  'reorder_level' => 4,  'unit_price' => 1850.00, 'location' => 'A-01'],
            ['sku' => 'SWT-2400', 'name' => 'Switch 24 ports',     'quantity' => 6,   'reorder_level' => 6,  'unit_price' => 420.50,  'location' => 'A-02'],
            ['sku' => 'RAM-3208', 'name' => 'DDR4 module 32GB',    'quantity' => 128, 'reorder_level' => 20, 'unit_price' => 96.00,   'location' => 'B-11'],
            ['sku' => 'SSD-1TBN', 'name' => 'NVMe SSD 1TB',        'quantity' => 3,   'reorder_level' => 15, 'unit_price' => 118.75,  'location' => 'B-12'],
            ['sku' => 'UPS-1500', 'name' => 'UPS 1500VA',          'quantity' => 9,   'reorder_level' => 3,  'unit_price' => 275.00,  'location' => 'C-04'],
            ['sku' => 'CBL-CAT6', 'name' => 'Cat6 patch cable 2m', 'quantity' => 340, 'reorder_level' => 50, 'unit_price' => 3.20,    'location' => 'C-07'],
            ['sku' => 'KVM-0801', 'name' => 'KVM switch 8 ports',  'quantity' => 1,   'reorder_level' => 2,  'unit_price' => 510.00,  'location' => null],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(['sku' => $product['sku']], $product);
        }
    }
}

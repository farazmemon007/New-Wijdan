<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Unit;
use App\Models\Brand;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        /* ================= MASTER ================= */

        $category = Category::firstOrCreate([
            'name' => 'Electronics'
        ]);

        $subCategory = Subcategory::firstOrCreate([
            'category_id' => $category->id,
            'name'        => 'Air-Condition (AC)'
        ]);

        $unit = Unit::firstOrCreate([
            'name' => 'Piece'
        ]);

        $brand = Brand::firstOrCreate([
            'name' => 'Samsung'
        ]);

        /* ================= AUTO VALUES ================= */

        $lastId = Product::max('id') ?? 0;
        $nextId = $lastId + 1;

        Product::create([
            'creater_id'       => 1,

            'category_id'      => $category->id,
            'sub_category_id'  => $subCategory->id,
            'brand_id'         => $brand->id,

            'is_part'          => 0,
            'is_assembled'     => 0,

            'item_code'        => 'ITEM-' . str_pad($nextId, 4, '0', STR_PAD_LEFT),
            'unit_id'          => $unit->id,

            'item_name'        => 'Samsung AC 1.5 Ton',

            // 🔴 REQUIRED FIELDS (STRICT DB)
            'model'            => 'AC-' . $nextId,
            'hs_code'          => '841510',
            'pack_type'        => 'Box',
            'pack_qty'         => 1,
            'piece_per_pack'   => 1,
            'loose_piece'      => 0,   // ✅ FINAL REQUIRED FIELD

            // BUSINESS FIELDS
            'color'            => json_encode(['White']),

            'price'            => 150000,
            'wholesale_price'  => 145000,

            'initial_stock'    => 20,
            'alert_quantity'   => 5,

            'barcode_path'     => rand(100000000000, 999999999999),

            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
    }
}

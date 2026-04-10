<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UnitOfMeasure;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $data = [
            ['name' => 'Piece',     'abbreviation' => 'pcs'],
            ['name' => 'Kilogram',  'abbreviation' => 'kg'],
            ['name' => 'Gram',      'abbreviation' => 'g'],
            ['name' => 'Liter',     'abbreviation' => 'L'],
            ['name' => 'Milliliter','abbreviation' => 'mL'],
            ['name' => 'Box',       'abbreviation' => 'box'],
            ['name' => 'Carton',    'abbreviation' => 'ctn'],
            ['name' => 'Meter',     'abbreviation' => 'm'],
            ['name' => 'Roll',      'abbreviation' => 'roll'],
        ];

        foreach ($data as $item) {
            DB::table('unit_of_measures')->updateOrInsert(
                ['abbreviation' => $item['abbreviation']], // Cek berdasarkan singkatan
                [
                    'name' => $item['name'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }

        $categories = [
            [
                'name' => 'Elektronik',
                'description' => 'Semua jenis perangkat elektronik',
                'children' => [
                    ['name' => 'Smartphone', 'description' => 'Android dan iPhone'],
                    ['name' => 'Laptop', 'description' => 'Workstation dan Gaming'],
                ]
            ],
            [
                'name' => 'Pakaian',
                'description' => 'Koleksi busana pria dan wanita',
                'children' => [
                    ['name' => 'Kaos', 'description' => null],
                    ['name' => 'Jaket', 'description' => null],
                ]
            ],
            [
                'name' => 'Kesehatan',
                'description' => 'Suplemen dan alat kesehatan',
                'children' => []
            ],
        ];

        foreach ($categories as $cat) {
            // Masukkan Parent
            $parentId = DB::table('product_categories')->insertGetId([
                'parent_id' => null,
                'name' => $cat['name'],
                'description' => $cat['description'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Masukkan Children jika ada
            if (!empty($cat['children'])) {
                foreach ($cat['children'] as $child) {
                    DB::table('product_categories')->insert([
                        'parent_id' => $parentId,
                        'name' => $child['name'],
                        'description' => $child['description'],
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}

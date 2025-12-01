<?php

namespace Database\Seeders;

use App\Models\API\Division;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $divisions = [
            ['nama' => 'Media', 'deskripsi' => 'Mengelola dokumentasi, desain, dan media sosial.'],
            ['nama' => 'Perlengkapan', 'deskripsi' => 'Mengelola logistik, kebutuhan acara, dan inventaris.'],
            ['nama' => 'Humas', 'deskripsi' => 'Mengelola hubungan masyarakat dan kerjasama mitra.'],
        ];

        foreach ($divisions as $division) {
            Division::create($division);
        }
    }
}

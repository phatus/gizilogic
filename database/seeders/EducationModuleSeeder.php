<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\EducationModule;

class EducationModuleSeeder extends Seeder
{
    public function run(): void
    {
        EducationModule::create([
            'title' => 'Tingkatkan Protein dengan Ikan Kembung!',
            'content' => 'Ikan kembung kaya akan Omega-3 dan protein berkualitas tinggi yang baik untuk kecerdasan otak. Mudah didapat di daerah pesisir kita dan harganya sangat terjangkau!',
            'type' => 'resep_substitusi',
            'target_nutrition' => 'kurang_protein',
        ]);

        EducationModule::create([
            'title' => 'Resep Gurih Ikan Layur Crispy',
            'content' => 'Kurang protein di piringmu? Yuk, minta ibu masak ikan layur goreng tepung. Selain renyah, kandungan gizinya sangat tinggi dan bagus untuk pertumbuhan remajamu.',
            'type' => 'resep_substitusi',
            'target_nutrition' => 'kurang_protein',
        ]);

        EducationModule::create([
            'title' => 'Ikan Tuna: Superfood Pesisir',
            'content' => 'Tuna memiliki nutrisi luar biasa! Dagingnya yang tebal sangat cocok untuk asupan protein harianmu agar tidak mudah lelah saat belajar.',
            'type' => 'mikro_edukasi',
            'target_nutrition' => 'kurang_protein',
        ]);

        EducationModule::create([
            'title' => 'Thiwul Inovasi Karbohidrat',
            'content' => 'Bosan dengan nasi? Thiwul (singkong) adalah sumber karbohidrat lezat khas yang bisa dipadukan dengan ikan laut. Indeks glikemiknya lebih rendah dari nasi putih lho!',
            'type' => 'mikro_edukasi',
            'target_nutrition' => 'kurang_karbo',
        ]);
    }
}

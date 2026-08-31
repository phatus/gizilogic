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
            'title' => 'Tingkatkan Protein dengan Tahu Tuna Pacitan!',
            'content' => 'Tahu Tuna adalah primadona dari Pacitan. Menggabungkan tahu (nabati) dan ikan tuna laut dalam (hewani), makanan ini kaya akan Omega-3 dan protein berkualitas tinggi untuk kecerdasan otak.',
            'substitution_recipe' => "Bahan-bahan:\n- Tahu pong/tahu kulit\n- 250 gr ikan tuna giling\n- 2 siung bawang putih (haluskan)\n- Daun bawang secukupnya\n- Tepung tapioka & bumbu penyedap\n\nCara membuat:\n1. Campur tuna giling, bawang putih, daun bawang, dan tepung hingga kalis.\n2. Masukkan adonan ke dalam tahu pong.\n3. Kukus selama 20 menit hingga matang. Tahu tuna siap dinikmati!",
            'type' => 'resep_substitusi',
            'target_nutrition' => 'kurang_protein',
        ]);

        EducationModule::create([
            'title' => 'Resep Gurih Sayur Kalakan Ikan Asap',
            'content' => 'Kurang lauk hari ini? Cobalah Sayur Kalakan khas Pacitan! Menggunakan ikan asap laut dipadu dengan kuah santan pedas, hidangan ini sangat menggugah selera dan tinggi protein.',
            'substitution_recipe' => "Bahan-bahan:\n- 3 potong ikan asap (pari/cucut/layur)\n- 400 ml santan kelapa\n- Daun salam, daun jeruk, lengkuas\n- Bumbu halus: Bawang merah, bawang putih, cabai rawit, kunyit, kemiri\n\nCara membuat:\n1. Tumis bumbu halus dan rempah daun hingga harum.\n2. Tuangkan santan dan aduk hingga mendidih.\n3. Masukkan ikan asap, masak dengan api kecil hingga bumbu meresap sempurna.",
            'type' => 'resep_substitusi',
            'target_nutrition' => 'kurang_protein',
        ]);

        EducationModule::create([
            'title' => 'Sayur Jantung Pisang (Ontong) Khas Pedesaan',
            'content' => 'Sayuran tidak melulu bayam dan kangkung lho! Di Pacitan, jantung pisang (ontong) sering dimasak menjadi sayur bersantan atau oseng pedas yang kaya akan serat, zat besi, dan vitamin.',
            'substitution_recipe' => "Bahan-bahan:\n- 1 buah jantung pisang (ambil bagian putihnya saja, rebus & potong)\n- 1 genggam teri nasi atau tempe semangit (untuk penyedap)\n- Bumbu iris: Cabai hijau, bawang merah, bawang putih, tomat\n\nCara membuat:\n1. Tumis bumbu iris dan teri nasi hingga harum.\n2. Masukkan irisan jantung pisang yang sudah direbus.\n3. Tambahkan sedikit air, garam, dan gula aren. Tumis hingga matang.",
            'type' => 'resep_substitusi',
            'target_nutrition' => 'kurang_sayur',
        ]);

        EducationModule::create([
            'title' => 'Thiwul Inovasi Karbohidrat',
            'content' => 'Bosan dengan nasi? Thiwul (dari singkong) adalah penganan khas Pacitan yang bisa dihidangkan manis atau sebagai pengganti nasi. Indeks glikemiknya lebih rendah dari nasi putih lho!',
            'substitution_recipe' => "Bahan-bahan:\n- Tepung gaplek (singkong kering yang ditumbuk)\n- Sedikit air\n- Gula merah sisir & kelapa parut (jika ingin manis)\n\nCara membuat:\n1. Percikkan air ke tepung gaplek, uleni hingga berbentuk butiran kecil (mawut).\n2. Kukus butiran thiwul di atas dandang bambu selama 15-20 menit.\n3. Sajikan bersama sayur kalakan dan ikan laut goreng!",
            'type' => 'resep_substitusi',
            'target_nutrition' => 'kurang_karbo',
        ]);
    }
}

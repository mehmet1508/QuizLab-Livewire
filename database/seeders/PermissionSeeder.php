<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $izinler = [
            ['anahtar' => 'quiz_ekle', 'etiket' => 'Quiz Ekleme'],
            ['anahtar' => 'quiz_duzenle', 'etiket' => 'Quiz Düzenleme'],
            ['anahtar' => 'quiz_sil', 'etiket' => 'Quiz Silme'],
            ['anahtar' => 'soru_ekle', 'etiket' => 'Soru Ekleme'],
            ['anahtar' => 'soru_sil', 'etiket' => 'Soru Silme (Quizden Çıkarma)'],
            ['anahtar' => 'kullanici_ekle', 'etiket' => 'Kullanıcı Ekleme'],
            ['anahtar' => 'kullanici_duzenle', 'etiket' => 'Kullanıcı Düzenleme'],
            ['anahtar' => 'kullanici_sil', 'etiket' => 'Kullanıcı Silme'],
        ];

        foreach ($izinler as $izin) {
            Permission::firstOrCreate(['anahtar' => $izin['anahtar']], $izin);
        }
    }
}

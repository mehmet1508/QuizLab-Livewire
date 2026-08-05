<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = ['baslik', 'aciklama', 'sure_dakika', 'sure_tipi', 'sure_birimi', 'gorsel', 'kategori_id'];

    public function getEfektifSureSaniyeAttribute()
    {
        if (! $this->sure_dakika) {
            return null;
        }

        $birimCarpani = $this->sure_birimi === 'saniye' ? 1 : 60;
        $deger = $this->sure_dakika * $birimCarpani;

        if ($this->sure_tipi === 'soru_basi') {
            return $deger * $this->questions()->count();
        }

        return $deger;
    }

    public function results()
    {
        return $this->hasMany(Result::class);
    }

    public function getGorselUrlAttribute()
    {
        return $this->gorsel
            ? asset('storage/'.$this->gorsel)
            : asset('images/quiz-varsayilan.jpg');
    }

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'quiz_question')->withPivot('puan');
    }

    public function getEfektifPuanlar()
    {
        $questions = $this->questions;
        $toplamAtanmis = $questions->sum(fn ($q) => $q->pivot->puan ?? 0);
        $atanmamislar = $questions->filter(fn ($q) => is_null($q->pivot->puan));

        $sonuc = [];

        foreach ($questions as $q) {
            if (! is_null($q->pivot->puan)) {
                $sonuc[$q->id] = $q->pivot->puan;
            }
        }

        if ($atanmamislar->isNotEmpty()) {
            $kalanPuan = max(0, 100 - $toplamAtanmis);
            $adet = $atanmamislar->count();
            $esitPay = intdiv($kalanPuan, $adet);
            $kalan = $kalanPuan % $adet;

            $i = 0;
            foreach ($atanmamislar as $q) {
                $sonuc[$q->id] = $esitPay + ($i < $kalan ? 1 : 0);
                $i++;
            }
        }

        return collect($sonuc);
    }

    public function kategori()
    {
        return $this->belongsTo(Category::class, 'kategori_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function getOrtalamaPuanAttribute()
    {
        return round($this->reviews()->avg('puan') ?? 0, 1);
    }
}

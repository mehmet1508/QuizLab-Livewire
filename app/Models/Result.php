<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Result extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'quiz_id', 'puan', 'sure_saniye'];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Bir quiz için, her kullanıcının en iyi denemesi, puana (sonra süreye) göre sıralı.
     *
     * @return Collection<int, Result>
     */
    public static function enIyiSiralama(int $quizId): Collection
    {
        $sirala = fn ($a, $b) => $a->puan !== $b->puan
            ? $b->puan <=> $a->puan
            : ($a->sure_saniye ?? PHP_INT_MAX) <=> ($b->sure_saniye ?? PHP_INT_MAX);

        return static::where('quiz_id', $quizId)
            ->with('user')
            ->get()
            ->groupBy('user_id')
            ->map(fn ($grup) => $grup->sort($sirala)->first())
            ->sort($sirala)
            ->values();
    }

    /**
     * Tüm quizler toplamında kullanıcıların genel sıralaması (toplam puan, çözdüğü quiz sayısı).
     *
     * @return Collection<int, array{user_id: int, user: User, toplam_puan: int, quiz_sayisi: int}>
     */
    public static function genelSiralama(): Collection
    {
        $enIyiDenemeler = static::with('user')
            ->get()
            ->groupBy(fn ($r) => $r->user_id.'-'.$r->quiz_id)
            ->map(fn ($grup) => $grup->sortByDesc('puan')->first());

        return $enIyiDenemeler->groupBy('user_id')
            ->map(fn ($grup) => [
                'user_id' => $grup->first()->user_id,
                'user' => $grup->first()->user,
                'toplam_puan' => $grup->sum('puan'),
                'quiz_sayisi' => $grup->count(),
            ])
            ->sortByDesc('toplam_puan')
            ->values();
    }
}

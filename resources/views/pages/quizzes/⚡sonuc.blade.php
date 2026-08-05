<?php

use App\Models\Quiz;
use App\Models\Result;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Sonuç')] class extends Component {
    public Quiz $quiz;

    public Result $result;

    public int $puan = 0;

    public string $yorum = '';

    public function mount(Quiz $quiz, Result $result): void
    {
        abort_unless($result->user_id === Auth::id() && $result->quiz_id === $quiz->id, 403);

        $this->quiz = $quiz;
        $this->result = $result;

        $mevcutYorum = Auth::user()->reviews()->where('quiz_id', $quiz->id)->first();

        $this->puan = $mevcutYorum->puan ?? 0;
        $this->yorum = $mevcutYorum->yorum ?? '';
    }

    public function degerlendir(): void
    {
        $cozdu = Result::where('quiz_id', $this->quiz->id)->where('user_id', Auth::id())->exists();

        if (! $cozdu) {
            $this->addError('yorum', 'Yorum yapabilmek için önce bu quizi çözmelisiniz.');

            return;
        }

        $this->validate([
            'puan' => 'required|integer|min:1|max:5',
            'yorum' => 'nullable|string|max:1000',
        ]);

        Review::updateOrCreate(
            ['user_id' => Auth::id(), 'quiz_id' => $this->quiz->id],
            ['puan' => $this->puan, 'yorum' => $this->yorum]
        );

        session()->flash('success', 'Değerlendirmeniz kaydedildi.');
    }
}; ?>

<div class="max-w-xl mx-auto">
    <flux:card class="bg-accent/5 text-center">
        <flux:heading size="lg">{{ $quiz->baslik }}</flux:heading>

        @php
            $maksimumPuan = $quiz->getEfektifPuanlar()->sum();
        @endphp

        <p class="text-3xl font-bold text-accent mt-2 mb-2">{{ $result->puan }} / {{ $maksimumPuan }}</p>
        <flux:text class="mb-2">puan aldınız.</flux:text>

        @if ($result->sure_saniye !== null)
            @php
                $saat = intdiv($result->sure_saniye, 3600);
                $dakika = intdiv($result->sure_saniye % 3600, 60);
                $saniye = $result->sure_saniye % 60;
                $sureMetni = $saat > 0
                    ? sprintf('%d:%02d:%02d', $saat, $dakika, $saniye)
                    : sprintf('%02d:%02d', $dakika, $saniye);
            @endphp
            <flux:text size="sm" class="mb-6">Süre: {{ $sureMetni }}</flux:text>
        @endif

        <div class="mt-6 pt-6 border-t border-zinc-200 dark:border-white/10 text-left" x-data="{ hover: 0 }">
            <flux:text class="font-medium mb-2">Bu quizi değerlendirin</flux:text>

            @if (session('success'))
                <flux:text class="text-green-600 dark:text-green-400 mb-2">{{ session('success') }}</flux:text>
            @endif

            @error('yorum')
                <flux:text class="text-red-600 dark:text-red-400 mb-2">{{ $message }}</flux:text>
            @enderror

            <form wire:submit="degerlendir">
                <div class="flex gap-1 mb-2">
                    @for ($yildiz = 1; $yildiz <= 5; $yildiz++)
                        <span @click="$wire.puan = {{ $yildiz }}" @mouseenter="hover = {{ $yildiz }}" @mouseleave="hover = 0"
                            class="text-2xl cursor-pointer select-none"
                            :class="(hover || $wire.puan) >= {{ $yildiz }} ? 'text-yellow-500' : 'text-zinc-300 dark:text-zinc-600'">★</span>
                    @endfor
                </div>

                <flux:textarea wire:model="yorum" placeholder="Yorumunuz (opsiyonel)" rows="2" />

                <flux:button type="submit" variant="primary" size="sm" class="mt-2">Gönder</flux:button>
            </form>
        </div>

        <div class="flex justify-center gap-3 mt-6">
            <flux:button href="{{ route('quizzes.index') }}" wire:navigate>Diğer Quizlere Dön</flux:button>
            <flux:button variant="primary" href="{{ route('profile.edit') }}" wire:navigate>Profilimi Gör</flux:button>
        </div>
    </flux:card>
</div>

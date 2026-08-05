<?php

use App\Models\Quiz;
use App\Models\Result;
use App\Models\User;
use App\Notifications\SiralamaGuncellendi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Quiz')] class extends Component {
    public Quiz $quiz;

    public bool $started = false;

    public ?string $bitisZamani = null;

    /** @var array<int, int> */
    public array $answers = [];

    public function mount(Quiz $quiz): void
    {
        $this->quiz = $quiz;
        $this->started = session()->has($this->baslangicKey());

        if ($this->started) {
            $this->quiz->load('questions.options');

            if ($this->quiz->efektif_sure_saniye && ! session()->has($this->bitisKey())) {
                session([$this->bitisKey() => now()->addSeconds($this->quiz->efektif_sure_saniye)]);
            }

            $this->bitisZamani = optional(session($this->bitisKey()))->toIso8601String();
        }
    }

    protected function baslangicKey(): string
    {
        return 'quiz_baslangic_' . $this->quiz->id;
    }

    protected function bitisKey(): string
    {
        return 'quiz_bitis_' . $this->quiz->id;
    }

    #[Computed]
    public function soruSayisi(): int
    {
        return $this->quiz->questions()->count();
    }

    #[Computed]
    public function yorumlar()
    {
        return $this->quiz->reviews()->with('user')->latest()->get();
    }

    #[Computed]
    public function puanlar()
    {
        return $this->quiz->getEfektifPuanlar();
    }

    public function basla(): void
    {
        session([$this->baslangicKey() => now()]);

        $this->redirect(route('quizzes.show', $this->quiz), navigate: true);
    }

    public function submit(): void
    {
        $this->quiz->load('questions.options');
        $puanlar = $this->quiz->getEfektifPuanlar();

        $puan = 0;

        foreach ($this->quiz->questions as $question) {
            $secilenOptionId = $this->answers[$question->id] ?? null;
            $dogruOption = $question->options->firstWhere('dogru_mu', true);

            if ($dogruOption && $secilenOptionId == $dogruOption->id) {
                $puan += $puanlar[$question->id];
            }
        }

        $baslangic = session($this->baslangicKey());
        $sureSaniye = $baslangic ? abs(now()->diffInSeconds($baslangic)) : null;

        $result = Result::create([
            'user_id' => Auth::id(),
            'quiz_id' => $this->quiz->id,
            'puan' => $puan,
            'sure_saniye' => $sureSaniye,
        ]);

        session()->forget([$this->bitisKey(), $this->baslangicKey()]);

        $digerKatilimcilar = User::whereIn('id', Result::where('quiz_id', $this->quiz->id)
            ->where('user_id', '!=', Auth::id())
            ->distinct()
            ->pluck('user_id'))->get();

        if ($digerKatilimcilar->isNotEmpty()) {
            Notification::send($digerKatilimcilar, new SiralamaGuncellendi($this->quiz));
        }

        $this->redirect(route('quizzes.sonuc', [$this->quiz, $result]), navigate: true);
    }
}; ?>

<div class="max-w-2xl mx-auto">
    @if (! $started)
        {{-- Giriş ekranı --}}
        <flux:card class="text-center">
            @if ($quiz->gorsel)
                <img src="{{ $quiz->gorsel_url }}" class="w-full h-40 object-cover rounded-lg mb-4">
            @endif

            <flux:heading size="xl">{{ $quiz->baslik }}</flux:heading>
            <flux:text class="mt-2">{{ $quiz->aciklama }}</flux:text>

            <div class="flex justify-center gap-6 mt-6">
                <div class="text-center">
                    <div class="text-2xl font-bold text-accent">{{ $this->soruSayisi }}</div>
                    <flux:text size="sm">Soru</flux:text>
                </div>

                <div class="text-center">
                    <div class="text-2xl font-bold text-accent">
                        @php $efektifSaniye = $quiz->efektif_sure_saniye; @endphp
                        @if ($efektifSaniye)
                            @php
                                $dk = intdiv($efektifSaniye, 60);
                                $sn = $efektifSaniye % 60;
                            @endphp
                            {{ $dk }}{{ $sn > 0 ? ':' . str_pad($sn, 2, '0', STR_PAD_LEFT) : '' }} dk
                        @else
                            Süresiz
                        @endif
                    </div>
                    <flux:text size="sm">Süre</flux:text>
                </div>
            </div>

            @if ($efektifSaniye)
                <div class="mt-6 text-sm bg-yellow-50 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300 rounded-lg p-3">
                    "Başla" dediğinizde süreniz başlayacaktır. Süre dolduğunda quiz otomatik olarak sonlanır.
                </div>
            @endif

            <div class="flex justify-center gap-3 mt-8">
                <flux:button href="{{ route('quizzes.index') }}" wire:navigate>Kapat</flux:button>
                <flux:button variant="primary" wire:click="basla">Başla</flux:button>
            </div>

            @if ($this->yorumlar->isNotEmpty())
                <div class="mt-8 pt-6 border-t border-zinc-200 dark:border-white/10 text-left">
                    <flux:text class="font-medium mb-3">
                        Değerlendirmeler ({{ $this->yorumlar->count() }}) — Ortalama {{ $quiz->ortalama_puan }} ★
                    </flux:text>

                    <div class="space-y-3 max-h-64 overflow-y-auto">
                        @foreach ($this->yorumlar as $yorum)
                            <div class="border border-zinc-200 dark:border-white/10 rounded-lg p-3">
                                <div class="flex justify-between items-center">
                                    <span class="font-medium text-sm">{{ $yorum->user->name }}</span>
                                    <span class="text-yellow-500 text-sm">
                                        {{ str_repeat('★', $yorum->puan) }}{{ str_repeat('☆', 5 - $yorum->puan) }}
                                    </span>
                                </div>
                                @if ($yorum->yorum)
                                    <flux:text size="sm" class="mt-1">{{ $yorum->yorum }}</flux:text>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </flux:card>
    @else
        {{-- Aktif quiz --}}
        <flux:card>
            @if ($bitisZamani)
                <div id="sayac" class="mb-4 text-lg font-semibold text-red-600 dark:text-red-400 text-center"></div>
            @endif

            <div class="flex flex-wrap gap-2 mb-6 justify-center">
                @foreach ($quiz->questions as $index => $question)
                    <button type="button"
                        class="soru-nav-btn w-9 h-9 rounded-full border border-zinc-200 dark:border-white/10 text-sm font-medium"
                        data-index="{{ $index }}" onclick="sorayaGit({{ $index }})">
                        {{ $index + 1 }}
                    </button>
                @endforeach
            </div>

            <form id="quiz-form" wire:submit="submit">
                @foreach ($quiz->questions as $index => $question)
                    <div class="soru-blok {{ $index === 0 ? '' : 'hidden' }}" data-index="{{ $index }}">
                        <div class="flex justify-between items-start mb-3">
                            <p class="font-medium">{{ $index + 1 }}. {{ $question->soru_metni }}</p>
                            <span class="text-sm bg-accent/10 text-accent px-2 py-1 rounded-full whitespace-nowrap ml-2">
                                {{ $this->puanlar[$question->id] }} puan
                            </span>
                        </div>

                        <div class="space-y-2 mb-4">
                            @foreach ($question->options as $option)
                                <label class="flex items-center gap-2 p-2 rounded hover:bg-zinc-50 dark:hover:bg-white/5 cursor-pointer">
                                    <input type="radio" wire:model="answers.{{ $question->id }}" value="{{ $option->id }}"
                                        class="cevap-input" data-soru-index="{{ $index }}">
                                    <span>{{ $option->sik_metni }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <div class="flex justify-between items-center mt-6 pt-4 border-t border-zinc-200 dark:border-white/10">
                    <flux:button type="button" id="oncekiBtn" onclick="oncekiSoru()">&larr; Önceki</flux:button>

                    <span id="soruSayaci" class="text-zinc-500 dark:text-zinc-400 text-sm"></span>

                    <flux:button type="button" id="sonrakiBtn" onclick="sonrakiSoru()">Sonraki &rarr;</flux:button>

                    <flux:button type="submit" id="bitirBtn" variant="primary" class="hidden">Quiz'i Bitir</flux:button>
                </div>
            </form>
        </flux:card>

        @script
            <script>
                const toplamSoru = {{ $quiz->questions->count() }};
                let aktifIndex = 0;

                function guncelleGorunum() {
                    $wire.$el.querySelectorAll('.soru-blok').forEach(el => {
                        el.classList.toggle('hidden', parseInt(el.dataset.index) !== aktifIndex);
                    });

                    $wire.$el.querySelectorAll('.soru-nav-btn').forEach(btn => {
                        const idx = parseInt(btn.dataset.index);
                        btn.classList.remove('bg-accent', 'text-accent-foreground', 'bg-green-100', 'text-green-800', 'dark:bg-green-900/40', 'dark:text-green-300');

                        if (idx === aktifIndex) {
                            btn.classList.add('bg-accent', 'text-accent-foreground');
                        } else if (soruCevaplandiMi(idx)) {
                            btn.classList.add('bg-green-100', 'text-green-800', 'dark:bg-green-900/40', 'dark:text-green-300');
                        }
                    });

                    $wire.$el.querySelector('#soruSayaci').textContent = `${aktifIndex + 1} / ${toplamSoru}`;
                    $wire.$el.querySelector('#oncekiBtn').disabled = aktifIndex === 0;

                    const sonSoru = aktifIndex === toplamSoru - 1;
                    $wire.$el.querySelector('#sonrakiBtn').classList.toggle('hidden', sonSoru);
                    $wire.$el.querySelector('#bitirBtn').classList.toggle('hidden', !sonSoru);
                }

                function soruCevaplandiMi(index) {
                    const inputs = $wire.$el.querySelectorAll(`.cevap-input[data-soru-index="${index}"]`);
                    return Array.from(inputs).some(input => input.checked);
                }

                window.sorayaGit = function (index) {
                    aktifIndex = index;
                    guncelleGorunum();
                };

                window.oncekiSoru = function () {
                    if (aktifIndex > 0) {
                        aktifIndex--;
                        guncelleGorunum();
                    }
                };

                window.sonrakiSoru = function () {
                    if (aktifIndex < toplamSoru - 1) {
                        aktifIndex++;
                        guncelleGorunum();
                    }
                };

                $wire.$el.querySelectorAll('.cevap-input').forEach(input => {
                    input.addEventListener('change', guncelleGorunum);
                });

                guncelleGorunum();

                @if ($bitisZamani)
                    const bitisZamani = new Date(@js($bitisZamani)).getTime();
                    const sayacEl = $wire.$el.querySelector('#sayac');

                    const interval = setInterval(() => {
                        const kalan = bitisZamani - new Date().getTime();

                        if (kalan <= 0) {
                            clearInterval(interval);
                            sayacEl.textContent = "Süre doldu, gönderiliyor...";
                            $wire.submit();
                            return;
                        }

                        const dakika = Math.floor((kalan / 1000 / 60) % 60);
                        const saniye = Math.floor((kalan / 1000) % 60);
                        sayacEl.textContent = `Kalan Süre: ${String(dakika).padStart(2, '0')}:${String(saniye).padStart(2, '0')}`;
                    }, 1000);
                @endif
            </script>
        @endscript
    @endif
</div>

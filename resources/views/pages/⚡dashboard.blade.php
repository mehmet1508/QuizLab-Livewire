<?php

use App\Models\Quiz;
use App\Models\Result;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Panelim')] class extends Component {
    #[Computed]
    public function siralamalar(): array
    {
        $userId = Auth::id();
        $quizIds = Result::where('user_id', $userId)->distinct()->pluck('quiz_id');
        $siralamalar = [];

        foreach ($quizIds as $quizId) {
            $quiz = Quiz::with('questions')->find($quizId);

            if (! $quiz) {
                continue;
            }

            $enIyiler = Result::enIyiSiralama($quizId);
            $kullaniciSirasi = $enIyiler->search(fn ($item) => $item->user_id === $userId);
            $kullanicininEnIyisi = $enIyiler->firstWhere('user_id', $userId);

            $siralamalar[$quizId] = [
                'quiz' => $quiz,
                'puan' => $kullanicininEnIyisi->puan,
                'sure_saniye' => $kullanicininEnIyisi->sure_saniye,
                'siralama' => $kullaniciSirasi + 1,
                'toplam_katilimci' => $enIyiler->count(),
                'maksimum_puan' => $quiz->getEfektifPuanlar()->sum(),
            ];
        }

        return $siralamalar;
    }

    #[Computed]
    public function genelSiralama()
    {
        return Result::genelSiralama();
    }

    #[Computed]
    public function tumQuizler()
    {
        $quizIds = Result::where('user_id', Auth::id())->distinct()->pluck('quiz_id');

        return Quiz::withCount(['results as katilimci_sayisi' => function ($q) {
            $q->select(DB::raw('count(distinct user_id)'));
        }])
            ->get()
            ->sortByDesc(fn ($quiz) => $quizIds->contains($quiz->id) ? 1 : 0)
            ->values();
    }
}; ?>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
    {{-- SOL: Genel Sıralama --}}
    <flux:card class="flex flex-col h-full">
        <flux:heading size="lg" class="mb-4">{{ __('Genel Sıralama') }}</flux:heading>

        @if ($this->genelSiralama->isEmpty())
            <flux:text>{{ __('Henüz kimse quiz çözmemiş.') }}</flux:text>
        @else
            <div class="space-y-2 flex-1 overflow-y-auto pr-1">
                @foreach ($this->genelSiralama as $index => $veri)
                    <div class="flex items-center justify-between p-3 rounded-lg {{ $veri['user_id'] === Auth::id() ? 'bg-accent/10' : '' }}">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="w-7 h-7 shrink-0 rounded-full flex items-center justify-center text-xs font-semibold
                                {{ $index === 0 ? 'bg-yellow-100 dark:bg-yellow-900/40 text-yellow-800 dark:text-yellow-300' : 'bg-zinc-100 dark:bg-white/10 text-zinc-600 dark:text-zinc-300' }}">
                                {{ $index + 1 }}
                            </span>
                            <span class="text-sm truncate">
                                {{ $veri['user']->name }}
                                @if ($veri['user_id'] === Auth::id())
                                    <span class="text-accent">(Siz)</span>
                                @endif
                            </span>
                        </div>
                        <span class="text-sm font-semibold shrink-0 ml-2">{{ $veri['toplam_puan'] }}</span>
                    </div>
                @endforeach
            </div>
        @endif

        <flux:button :href="route('genel-siralama')" wire:navigate variant="primary" class="mt-4">
            {{ __('Tam Sayfada Aç') }}
        </flux:button>
    </flux:card>

    {{-- SAĞ: Tüm Quizler --}}
    <flux:card class="flex flex-col h-full">
        <flux:heading size="lg" class="mb-4">{{ __('Quizler') }}</flux:heading>

        @if ($this->tumQuizler->isEmpty())
            <flux:text>{{ __('Henüz hiç quiz eklenmemiş.') }}</flux:text>
        @else
            <div class="overflow-y-auto flex-1">
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-white/10">
                            <th class="py-2">Quiz</th>
                            <th class="py-2">Durum</th>
                            <th class="py-2">Katılımcı</th>
                            <th class="py-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->tumQuizler as $quiz)
                            @php $veri = $this->siralamalar[$quiz->id] ?? null; @endphp
                            <tr class="border-b border-zinc-100 dark:border-white/5">
                                <td class="py-3 font-medium">{{ $quiz->baslik }}</td>
                                <td class="py-3">
                                    @if ($veri)
                                        <span class="inline-block whitespace-nowrap px-2 py-1 rounded-full text-xs font-medium
                                            {{ $veri['siralama'] === 1 ? 'bg-yellow-100 dark:bg-yellow-900/40 text-yellow-800 dark:text-yellow-300' : 'bg-accent/10 text-accent' }}">
                                            {{ $veri['puan'] }} p. — {{ $veri['siralama'] }}.
                                        </span>
                                    @else
                                        <span class="text-zinc-400 dark:text-zinc-500 text-xs">Girmediniz</span>
                                    @endif
                                </td>
                                <td class="py-3 text-zinc-500 dark:text-zinc-400">{{ $quiz->katilimci_sayisi }} kişi</td>
                                <td class="py-3 text-right whitespace-nowrap space-x-3">
                                    @if ($veri)
                                        <flux:link :href="route('quizzes.leaderboard', $quiz)" wire:navigate>Detay</flux:link>
                                    @else
                                        <flux:link :href="route('quizzes.show', $quiz)" wire:navigate>Git</flux:link>
                                        <flux:link :href="route('quizzes.leaderboard', $quiz)" wire:navigate>Detay</flux:link>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </flux:card>
</div>

<?php

use App\Models\Category;
use App\Models\Quiz;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Title('Quizler')] class extends Component {
    #[Url]
    public string $q = '';

    #[Url(as: 'kategori')]
    public ?int $kategoriId = null;

    #[Computed]
    public function quizzes()
    {
        $query = Quiz::withCount(['results as katilimci_sayisi' => function ($q) {
            $q->select(DB::raw('count(distinct user_id)'));
        }]);

        if ($this->q !== '') {
            $query->where('baslik', 'like', '%' . $this->q . '%');
        }

        if ($this->kategoriId) {
            $query->where('kategori_id', $this->kategoriId);
        }

        return $query->latest()->get();
    }

    #[Computed]
    public function kategoriler()
    {
        return Category::all();
    }
}; ?>

<div>
    <flux:heading size="xl">{{ __('Quizler') }}</flux:heading>

    <div class="flex flex-wrap gap-3 mt-4 mb-6">
        <flux:input wire:model.live.debounce.400ms="q" placeholder="Quiz ara..." class="flex-1 min-w-[200px]" />

        <flux:select wire:model.live="kategoriId" class="w-auto min-w-[180px]">
            <option value="">Tüm Kategoriler</option>
            @foreach ($this->kategoriler as $kategori)
                <option value="{{ $kategori->id }}">{{ $kategori->ad }}</option>
            @endforeach
        </flux:select>
    </div>

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($this->quizzes as $quiz)
            <a href="{{ route('quizzes.show', $quiz) }}" wire:navigate
                class="group block bg-white dark:bg-white/10 rounded-xl overflow-hidden border border-zinc-200 dark:border-white/10 hover:shadow-lg hover:-translate-y-0.5 transition">

                <div class="relative h-40 overflow-hidden">
                    <img src="{{ $quiz->gorsel_url }}" alt="{{ $quiz->baslik }}"
                        class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                </div>

                <div class="p-4">
                    <flux:heading size="lg">{{ $quiz->baslik }}</flux:heading>
                    <flux:text class="mt-1 line-clamp-2">{{ $quiz->aciklama }}</flux:text>

                    <div class="flex items-center gap-1 mt-3 text-sm text-zinc-500 dark:text-zinc-400">
                        <flux:icon name="users" class="size-4" />
                        {{ $quiz->katilimci_sayisi }} kişi çözdü

                        @if ($quiz->ortalama_puan > 0)
                            <span class="flex items-center gap-1 text-yellow-500">
                                ★ {{ $quiz->ortalama_puan }}
                                <span class="text-zinc-400 dark:text-zinc-500">({{ $quiz->reviews()->count() }})</span>
                            </span>
                        @endif
                    </div>
                </div>
            </a>
        @endforeach
    </div>

    @if ($this->quizzes->isEmpty())
        <flux:text class="mt-6">{{ __('Henüz hiç quiz eklenmemiş.') }}</flux:text>
    @endif
</div>

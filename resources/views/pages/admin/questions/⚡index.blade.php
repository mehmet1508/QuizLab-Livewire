<?php

use App\Models\Question;
use App\Models\Quiz;
use Flux\Flux;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Sorular')] class extends Component {
    public Quiz $quiz;

    public function mount(Quiz $quiz): void
    {
        $quiz->load('questions.options');

        $this->quiz = $quiz;
    }

    public function cikar(Question $question): void
    {
        $this->authorize('soru_sil');

        // Soruyu tamamen silmiyoruz, sadece bu quizden çıkarıyoruz (havuzda kalır)
        $this->quiz->questions()->detach($question->id);
        $this->quiz->load('questions.options');

        Flux::toast(variant: 'success', text: 'Soru quizden çıkarıldı.');
    }
}; ?>

<div class="max-w-3xl mx-auto">
    <flux:card>
        <flux:heading size="lg" class="mb-1">{{ $quiz->baslik }} — Sorular</flux:heading>

        @if (session('hatalar'))
            <div class="mb-4 p-3 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300 rounded">
                <p class="font-medium">Bazı satırlar atlandı:</p>
                <ul class="list-disc ml-5 mt-1">
                    @foreach (session('hatalar') as $hata)
                        <li>{{ $hata }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <flux:link :href="route('admin.quizzes.index')" wire:navigate class="text-sm">&larr; Quiz Listesine Dön</flux:link>

        <div class="flex flex-wrap gap-3 mt-4 mb-4">
            <flux:button :href="route('admin.questions.create', $quiz)" wire:navigate variant="primary">
                {{ __('+ Yeni Soru Oluştur') }}
            </flux:button>

            <flux:button :href="route('admin.questions.pool', $quiz)" wire:navigate>
                {{ __('Havuzdan Soru Ekle') }}
            </flux:button>

            <flux:button :href="route('admin.questions.import', $quiz)" wire:navigate>
                {{ __('Excel ile Yükle') }}
            </flux:button>
        </div>

        @foreach ($quiz->questions as $question)
            <div wire:key="question-{{ $question->id }}" class="border border-zinc-200 dark:border-white/10 rounded-lg p-4 mb-3">
                <div class="flex justify-between items-start">
                    <p class="font-medium">
                        {{ $loop->iteration }}. {{ $question->soru_metni }}
                        <span class="text-sm text-zinc-500 dark:text-zinc-400">
                            ({{ $question->pivot->puan !== null ? $question->pivot->puan.' puan' : 'otomatik puan' }})
                        </span>
                    </p>
                    <div class="space-x-2 whitespace-nowrap">
                        <flux:link :href="route('admin.questions.edit', [$quiz, $question])" wire:navigate>Düzenle</flux:link>
                        <flux:link wire:click="cikar({{ $question->id }})" wire:confirm="Emin misiniz?" variant="danger" class="cursor-pointer">
                            Quizden Çıkar
                        </flux:link>
                    </div>
                </div>
                <ul class="mt-2 ml-4 list-disc">
                    @foreach ($question->options as $option)
                        <li class="{{ $option->dogru_mu ? 'text-green-600 dark:text-green-400 font-semibold' : 'text-zinc-600 dark:text-zinc-400' }}">
                            {{ $option->sik_metni }} {{ $option->dogru_mu ? '(Doğru)' : '' }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </flux:card>
</div>

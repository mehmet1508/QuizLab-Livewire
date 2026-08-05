<?php

use App\Models\Quiz;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Admin — Quiz Yönetimi')] class extends Component {
    #[Computed]
    public function quizzes()
    {
        return Quiz::withCount('questions')->get();
    }

    public function sil(Quiz $quiz): void
    {
        $this->authorize('quiz_sil');

        $quiz->delete();

        Flux::toast(variant: 'success', text: 'Quiz silindi.');
    }
}; ?>

<div class="max-w-4xl mx-auto">
    <flux:heading size="xl" class="mb-6">{{ __('Admin — Quiz Yönetimi') }}</flux:heading>

    <div class="flex flex-wrap gap-3 mb-6">
        <flux:button :href="route('admin.quizzes.create')" wire:navigate variant="primary">
            {{ __('+ Yeni Quiz Ekle') }}
        </flux:button>

        <flux:button :href="route('admin.categories.index')" wire:navigate>
            {{ __('Kategoriler') }}
        </flux:button>

        <flux:button :href="route('admin.questions.createGlobal')" wire:navigate>
            {{ __('+ Soru Ekle (Havuza)') }}
        </flux:button>
    </div>

    <flux:card>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-white/10">
                        <th class="py-2">Başlık</th>
                        <th class="py-2">Soru Sayısı</th>
                        <th class="py-2">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($this->quizzes as $quiz)
                        <tr wire:key="quiz-{{ $quiz->id }}" class="border-b border-zinc-100 dark:border-white/5">
                            <td class="py-2">{{ $quiz->baslik }}</td>
                            <td class="py-2 text-zinc-500 dark:text-zinc-400">{{ $quiz->questions_count }}</td>
                            <td class="py-2 space-x-2 whitespace-nowrap">
                                <flux:link :href="route('admin.quizzes.leaderboard', $quiz)" wire:navigate>Sıralama</flux:link>
                                <flux:link :href="route('admin.quizzes.puanlar', $quiz)" wire:navigate>Puanlar</flux:link>
                                <flux:link :href="route('admin.questions.index', $quiz)" wire:navigate>Sorular</flux:link>
                                <flux:link :href="route('admin.quizzes.edit', $quiz)" wire:navigate>Düzenle</flux:link>
                                <flux:link wire:click="sil({{ $quiz->id }})" wire:confirm="Emin misiniz?" variant="danger" class="cursor-pointer">
                                    Sil
                                </flux:link>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </flux:card>
</div>

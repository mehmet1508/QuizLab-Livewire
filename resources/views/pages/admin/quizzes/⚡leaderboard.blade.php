<?php

use App\Models\Quiz;
use App\Models\Result;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Admin — Sıralama')] class extends Component {
    public Quiz $quiz;

    public function mount(Quiz $quiz): void
    {
        $quiz->load('questions');

        $this->quiz = $quiz;
    }

    #[Computed]
    public function enIyiler()
    {
        return Result::enIyiSiralama($this->quiz->id);
    }

    #[Computed]
    public function maksimumPuan()
    {
        return $this->quiz->getEfektifPuanlar()->sum();
    }
}; ?>

<div class="max-w-3xl mx-auto">
    <flux:card>
        <flux:heading size="lg" class="mb-1">{{ $quiz->baslik }} — Sıralama (Admin)</flux:heading>

        <flux:link :href="route('admin.quizzes.index')" wire:navigate class="text-sm">&larr; Quiz Listesine Dön</flux:link>

        @if ($this->enIyiler->isEmpty())
            <flux:text class="mt-4">{{ __('Bu quizi henüz kimse çözmemiş.') }}</flux:text>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left mt-4">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-white/10">
                            <th class="py-2">Sıra</th>
                            <th class="py-2">Kullanıcı</th>
                            <th class="py-2">Email</th>
                            <th class="py-2">Puan</th>
                            <th class="py-2">Süre</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->enIyiler as $index => $sonuc)
                            <tr class="border-b border-zinc-100 dark:border-white/5">
                                <td class="py-2">
                                    {{ $index + 1 }}
                                    @if ($index === 0) 🏆 @endif
                                </td>
                                <td class="py-2">{{ $sonuc->user->name }}</td>
                                <td class="py-2 text-zinc-500 dark:text-zinc-400">{{ $sonuc->user->email }}</td>
                                <td class="py-2">{{ $sonuc->puan }} / {{ $this->maksimumPuan }}</td>
                                <td class="py-2 text-zinc-500 dark:text-zinc-400">
                                    @if ($sonuc->sure_saniye !== null)
                                        {{ sprintf('%02d:%02d', floor($sonuc->sure_saniye / 60), $sonuc->sure_saniye % 60) }}
                                    @else
                                        —
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

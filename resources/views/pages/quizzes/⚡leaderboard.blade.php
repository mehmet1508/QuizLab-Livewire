<?php

use App\Models\Quiz;
use App\Models\Result;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Genel Sıralama')] class extends Component {
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
        <flux:heading size="lg" class="mb-1">{{ $quiz->baslik }} — Genel Sıralama</flux:heading>

        <flux:link href="{{ route('dashboard') }}" wire:navigate class="text-sm">&larr; Panelime Dön</flux:link>

        <div class="overflow-x-auto">
            <table class="w-full text-left mt-4">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-white/10">
                        <th class="py-2">Sıra</th>
                        <th class="py-2">Kullanıcı</th>
                        <th class="py-2">Puan</th>
                        <th class="py-2">Süre</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($this->enIyiler as $index => $sonuc)
                        <tr class="border-b border-zinc-100 dark:border-white/5 {{ $sonuc->user_id === Auth::id() ? 'bg-accent/10 font-semibold' : '' }}">
                            <td class="py-2">
                                {{ $index + 1 }}
                                @if ($index === 0) 🏆 @endif
                            </td>
                            <td class="py-2">
                                {{ $sonuc->user->name }}
                                @if ($sonuc->user_id === Auth::id())
                                    <span class="text-accent text-sm">(Siz)</span>
                                @endif
                            </td>
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
    </flux:card>
</div>

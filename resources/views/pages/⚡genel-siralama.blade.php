<?php

use App\Models\Result;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Genel Sıralama')] class extends Component {
    #[Computed]
    public function genelSiralama()
    {
        return Result::genelSiralama();
    }
}; ?>

<div class="max-w-3xl mx-auto">
    <flux:card>
        <flux:link :href="route('dashboard')" wire:navigate class="text-sm">&larr; Panelime Dön</flux:link>

        <div class="overflow-x-auto">
            <table class="w-full text-left mt-4">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-white/10">
                        <th class="py-2">Sıra</th>
                        <th class="py-2">Kullanıcı</th>
                        <th class="py-2">Toplam Puan</th>
                        <th class="py-2">Çözdüğü Quiz Sayısı</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($this->genelSiralama as $index => $veri)
                        <tr class="border-b border-zinc-100 dark:border-white/5 {{ $veri['user_id'] === Auth::id() ? 'bg-accent/10 font-semibold' : '' }}">
                            <td class="py-2">
                                {{ $index + 1 }}
                                @if ($index === 0) 🏆 @endif
                            </td>
                            <td class="py-2">
                                {{ $veri['user']->name }}
                                @if ($veri['user_id'] === Auth::id())
                                    <span class="text-accent text-sm">(Siz)</span>
                                @endif
                            </td>
                            <td class="py-2">{{ $veri['toplam_puan'] }}</td>
                            <td class="py-2 text-zinc-500 dark:text-zinc-400">{{ $veri['quiz_sayisi'] }} quiz</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </flux:card>
</div>

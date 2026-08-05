<?php

use App\Models\Category;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Kategoriler')] class extends Component {
    public string $ad = '';

    #[Computed]
    public function categories()
    {
        return Category::withCount('quizzes')->get();
    }

    public function ekle(): void
    {
        $this->validate(['ad' => 'required|string|max:255']);

        Category::create(['ad' => $this->ad]);

        $this->reset('ad');

        Flux::toast(variant: 'success', text: 'Kategori eklendi.');
    }

    public function sil(Category $category): void
    {
        $category->delete();

        Flux::toast(variant: 'success', text: 'Kategori silindi.');
    }
}; ?>

<div class="max-w-2xl mx-auto">
    <flux:heading size="xl" class="mb-6">{{ __('Kategoriler') }}</flux:heading>

    <flux:card>
        <form wire:submit="ekle" class="flex gap-2 mb-6">
            <flux:input wire:model="ad" placeholder="Yeni kategori adı" class="flex-1" />
            <flux:button type="submit" variant="primary">Ekle</flux:button>
        </form>

        <div class="space-y-2">
            @foreach ($this->categories as $category)
                <div class="flex justify-between items-center border-b border-zinc-100 dark:border-white/10 py-2">
                    <span>
                        {{ $category->ad }}
                        <span class="text-sm text-zinc-500 dark:text-zinc-400">({{ $category->quizzes_count }} quiz)</span>
                    </span>

                    <flux:button size="sm" variant="danger" wire:click="sil({{ $category->id }})" wire:confirm="Emin misiniz?">
                        {{ __('Sil') }}
                    </flux:button>
                </div>
            @endforeach
        </div>
    </flux:card>
</div>

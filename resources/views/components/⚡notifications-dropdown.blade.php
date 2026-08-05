<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    #[Computed]
    public function unreadCount(): int
    {
        return Auth::user()->unreadNotifications->count();
    }

    #[Computed]
    public function bildirimler()
    {
        return Auth::user()->notifications()->latest()->take(10)->get();
    }

    public function tumunuOku(): void
    {
        Auth::user()->unreadNotifications->markAsRead();

        unset($this->unreadCount, $this->bildirimler);
    }
}; ?>

<flux:dropdown position="bottom" align="start">
    <button type="button"
        class="relative p-2 rounded-lg text-zinc-500 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-white/10">
        <flux:icon name="bell" class="size-5" />

        @if ($this->unreadCount > 0)
            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center">
                {{ $this->unreadCount }}
            </span>
        @endif
    </button>

    <flux:menu class="w-80">
        <div class="p-3 border-b border-zinc-100 dark:border-white/10 flex justify-between items-center">
            <span class="font-medium">{{ __('Bildirimler') }}</span>
            <button type="button" wire:click="tumunuOku" class="text-xs text-accent">
                {{ __('Tümünü okundu işaretle') }}
            </button>
        </div>

        <div class="max-h-80 overflow-y-auto">
            @forelse ($this->bildirimler as $bildirim)
                <flux:menu.item :href="route('quizzes.show', $bildirim->data['quiz_id'])" wire:navigate
                    class="block !h-auto py-2 {{ $bildirim->read_at ? 'opacity-60' : '' }}">
                    <div>
                        <p class="text-sm font-medium">{{ $bildirim->data['baslik'] }}</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $bildirim->data['mesaj'] }}</p>
                    </div>
                </flux:menu.item>
            @empty
                <p class="p-3 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Henüz bildirim yok.') }}</p>
            @endforelse
        </div>
    </flux:menu>
</flux:dropdown>

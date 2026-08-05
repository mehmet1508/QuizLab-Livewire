<?php

use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Kullanıcı Yönetimi')] class extends Component {
    #[Computed]
    public function users()
    {
        return User::withCount('results')->get();
    }

    public function sil(User $user): void
    {
        $this->authorize('kullanici_sil');

        if ($user->id === Auth::id()) {
            Flux::toast(variant: 'danger', text: 'Kendi hesabınızı silemezsiniz.');

            return;
        }

        $user->delete();

        Flux::toast(variant: 'success', text: 'Kullanıcı silindi.');
    }

    public function toggleAdmin(User $user): void
    {
        $user->is_admin = ! $user->is_admin;
        $user->save();

        Flux::toast(variant: 'success', text: 'Kullanıcı durumu güncellendi.');
    }
}; ?>

<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <flux:heading size="xl">{{ __('Kullanıcı Yönetimi') }}</flux:heading>

        <flux:button :href="route('admin.users.create')" wire:navigate variant="primary">
            {{ __('+ Yeni Kullanıcı Ekle') }}
        </flux:button>
    </div>

    <flux:card>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-white/10">
                        <th class="py-2">Ad</th>
                        <th class="py-2">Email</th>
                        <th class="py-2">Çözdüğü Quiz</th>
                        <th class="py-2">Rol</th>
                        <th class="py-2">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($this->users as $user)
                        <tr wire:key="user-{{ $user->id }}" class="border-b border-zinc-100 dark:border-white/5">
                            <td class="py-2">{{ $user->name }}</td>
                            <td class="py-2 text-zinc-500 dark:text-zinc-400">{{ $user->email }}</td>
                            <td class="py-2 text-zinc-500 dark:text-zinc-400">{{ $user->results_count }}</td>
                            <td class="py-2">
                                <button type="button" wire:click="toggleAdmin({{ $user->id }})"
                                    class="{{ $user->is_admin ? 'text-green-600 dark:text-green-400' : 'text-zinc-500 dark:text-zinc-400' }} font-medium">
                                    {{ $user->is_admin ? 'Admin' : 'Kullanıcı' }}
                                </button>
                            </td>
                            <td class="py-2 space-x-2 whitespace-nowrap">
                                <flux:link :href="route('admin.users.edit', $user)" wire:navigate>Düzenle</flux:link>

                                @if ($user->id !== Auth::id())
                                    <flux:link
                                        wire:click="sil({{ $user->id }})"
                                        wire:confirm="Emin misiniz?"
                                        variant="danger"
                                        class="cursor-pointer"
                                    >
                                        Sil
                                    </flux:link>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </flux:card>
</div>

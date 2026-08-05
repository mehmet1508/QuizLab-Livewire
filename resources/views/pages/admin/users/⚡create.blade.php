<?php

use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Yeni Kullanıcı Ekle')] class extends Component {
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public bool $isAdmin = false;

    public function kaydet(): void
    {
        $this->authorize('kullanici_ekle');

        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_admin' => $this->isAdmin,
            'email_verified_at' => now(),
        ]);

        Flux::toast(variant: 'success', text: 'Kullanıcı oluşturuldu.');

        $this->redirect(route('admin.users.index'), navigate: true);
    }
}; ?>

<div class="max-w-xl mx-auto">
    <flux:heading size="xl" class="mb-6">{{ __('Yeni Kullanıcı Ekle') }}</flux:heading>

    <flux:card>
        <form wire:submit="kaydet" class="space-y-4">
            <flux:input wire:model="name" :label="__('Ad Soyad')" />
            <flux:input wire:model="email" type="email" :label="__('Email')" />
            <flux:input wire:model="password" type="password" :label="__('Şifre')" />

            <flux:checkbox wire:model="isAdmin" :label="__('Admin yetkisi ver')" />

            <flux:button type="submit" variant="primary">{{ __('Kaydet') }}</flux:button>
        </form>
    </flux:card>
</div>

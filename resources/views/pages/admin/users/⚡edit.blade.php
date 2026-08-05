<?php

use App\Models\Permission;
use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Kullanıcı Düzenle')] class extends Component {
    public User $user;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public bool $isAdmin = false;

    /** @var array<int, int> */
    public array $permissionIds = [];

    public function mount(User $user): void
    {
        $this->user = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->isAdmin = $user->is_admin;
        $this->permissionIds = $user->permissions->pluck('id')->toArray();
    }

    #[Computed]
    public function allPermissions()
    {
        return Permission::all();
    }

    public function guncelle(): void
    {
        $this->authorize('kullanici_duzenle');

        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $this->user->id,
            'password' => 'nullable|string|min:8',
        ]);

        $this->user->name = $validated['name'];
        $this->user->email = $validated['email'];

        if (Auth::user()->is_admin) {
            $this->user->is_admin = $this->isAdmin;
        }

        if ($validated['password']) {
            $this->user->password = Hash::make($validated['password']);
        }

        $this->user->save();

        if (Auth::user()->is_admin) {
            $this->user->permissions()->sync($this->permissionIds);
        }

        Flux::toast(variant: 'success', text: 'Kullanıcı güncellendi.');

        $this->redirect(route('admin.users.index'), navigate: true);
    }
}; ?>

<div class="max-w-xl mx-auto">
    <flux:heading size="xl" class="mb-6">{{ __('Kullanıcı Düzenle') }}</flux:heading>

    <flux:card>
        <form wire:submit="guncelle" class="space-y-4">
            <flux:input wire:model="name" :label="__('Ad Soyad')" />
            <flux:input wire:model="email" type="email" :label="__('Email')" />
            <flux:input wire:model="password" type="password" :label="__('Yeni Şifre (boş bırakırsanız değişmez)')" />

            <flux:checkbox wire:model="isAdmin" :label="__('Admin yetkisi')" />

            @if (Auth::user()->is_admin)
                <flux:checkbox.group wire:model="permissionIds" :label="__('Özel İzinler')">
                    @foreach ($this->allPermissions as $permission)
                        <flux:checkbox value="{{ $permission->id }}" :label="$permission->etiket" />
                    @endforeach
                </flux:checkbox.group>
            @endif

            <flux:button type="submit" variant="primary">{{ __('Güncelle') }}</flux:button>
        </form>
    </flux:card>
</div>

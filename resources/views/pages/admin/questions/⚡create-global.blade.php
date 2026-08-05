<?php

use App\Models\Question;
use Flux\Flux;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Havuza Yeni Soru Ekle')] class extends Component {
    public string $soruMetni = '';

    /** @var array<int, string> */
    public array $siklar = ['', '', '', ''];

    public ?int $dogruSik = null;

    public function kaydet(): void
    {
        $this->authorize('soru_ekle');

        $validated = $this->validate([
            'soruMetni' => 'required|string',
            'siklar' => 'required|array|min:2',
            'siklar.*' => 'required|string',
            'dogruSik' => 'required|integer',
        ]);

        $question = Question::create(['soru_metni' => $validated['soruMetni']]);

        foreach ($validated['siklar'] as $index => $sikMetni) {
            $question->options()->create([
                'sik_metni' => $sikMetni,
                'dogru_mu' => (int) $validated['dogruSik'] === $index,
            ]);
        }

        Flux::toast(variant: 'success', text: 'Soru havuza eklendi. İstediğiniz quize "Havuzdan Soru Ekle" ile dahil edebilirsiniz.');

        $this->redirect(route('admin.quizzes.index'), navigate: true);
    }
}; ?>

<div class="max-w-xl mx-auto">
    <flux:card>
        <flux:heading size="lg" class="mb-1">{{ __('Havuza Yeni Soru Ekle') }}</flux:heading>

        <flux:link :href="route('admin.quizzes.index')" wire:navigate class="text-sm">&larr; Quiz Listesine Dön</flux:link>

        <flux:text size="sm" class="mt-3 mb-4">
            {{ __('Bu soru herhangi bir quize bağlanmadan doğrudan havuza eklenir. Daha sonra istediğiniz quizde "Havuzdan Soru Ekle" ile kullanabilirsiniz.') }}
        </flux:text>

        <form wire:submit="kaydet" class="space-y-4">
            <flux:input wire:model="soruMetni" :label="__('Soru Metni')" />

            <flux:radio.group wire:model="dogruSik" :label="__('Şıklar (doğru olanı işaretleyin)')">
                @foreach ($siklar as $index => $sik)
                    <div class="flex items-center gap-2">
                        <flux:radio value="{{ $index }}" />
                        <flux:input wire:model="siklar.{{ $index }}" placeholder="Şık {{ $index + 1 }}" class="flex-1" />
                    </div>
                @endforeach
            </flux:radio.group>

            <flux:button type="submit" variant="primary">{{ __('Havuza Ekle') }}</flux:button>
        </form>
    </flux:card>
</div>

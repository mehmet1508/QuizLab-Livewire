<?php

use App\Models\Question;
use App\Models\Quiz;
use Flux\Flux;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Yeni Soru')] class extends Component {
    public Quiz $quiz;

    public string $soruMetni = '';

    /** @var array<int, string> */
    public array $siklar = ['', '', '', ''];

    public ?int $dogruSik = null;

    public function mount(Quiz $quiz): void
    {
        $this->quiz = $quiz;
    }

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

        $this->quiz->questions()->attach($question->id);

        Flux::toast(variant: 'success', text: 'Soru oluşturuldu ve quize eklendi.');

        $this->redirect(route('admin.questions.index', $this->quiz), navigate: true);
    }
}; ?>

<div class="max-w-xl mx-auto">
    <flux:heading size="xl" class="mb-1">{{ $quiz->baslik }} — {{ __('Yeni Soru') }}</flux:heading>

    <flux:card class="mt-4">
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

            <flux:button type="submit" variant="primary">{{ __('Kaydet') }}</flux:button>
        </form>
    </flux:card>
</div>

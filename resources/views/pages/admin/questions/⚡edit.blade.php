<?php

use App\Models\Question;
use App\Models\Quiz;
use Flux\Flux;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Soru Düzenle')] class extends Component {
    public Quiz $quiz;

    public Question $question;

    public string $soruMetni = '';

    /** @var array<int, string> */
    public array $siklar = [];

    public ?int $dogruSik = null;

    public function mount(Quiz $quiz, Question $question): void
    {
        $question->load('options');

        $this->quiz = $quiz;
        $this->question = $question;
        $this->soruMetni = $question->soru_metni;
        $this->siklar = $question->options->pluck('sik_metni')->all();
        $this->dogruSik = $question->options->search(fn ($option) => $option->dogru_mu) ?: null;
    }

    public function guncelle(): void
    {
        $validated = $this->validate([
            'soruMetni' => 'required|string',
            'siklar' => 'required|array|min:2',
            'siklar.*' => 'required|string',
            'dogruSik' => 'required|integer',
        ]);

        $this->question->update(['soru_metni' => $validated['soruMetni']]);

        $options = $this->question->options()->orderBy('id')->get();

        foreach ($validated['siklar'] as $index => $sikMetni) {
            if (isset($options[$index])) {
                $options[$index]->update([
                    'sik_metni' => $sikMetni,
                    'dogru_mu' => (int) $validated['dogruSik'] === $index,
                ]);
            }
        }

        Flux::toast(variant: 'success', text: 'Soru güncellendi.');

        $this->redirect(route('admin.questions.index', $this->quiz), navigate: true);
    }
}; ?>

<div class="max-w-xl mx-auto">
    <flux:heading size="xl" class="mb-1">{{ $quiz->baslik }} — {{ __('Soru Düzenle') }}</flux:heading>

    <flux:card class="mt-4">
        <form wire:submit="guncelle" class="space-y-4">
            <flux:input wire:model="soruMetni" :label="__('Soru Metni')" />

            <flux:radio.group wire:model="dogruSik" :label="__('Şıklar (doğru olanı işaretleyin)')">
                @foreach ($siklar as $index => $sik)
                    <div class="flex items-center gap-2">
                        <flux:radio value="{{ $index }}" />
                        <flux:input wire:model="siklar.{{ $index }}" class="flex-1" />
                    </div>
                @endforeach
            </flux:radio.group>

            <flux:button type="submit" variant="primary">{{ __('Güncelle') }}</flux:button>
        </form>
    </flux:card>
</div>

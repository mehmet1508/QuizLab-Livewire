<?php

use App\Models\Quiz;
use Flux\Flux;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Puan Dağılımı')] class extends Component {
    public Quiz $quiz;

    /** @var array<int, int> */
    public array $puanlar = [];

    public function mount(Quiz $quiz): void
    {
        $quiz->load('questions');
        $this->quiz = $quiz;

        $efektifPuanlar = $quiz->getEfektifPuanlar();

        foreach ($quiz->questions as $question) {
            $this->puanlar[$question->id] = $efektifPuanlar[$question->id] ?? 0;
        }
    }

    public function kaydet(): void
    {
        $this->authorize('quiz_duzenle');

        $this->validate([
            'puanlar' => 'required|array',
            'puanlar.*' => 'required|integer|min:0',
        ]);

        $toplam = array_sum($this->puanlar);

        if ($toplam > 100) {
            $this->addError('puanlar', "Toplam puan 100'ü geçemez. Girdiğiniz toplam: {$toplam}");

            return;
        }

        foreach ($this->puanlar as $questionId => $puan) {
            $this->quiz->questions()->updateExistingPivot($questionId, ['puan' => $puan]);
        }

        Flux::toast(variant: 'success', text: "Puanlar güncellendi (toplam: {$toplam}).");

        $this->redirect(route('admin.quizzes.index'), navigate: true);
    }

    public function sifirla(): void
    {
        $this->authorize('quiz_duzenle');

        foreach ($this->quiz->questions as $question) {
            $this->quiz->questions()->updateExistingPivot($question->id, ['puan' => null]);
        }

        $efektifPuanlar = $this->quiz->getEfektifPuanlar();

        foreach ($this->quiz->questions as $question) {
            $this->puanlar[$question->id] = $efektifPuanlar[$question->id] ?? 0;
        }

        Flux::toast(variant: 'success', text: 'Puanlar otomatik eşit dağılıma sıfırlandı.');
    }
}; ?>

<div class="max-w-2xl mx-auto">
    <flux:card>
        <flux:heading size="lg" class="mb-4">{{ $quiz->baslik }} — Puan Dağılımı</flux:heading>

        @error('puanlar')
            <flux:text class="text-red-600 dark:text-red-400 mb-4">{{ $message }}</flux:text>
        @enderror

        <flux:text size="sm" class="mb-4">
            {{ __('Toplam puan en fazla 100 olabilir. Bir soruya değer girmezseniz (0 bırakırsanız), kalan puan diğer sorular arasında otomatik eşit paylaştırılır.') }}
        </flux:text>

        @if ($quiz->questions->isEmpty())
            <flux:text>{{ __('Bu quiz\'e henüz soru eklenmemiş.') }}</flux:text>
        @else
            <form wire:submit="kaydet">
                @foreach ($quiz->questions as $question)
                    <div class="mb-3 flex items-center justify-between gap-4">
                        <span class="flex-1">{{ $question->soru_metni }}</span>
                        <flux:input type="number" wire:model="puanlar.{{ $question->id }}" min="0" max="100" class="w-24" />
                    </div>
                @endforeach

                <flux:text size="sm" class="mt-2">Toplam (otomatik dağıtılanlar dahil): {{ array_sum($puanlar) }} / 100</flux:text>

                <flux:button type="submit" variant="primary" class="mt-4">{{ __('Kaydet') }}</flux:button>
            </form>

            <flux:button variant="ghost" size="sm" wire:click="sifirla" class="mt-4">
                {{ __('Otomatik Eşit Dağılıma Sıfırla') }}
            </flux:button>
        @endif
    </flux:card>
</div>

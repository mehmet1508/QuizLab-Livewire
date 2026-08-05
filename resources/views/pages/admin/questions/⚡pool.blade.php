<?php

use App\Models\Question;
use App\Models\Quiz;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Havuzdan Soru Ekle')] class extends Component {
    public Quiz $quiz;

    /** @var array<int, int> */
    public array $questionIds = [];

    public function mount(Quiz $quiz): void
    {
        $this->quiz = $quiz;
    }

    #[Computed]
    public function availableQuestions()
    {
        $attachedIds = $this->quiz->questions()->pluck('questions.id');

        return Question::whereNotIn('id', $attachedIds)->with('options')->get();
    }

    public function ekle(): void
    {
        $this->authorize('soru_ekle');

        $this->validate([
            'questionIds' => 'required|array',
            'questionIds.*' => 'exists:questions,id',
        ]);

        $this->quiz->questions()->syncWithoutDetaching($this->questionIds);

        Flux::toast(variant: 'success', text: 'Seçilen sorular quize eklendi.');

        $this->redirect(route('admin.questions.index', $this->quiz), navigate: true);
    }
}; ?>

<div class="max-w-3xl mx-auto">
    <flux:card>
        <flux:heading size="lg" class="mb-1">{{ $quiz->baslik }} — {{ __('Havuzdan Soru Ekle') }}</flux:heading>

        <flux:link :href="route('admin.questions.index', $quiz)" wire:navigate class="text-sm">&larr; Geri Dön</flux:link>

        @if ($this->availableQuestions->isEmpty())
            <flux:text class="mt-4">{{ __('Havuzda eklenebilecek başka soru yok. Tüm sorular ya bu quizde ya da hiç soru oluşturulmamış.') }}</flux:text>
        @else
            <form wire:submit="ekle" class="mt-4">
                <flux:checkbox.group wire:model="questionIds">
                    @foreach ($this->availableQuestions as $question)
                        <flux:checkbox value="{{ $question->id }}" :label="$question->soru_metni"
                            :description="$question->options->count().' şık'" />
                    @endforeach
                </flux:checkbox.group>

                <flux:button type="submit" variant="primary" class="mt-4">{{ __('Seçilenleri Ekle') }}</flux:button>
            </form>
        @endif
    </flux:card>
</div>

<?php

use App\Models\Category;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use App\Notifications\YeniQuizEklendi;
use Flux\Flux;
use Illuminate\Support\Facades\Notification;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Yeni Quiz Ekle')] class extends Component {
    use WithFileUploads;

    public string $baslik = '';

    public ?int $kategoriId = null;

    public string $aciklama = '';

    public $gorsel;

    public string $sureTipi = 'sabit';

    public ?int $sureDakika = null;

    public string $sureBirimi = 'dakika';

    /** @var array<int, int> */
    public array $questionIds = [];

    #[Computed]
    public function kategoriler()
    {
        return Category::all();
    }

    #[Computed]
    public function allQuestions()
    {
        return Question::with('options')->get();
    }

    public function kaydet(): void
    {
        $this->authorize('quiz_ekle');

        $validated = $this->validate([
            'baslik' => 'required|string|max:255',
            'aciklama' => 'nullable|string',
            'sureTipi' => 'required|in:sabit,soru_basi',
            'sureBirimi' => 'required|in:dakika,saniye',
            'sureDakika' => 'nullable|integer|min:1',
            'gorsel' => 'nullable|image|max:2048',
            'kategoriId' => 'nullable|exists:categories,id',
        ]);

        $veri = [
            'baslik' => $validated['baslik'],
            'aciklama' => $validated['aciklama'],
            'sure_dakika' => $validated['sureDakika'],
            'sure_tipi' => $validated['sureTipi'],
            'sure_birimi' => $validated['sureBirimi'],
            'kategori_id' => $validated['kategoriId'],
        ];

        if ($this->gorsel) {
            $veri['gorsel'] = $this->gorsel->store('quiz-gorselleri', 'public');
        }

        $quiz = Quiz::create($veri);

        if (! empty($this->questionIds)) {
            $quiz->questions()->attach($this->questionIds);
        }

        Notification::send(User::all(), new YeniQuizEklendi($quiz));

        Flux::toast(variant: 'success', text: 'Quiz oluşturuldu.');

        $this->redirect(route('admin.quizzes.index'), navigate: true);
    }
}; ?>

<div class="max-w-xl mx-auto">
    <flux:heading size="xl" class="mb-6">{{ __('Yeni Quiz Ekle') }}</flux:heading>

    <flux:card>
        <form wire:submit="kaydet" class="space-y-4">
            <flux:input wire:model="baslik" :label="__('Başlık')" />

            <flux:select wire:model="kategoriId" :label="__('Kategori')">
                <option value="">Kategorisiz</option>
                @foreach ($this->kategoriler as $kategori)
                    <option value="{{ $kategori->id }}">{{ $kategori->ad }}</option>
                @endforeach
            </flux:select>

            <flux:textarea wire:model="aciklama" :label="__('Açıklama')" />

            <flux:input type="file" wire:model="gorsel" :label="__('Quiz Görseli (opsiyonel)')" accept="image/*" />

            <flux:radio.group wire:model="sureTipi" :label="__('Süre Türü')">
                <flux:radio value="sabit" label="Toplam süre belirle" />
                <flux:radio value="soru_basi" label="Soru başına süre belirle" />
            </flux:radio.group>

            <div class="flex gap-2 items-start">
                <flux:input type="number" wire:model="sureDakika" min="1" :label="__('Süre Değeri (boş = süresiz)')" class="flex-1" />

                <flux:select wire:model="sureBirimi" class="w-auto mt-6">
                    <option value="dakika">Dakika</option>
                    <option value="saniye">Saniye</option>
                </flux:select>
            </div>
            <flux:text size="sm">"Soru başına" seçilirse bu değer, quiz'deki soru sayısıyla çarpılır.</flux:text>

            <div>
                <flux:heading size="sm" class="mb-2">{{ __('Havuzdan Sorular Seç (opsiyonel)') }}</flux:heading>

                @if ($this->allQuestions->isEmpty())
                    <flux:text size="sm">{{ __('Havuzda henüz soru yok. Quiz\'i oluşturduktan sonra soru ekleyebilirsiniz.') }}</flux:text>
                @else
                    <div class="border border-zinc-200 dark:border-white/10 rounded max-h-64 overflow-y-auto p-2">
                        <flux:checkbox.group wire:model="questionIds">
                            @foreach ($this->allQuestions as $question)
                                <flux:checkbox value="{{ $question->id }}" :label="$question->soru_metni" />
                            @endforeach
                        </flux:checkbox.group>
                    </div>
                @endif
            </div>

            <flux:button type="submit" variant="primary">{{ __('Kaydet') }}</flux:button>
        </form>
    </flux:card>
</div>

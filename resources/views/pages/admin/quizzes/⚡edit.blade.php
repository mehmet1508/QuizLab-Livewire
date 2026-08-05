<?php

use App\Models\Category;
use App\Models\Quiz;
use Flux\Flux;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Quiz Düzenle')] class extends Component {
    use WithFileUploads;

    public Quiz $quiz;

    public string $baslik = '';

    public ?int $kategoriId = null;

    public string $aciklama = '';

    public $gorsel;

    public string $sureTipi = 'sabit';

    public ?int $sureDakika = null;

    public string $sureBirimi = 'dakika';

    public function mount(Quiz $quiz): void
    {
        $this->quiz = $quiz;
        $this->baslik = $quiz->baslik;
        $this->kategoriId = $quiz->kategori_id;
        $this->aciklama = $quiz->aciklama ?? '';
        $this->sureTipi = $quiz->sure_tipi ?? 'sabit';
        $this->sureDakika = $quiz->sure_dakika;
        $this->sureBirimi = $quiz->sure_birimi ?? 'dakika';
    }

    #[Computed]
    public function kategoriler()
    {
        return Category::all();
    }

    public function guncelle(): void
    {
        $this->authorize('quiz_duzenle');

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
            if ($this->quiz->gorsel) {
                Storage::disk('public')->delete($this->quiz->gorsel);
            }

            $veri['gorsel'] = $this->gorsel->store('quiz-gorselleri', 'public');
        }

        $this->quiz->update($veri);

        Flux::toast(variant: 'success', text: 'Quiz güncellendi.');

        $this->redirect(route('admin.quizzes.index'), navigate: true);
    }
}; ?>

<div class="max-w-xl mx-auto">
    <flux:heading size="xl" class="mb-6">{{ __('Quiz Düzenle') }}</flux:heading>

    <flux:card>
        <form wire:submit="guncelle" class="space-y-4">
            <flux:input wire:model="baslik" :label="__('Başlık')" />

            <flux:select wire:model="kategoriId" :label="__('Kategori')">
                <option value="">Kategorisiz</option>
                @foreach ($this->kategoriler as $kategori)
                    <option value="{{ $kategori->id }}">{{ $kategori->ad }}</option>
                @endforeach
            </flux:select>

            <flux:textarea wire:model="aciklama" :label="__('Açıklama')" />

            <div>
                <flux:input type="file" wire:model="gorsel" :label="__('Quiz Görseli (opsiyonel)')" accept="image/*" />

                @if ($quiz->gorsel)
                    <img src="{{ $quiz->gorsel_url }}" class="mt-2 h-24 rounded object-cover">
                    <flux:text size="sm" class="mt-1">{{ __('Yeni bir görsel seçerseniz mevcut görsel değişir.') }}</flux:text>
                @endif
            </div>

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

            <flux:button type="submit" variant="primary">{{ __('Güncelle') }}</flux:button>
        </form>
    </flux:card>
</div>

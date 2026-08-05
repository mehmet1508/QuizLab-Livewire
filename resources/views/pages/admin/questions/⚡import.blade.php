<?php

use App\Imports\QuestionsImport;
use App\Models\Quiz;
use Flux\Flux;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

new #[Title('Excel ile Soru Yükle')] class extends Component {
    use WithFileUploads;

    public Quiz $quiz;

    public $excelDosya;

    public function mount(Quiz $quiz): void
    {
        $this->quiz = $quiz;
    }

    public function yukle(): void
    {
        $this->authorize('soru_ekle');

        $this->validate([
            'excelDosya' => 'required|file|mimes:xlsx,xls,csv,txt',
        ]);

        $import = new QuestionsImport($this->quiz);
        Excel::import($import, $this->excelDosya->getRealPath());

        $mesaj = "{$import->eklenenSayisi} soru başarıyla eklendi.";

        if (! empty($import->hatalar)) {
            session()->flash('hatalar', $import->hatalar);
        }

        Flux::toast(variant: 'success', text: $mesaj);

        $this->redirect(route('admin.questions.index', $this->quiz), navigate: true);
    }
}; ?>

<div class="max-w-xl mx-auto">
    <flux:card>
        <flux:heading size="lg" class="mb-1">{{ $quiz->baslik }} — {{ __('Excel ile Soru Yükle') }}</flux:heading>

        <flux:link :href="route('admin.questions.index', $quiz)" wire:navigate class="text-sm">&larr; Geri Dön</flux:link>

        <div class="mt-4 mb-6 p-4 bg-zinc-50 dark:bg-white/5 rounded text-sm">
            <p class="font-medium mb-2">Excel/CSV Formatı</p>
            <p>Sütunlar: <code>soru_metni, sik1, sik2, sik3, sik4, dogru_sik</code></p>
            <p class="mt-1"><code>dogru_sik</code> sütunu doğru şıkkın kaçıncı sırada olduğunu gösterir (1-4 arası).</p>
            <a href="{{ route('admin.questions.ornek') }}" class="text-accent underline inline-block mt-2">Örnek dosyayı indir</a>
        </div>

        <form wire:submit="yukle" class="space-y-4">
            <flux:input type="file" wire:model="excelDosya" :label="__('Excel/CSV Dosyası')" accept=".xlsx,.xls,.csv,.txt" />

            <flux:button type="submit" variant="primary">{{ __('Yükle') }}</flux:button>
        </form>
    </flux:card>
</div>

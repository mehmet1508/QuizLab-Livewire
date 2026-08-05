<?php

namespace App\Imports;

use App\Models\Question;
use App\Models\Quiz;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class QuestionsImport implements ToCollection, WithHeadingRow
{
    protected Quiz $quiz;

    public int $eklenenSayisi = 0;

    public array $hatalar = [];

    public function __construct(Quiz $quiz)
    {
        $this->quiz = $quiz;
    }

    public function collection($rows)
    {
        foreach ($rows as $index => $row) {
            $satirNo = $index + 2; // başlık satırı + 1-index

            $soruMetni = trim($row['soru_metni'] ?? '');
            $siklar = [
                trim($row['sik1'] ?? ''),
                trim($row['sik2'] ?? ''),
                trim($row['sik3'] ?? ''),
                trim($row['sik4'] ?? ''),
            ];
            $dogruSik = (int) ($row['dogru_sik'] ?? 0);

            if (empty($soruMetni)) {
                $this->hatalar[] = "Satır $satirNo: soru metni boş, atlandı.";

                continue;
            }

            $doluSiklar = array_filter($siklar, fn ($s) => $s !== '');
            if (count($doluSiklar) < 2) {
                $this->hatalar[] = "Satır $satirNo: en az 2 şık gerekli, atlandı.";

                continue;
            }

            if ($dogruSik < 1 || $dogruSik > 4 || empty($siklar[$dogruSik - 1])) {
                $this->hatalar[] = "Satır $satirNo: doğru şık numarası geçersiz, atlandı.";

                continue;
            }

            $question = Question::create(['soru_metni' => $soruMetni]);

            foreach ($siklar as $i => $sikMetni) {
                if ($sikMetni === '') {
                    continue;
                }

                $question->options()->create([
                    'sik_metni' => $sikMetni,
                    'dogru_mu' => $i === $dogruSik - 1,
                ]);
            }

            $this->quiz->questions()->attach($question->id);
            $this->eklenenSayisi++;
        }
    }
}

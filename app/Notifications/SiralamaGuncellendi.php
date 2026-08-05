<?php

namespace App\Notifications;

use App\Models\Quiz;
use Illuminate\Notifications\Notification;

class SiralamaGuncellendi extends Notification
{
    public function __construct(public Quiz $quiz) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'baslik' => 'Sıralama güncellendi',
            'mesaj' => "\"{$this->quiz->baslik}\" quizinde sıralama değişti.",
            'quiz_id' => $this->quiz->id,
        ];
    }
}

<?php

namespace App\Notifications;

use App\Models\Quiz;
use Illuminate\Notifications\Notification;

class YeniQuizEklendi extends Notification
{
    public function __construct(public Quiz $quiz) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'baslik' => 'Yeni quiz eklendi',
            'mesaj' => "\"{$this->quiz->baslik}\" adlı yeni bir quiz eklendi.",
            'quiz_id' => $this->quiz->id,
        ];
    }
}

<?php

namespace App\Notifications;

use App\Models\WorkshopSession;
use App\Models\WorkshopSurveyTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WorkshopSurveyPendingReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public WorkshopSession $session,
        public WorkshopSurveyTemplate $template,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $kindLabel = WorkshopSurveyTemplate::KINDS[$this->template->kind] ?? $this->template->kind;

        return (new MailMessage)
            ->subject("Pending: {$kindLabel} survey for \"{$this->session->name}\"")
            ->greeting("Hi {$notifiable->first_name},")
            ->line("You still have a {$kindLabel} survey to complete for \"{$this->session->name}\".")
            ->action('Complete the survey', route('portal.my-workshops.surveys.show', [$this->session, $this->template]))
            ->line('It only takes a few minutes.');
    }
}

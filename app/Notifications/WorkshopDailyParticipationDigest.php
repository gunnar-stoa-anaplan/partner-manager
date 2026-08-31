<?php

namespace App\Notifications;

use App\Models\WorkshopSession;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WorkshopDailyParticipationDigest extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array{total: int, completed: int, at_risk: int}  $summary
     */
    public function __construct(
        public WorkshopSession $session,
        public array $summary,
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
        return (new MailMessage)
            ->subject("Daily digest: \"{$this->session->name}\"")
            ->greeting("Hi {$notifiable->name},")
            ->line("Here's where \"{$this->session->name}\" stands today:")
            ->line("{$this->summary['total']} participant(s) enrolled, {$this->summary['completed']} completed, {$this->summary['at_risk']} at risk of falling behind.")
            ->action('View session', route('workshops.sessions.show', [$this->session->workshop, $this->session]));
    }
}

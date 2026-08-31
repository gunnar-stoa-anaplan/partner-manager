<?php

namespace App\Notifications;

use App\Models\WorkshopSession;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WorkshopSessionReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public WorkshopSession $session) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $workshop = $this->session->workshop;

        return (new MailMessage)
            ->subject("Reminder: \"{$workshop->name}\" starts {$this->session->start_date->format('M j')}")
            ->greeting("Hi {$notifiable->first_name},")
            ->line("Your workshop session \"{$this->session->name}\" for {$workshop->name} starts on {$this->session->start_date->format('l, F j')}.")
            ->action('View session details', route('portal.my-workshops.show', $this->session))
            ->line('If you haven\'t completed the pre-workshop survey yet, do that before the session starts.');
    }
}

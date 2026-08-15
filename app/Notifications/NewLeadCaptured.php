<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewLeadCaptured extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Lead $lead) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject(__('crm.new_lead_subject', ['reference' => $this->lead->reference]))
            ->greeting(__('crm.new_lead_greeting'))
            ->line(__('crm.new_lead_name').': '.$this->lead->name)
            ->line(__('crm.new_lead_phone').': '.$this->lead->phone)
            ->line(__('crm.new_lead_source').': '.$this->lead->source->getLabel())
            ->line(__('crm.new_lead_score').': '.$this->lead->score.'/100');

        if ($this->lead->product) {
            $message->line(__('crm.new_lead_product').': '.$this->lead->product->name);
        }

        if (filled($this->lead->message)) {
            $message->line(__('crm.new_lead_message').': '.$this->lead->message);
        }

        return $message->action(
            __('crm.new_lead_action'),
            url('/admin/leads/'.$this->lead->id.'/edit')
        );
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'lead_id' => $this->lead->id,
            'reference' => $this->lead->reference,
            'name' => $this->lead->name,
            'phone' => $this->lead->phone,
            'score' => $this->lead->score,
        ];
    }
}

<?php

namespace App\Notifications;

use App\Models\TravelOrder;
use App\Enums\TravelOrderStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TravelOrderStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    private TravelOrder $travelOrder;
    private TravelOrderStatus $newStatus;
    public $tries = 3;

    public function __construct(TravelOrder $travelOrder)
    {
        $this->travelOrder = $travelOrder;
        $this->newStatus = TravelOrderStatus::tryFrom($travelOrder->status);
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $statusMessage = $this->getStatusMessage();
        $statusColor = $this->getStatusColor();

        return (new MailMessage)
            ->subject("Status do Pedido de Viagem Atualizado - {$this->travelOrder->destination}")
            ->view('emails.travel-order-status', [
                'travelOrder' => $this->travelOrder,
                'status' => $this->newStatus->value,
                'statusMessage' => $statusMessage,
                'statusColor' => $statusColor,
                'user' => $notifiable,
            ]);
    }

    private function getStatusMessage(): string
    {
        return match ($this->newStatus) {
            TravelOrderStatus::APPROVED => 'Seu pedido de viagem foi aprovado!',
            TravelOrderStatus::CANCELED => 'Seu pedido de viagem foi cancelado.',
            default => 'O status do seu pedido de viagem foi atualizado.',
        };
    }

    private function getStatusColor(): string
    {
        return match ($this->newStatus) {
            TravelOrderStatus::APPROVED => '#10b981',
            TravelOrderStatus::CANCELED => '#ef4444',
            default => '#6b7280',
        };
    }

    public function getTravelOrder(): TravelOrder
    {
        return $this->travelOrder;
    }

    public function getNewStatus(): TravelOrderStatus
    {
        return $this->newStatus;
    }
}

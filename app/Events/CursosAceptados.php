<?php

namespace App\Events;

use App\Models\DeteccionNecesidades;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CursosAceptados implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $curso;
    /**
     * Create a new event instance.
     */
    public function __construct($cursos)
    {
        $this->curso = $cursos;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('cursos-aceptados'),
        ];
    }
}

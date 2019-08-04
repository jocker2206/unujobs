<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Models\User;
use App\Notifications\BasicNotification;

class ImportQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    private $url;
    private $titulo;


    public function __construct($url, $titulo)
    {
        $this->url = $url;
        $this->titulo = $titulo;
    }


    public function handle()
    {
        $users = User::all();
    
        foreach ($users as $user) {
            $user->notify(new BasicNotification(
                $this->url, 
                "La importación {$this->titulo}, ya está lista!",
                "fas fa-upload",
                "bg-primary"
            ));
        }
    }
}

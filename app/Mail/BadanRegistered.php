<?php

namespace App\Mail;

use PDF;
use App\Infrastructures\Badan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BadanRegistered extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $badan;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Badan $badan)
    {
        $this->badan = $badan;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $kontak = $this->badan->kontak;
        $pdf = PDF::loadView('pdf.registeredNotifier', ['badan' => $this->badan, 'kontak' => $kontak]);
        $subject = "Pemesanan " . $this->badan->kategori. " " . $this->badan->nama;

        return $this
            ->subject($subject)
            ->view('mail.registeredNotifier')
            ->attachData($pdf->output(), $this->badan->kategori.' '.$this->badan->nama . '.pdf', [
                'mime' => 'application/pdf',
            ])
            ->with([
                'subject' => $subject,
                'kontak' => $kontak
            ]);
    }
}

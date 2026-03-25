<?php

namespace App\Mail;

use App\Models\Company;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CompanyAdminWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Company $company,
        public User $admin,
        public string $plainPin
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Vos accès PointagePro Mobile - ' . $this->company->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin_welcome',
        );
    }
}

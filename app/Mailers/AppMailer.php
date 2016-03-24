<?php

namespace App\Mailers;

use App\Models\Person;
use Illuminate\Contracts\Mail\Mailer;
use SendGrid;
use SendGrid\Email;

class AppMailer
{
    /**
     * The Laravel Mailer instance.
     *
     * @var Mailer
     */
    protected $mailer;

    /**
     * The sender of the email.
     *
     * @var string
     */
    protected $from = 'no-reply@medea.weopendata.com';

    /**
     * The recipient of the email.
     *
     * @var string
     */
    protected $to;

    /**
     * The view for the email.
     *
     * @var string
     */
    protected $view;

    /**
     * The data associated with the view for the email.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Create a new app mailer instance.
     *
     * @param Mailer $mailer
     */
    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Deliver a password reset link.
     *
     * @param Person $user
     * @return void
     */
    public function sendResetLinkEmail(Person $user)
    {
        $this->to = $user->email;
        $this->view = 'auth.emails.password';
        $this->data = compact('user');
        $this->subject = 'Reset your password';
        $this->deliver();
    }

    /**
     * Deliver the email confirmation.
     *
     * @param  Person $user
     * @return void
     */
    public function sendEmailConfirmationTo(Person $user)
    {
        $this->to = $user->email;
        $this->view = 'auth.emails.confirm';
        $this->data = compact('user');
        $this->subject = 'Complete your registration';
        $this->deliver();
    }

    /**
     * Deliver a registration email to the admin
     *
     * @param Person $user
     * @return void
     */
    public function sendRegistrationToAdmin(Person $user)
    {
        $this->to = env('ADMIN_EMAIL');
        $this->view = 'auth.emails.adminconfirm';

        $roles_string = '';

        foreach ($user->getRoles() as $role) {
            $roles_string .= $role . ',';
        }

        $roles_string = rtrim($roles_string, ',');

        $this->data = ['user' => $user, 'roles' => $roles_string];
        $this->subject = 'Nieuwe registratie';
        $this->deliver();
    }

    /**
     * Deliver the email.
     *
     * @return void
     */
    public function deliver()
    {
        $sendgrid = new SendGrid(env('SEND_GRID_API_KEY'));
        $email = new Email();

        $html = view($this->view)
                    ->with($this->data)
                    ->render();

        $email->addTo($this->to)
        ->setFrom("no-reply@medea.weopendata.com")
        ->setSubject($this->subject)
        ->setHtml($html);

        $sendgrid->send($email);
    }
}
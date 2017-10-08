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
    protected $from = 'no-reply@vondsten.be';

    /**
     * The recipient of the email.
     *
     * @var string
     */
    protected $recipient;

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
     * @param  Person $user
     * @return void
     */
    public function sendResetLinkEmail(Person $user)
    {
        $this->to = $user->email;
        $this->view = 'auth.emails.password';
        $this->data = compact('user');
        $this->subject = 'Reset je wachtwoord';
        $this->deliver();
    }

    /**
     * Deliver the email confirmation. (not used atm)
     *
     * @param  Person $user
     * @return void
     */
    public function sendEmailConfirmationTo(Person $user)
    {
        $this->to = $user->email;
        $this->view = 'auth.emails.confirm';
        $this->data = compact('user');
        $this->subject = 'Voltooi je registratie.';
        $this->deliver();
    }

    /**
     * Deliver a registration email to the admin
     *
     * @param  Person $user
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
     * Send a confirmation of registration email
     *
     * @param  Person $user
     * @return void
     */
    public function sendRegistrationConfirmation(Person $user)
    {
        $this->to = $user->email;
        $this->view = 'auth.emails.registrationconfirmation';
        $this->data = compact('user');
        $this->subject = 'Uw registratie werd goedgekeurd!';
        $this->deliver();
    }

    /**
     * Send a denial of registration email of the acceptance of the user account to that user
     *
     * @param  Person $user
     * @return void
     */
    public function sendRegistrationDenial(Person $user)
    {
        $this->to = $user->email;
        $this->view = 'auth.emails.registrationdenial';
        $this->data = compact('user');
        $this->subject = 'Uw registratie werd niet goedgekeurd';
        $this->deliver();
    }

    /**
     * Send a notification about the validation status of the user's find
     *
     * @param  Person  $user
     * @param  string  $title  The title of the find
     * @param  integer $findId The ID of the find
     * @return void
     */
    public function sendFindStatusUpdate(Person $user, $title, $findId)
    {
        $this->to = $user->email;
        $this->view = 'notifications.emails.statuschanged';
        $this->data = ['user' => $user, 'title' => $title, 'findId' => $findId];
        $this->subject = 'Uw vondst werd behandeld door een validator';
        $this->deliver();
    }

    /**
     * Send an email to the user that has added a new find
     *
     * @param  Person  $user   The user that has created the find
     * @param  string  $title  The title of the find
     * @param  integer $findId The ID of the find
     * @return void
     */
    public function sendNewFindEmail(Person $user, $title, $findId)
    {
        $this->to = $user->email;
        $this->view = 'notifications.emails.newfind';
        $this->data = compact('user', 'title', 'findId');
        $this->subject = 'Nieuwe vondst';
        $this->deliver();
    }

    /**
     * Send an email to a certain person through the platform
     *
     * @param  string $message
     * @param  Person $user
     * @param  Person $sender
     * @return void
     */
    public function sendPlatformMessage($message, $recipient, $sender)
    {
        $this->to = $recipient->email;
        $this->view = 'notifications.emails.privatemessage';
        $this->data = ['message' => $message, 'sender' => $sender, 'recipient' => $recipient];
        $this->subject = 'MEDEA - Nieuw bericht';
        $this->deliver();
    }

    /**
     * Deliver the email.
     *
     * @return void
     */
    public function deliver()
    {
        $html = view($this->view)
                    ->with($this->data)
                    ->render();

        if (env('APP_ENV') != 'production') {
            \Log::info($html);
        } else {
            $sendgrid = new SendGrid(env('SEND_GRID_API_KEY'));
            $email = new Email();

            $email->addTo($this->to)
            ->setFrom('no-reply@medea.weopendata.com')
            ->setSubject($this->subject)
            ->setHtml($html);

            $sendgrid->send($email);
        }
    }
}

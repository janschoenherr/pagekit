<?php

namespace Pagekit\User\Controller;

use Pagekit\Application as App;
use Pagekit\Application\Exception;
use Pagekit\Module\Module;
use Pagekit\User\Model\User;

class RegistrationController
{
    /**
     * @var Module
     */
    protected $module;

    public function __construct()
    {
        $this->module = App::module('system/user');
    }

    public function indexAction()
    {
        if (App::user()->isAuthenticated()) {
            App::message()->info(__('You are already logged in.'));
            return App::redirect();
        }

        if ($this->module->config('registration') == 'admin') {
            App::message()->info(__('Public user registration is disabled.'));
            return App::redirect();
        }

        return [
            '$view' => [
                'title' => __('User Registration'),
                'name'  => 'system/user/registration.php'
            ]
        ];
    }

    /**
     * @Request({"user": "array"})
     */
    public function registerAction($data)
    {
        try {

            if (App::user()->isAuthenticated() || $this->module->config('registration') == 'admin') {
                return App::redirect();
            }

            if (!App::csrf()->validate()) {
                throw new Exception(__('Invalid token. Please try again.'));
            }

            $password = @$data['password'];
            if (trim($password) != $password || strlen($password) < 3) {
                throw new Exception(__('Invalid Password.'));
            }

            $user = User::create();
            $user->setRegistered(new \DateTime);
            $user->setName(@$data['name']);
            $user->setUsername(@$data['username']);
            $user->setEmail(@$data['email']);
            $user->setPassword(App::get('auth.password')->hash($password));
            $user->setStatus(User::STATUS_BLOCKED);

            $token = App::get('auth.random')->generateString(32);
            $admin = $this->module->config('registration') == 'approval';

            if ($verify = $this->module->config('require_verification')) {

                $user->setActivation($token);

            } elseif ($admin) {

                $user->setActivation($token);
                $user->set('verified', true);

            } else {

                $user->setStatus(User::STATUS_ACTIVE);

            }

            $user->validate();
            $user->save();

            if ($verify) {

                $this->sendVerificationMail($user);
                $message = __('Your user account has been created. Complete your registration, by clicking the link provided in the mail that has been sent to you.');

            } elseif ($admin) {

                $this->sendApproveMail($user);
                $message = __('Your user account has been created and is pending approval by the site administrator.');

            } else {

                $this->sendWelcomeEmail($user);
                $message = __('Your user account has been created.');

            }

        } catch (Exception $e) {
            App::abort(400, $e->getMessage());
        }

        App::message()->success($message);

        return ['redirect' => App::url('@user/login', [], true)];
    }

    /**
     * @Request({"user", "key"})
     */
    public function activateAction($username, $activation)
    {
        if (empty($username) || empty($activation) || !$user = User::where(['username' => $username, 'activation' => $activation, 'status' => User::STATUS_BLOCKED, 'access IS NULL'])->first()) {
            App::message()->error(__('Invalid key.'));
            return App::redirect();
        }

        if ($admin = $this->module->config('registration') == 'approval' and !$user->get('verified')) {

            $user->setActivation(App::get('auth.random')->generateString(32));
            $this->sendApproveMail($user);

            App::message()->success(__('Your email has been verified. Once an administrator approves your account, you will be notified by email.'));

        } else {

            $user->set('verified', true);
            $user->setStatus(User::STATUS_ACTIVE);
            $user->setActivation('');
            $this->sendWelcomeEmail($user);

            if ($admin) {
                App::message()->success(__('The user\'s account has been activated and the user has been notified about it.'));
            } else {
                App::message()->success(__('Your account has been activated.'));
            }
        }

        $user->save();

        return App::redirect('@user/login');
    }

    protected function sendWelcomeEmail($user)
    {
        try {

            $mail = App::mailer()->create();
            $mail->setTo($user->getEmail())
                 ->setSubject(__('Welcome to %site%!', ['%site%' => App::module('system/site')->config('title')]))
                 ->setBody(App::view('system/user:mails/welcome.php', compact('user', 'mail')), 'text/html')
                 ->send();

        } catch (\Exception $e) {}
    }

    protected function sendVerificationMail($user)
    {
        try {

            $mail = App::mailer()->create();
            $mail->setTo($user->getEmail())
                 ->setSubject(__('Activate your %site% account.', ['%site%' => App::module('system/site')->config('title')]))
                 ->setBody(App::view('system/user:mails/verification.php', compact('user', 'mail')), 'text/html')
                 ->send();

        } catch (\Exception $e) {
            throw new Exception(__('Unable to send verification link.'));
        }
    }

    protected function sendApproveMail($user)
    {
        try {

            $mail = App::mailer()->create();
            $mail->setTo(App::module('mail')->config('from_address'))
                 ->setSubject(__('Approve an account at %site%.', ['%site%' => App::module('system/site')->config('title')]))
                 ->setBody(App::view('system/user:mails/approve.php', compact('user', 'mail')), 'text/html')
                 ->send();

        } catch (\Exception $e) {}
    }
}

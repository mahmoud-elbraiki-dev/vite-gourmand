<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\Csrf;
use App\Core\Mailer;
use App\Core\View;

final class ContactController
{
    public function index(): void
    {
        if (is_post()) {
            Csrf::check();
            // Pot de miel anti-robot : champ invisible qui doit rester vide
            if (post('site_web') !== '') {
                redirect('/contact');
            }
            $titre = post('titre');
            $description = post('description');
            $email = post('email');
            $errors = [];
            if ($titre === '' || mb_strlen($titre) > 120) {
                $errors[] = 'Le titre est obligatoire (120 caractères max.).';
            }
            if (mb_strlen($description) < 10 || mb_strlen($description) > 3000) {
                $errors[] = 'La description doit contenir entre 10 et 3000 caractères.';
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Votre adresse e-mail est invalide.';
            }
            if ($errors) {
                keep_old($_POST);
                foreach ($errors as $err) {
                    flash('error', $err);
                }
                redirect('/contact');
            }
            Mailer::send(env('MAIL_COMPANY', 'contact@vitegourmand.fr'), '[Contact site] ' . $titre,
                '<p><strong>De :</strong> ' . e($email) . '</p><p>' . nl2br(e($description)) . '</p>', $email);
            clear_old();
            flash('success', 'Merci ! Votre message a bien été envoyé, nous vous répondrons rapidement.');
            redirect('/contact');
        }
        View::render('pages/contact', ['title' => 'Contact']);
    }
}

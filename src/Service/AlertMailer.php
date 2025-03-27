<?php

namespace App\Service;

use App\Entity\Product;
use App\Repository\UserRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class AlertMailer
{
    private MailerInterface $mailer;
    private UserRepository $userRepository;

    public function __construct(MailerInterface $mailer, UserRepository $userRepository)
    {
        $this->mailer = $mailer;
        $this->userRepository = $userRepository;
    }

    public function alertProductStockIsTooLow(Product $p)
    {
        $managers = $this->userRepository->findManagers();

        $mail = (new TemplatedEmail())
            ->subject('Alerte de stock')
            ->priority(Email::PRIORITY_HIGH)
            ->htmlTemplate('emails/low_stock.html.twig')
            ->context([
                "name" => $p->getName(),
                "amount" => $p->getAmount()
            ]);

        foreach ($managers as $manager) {
            $mail->addTo($manager->getEmail());
        }

        $this->mailer->send($mail);
    }
}

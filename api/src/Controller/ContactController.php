<?php

namespace App\Controller;

use App\Entity\FormContact;
use App\Repository\TatoueurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    #[Route('/api/contact/send', name: 'api_contact_send', methods: ['POST'])]
    public function sendContact(
        Request $request,
        MailerInterface $mailer,
        TatoueurRepository $tatoueurRepo,
        EntityManagerInterface $em
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        // Récupérer le tatoueur
        $tatoueurId = (int) str_replace('/api/tatoueurs/', '', $data['tatoueur']);
        $tatoueur = $tatoueurRepo->find($tatoueurId);

        if (!$tatoueur) {
            return new JsonResponse(['error' => 'Tatoueur introuvable'], 404);
        }

        // Sauvegarder en BDD
        $formContact = new FormContact();
        $formContact->setNomPrenom($data['nomPrenom']);
        $formContact->setEmail($data['email']);
        $formContact->setTelephone($data['telephone']);
        $formContact->setSujet($data['sujet']);
        $formContact->setMessage($data['message']);
        $formContact->setTatoueur($tatoueur);
        $formContact->setDate(new \DateTimeImmutable());

        $em->persist($formContact);
        $em->flush();

        // Envoyer l'email au tatoueur
        $email = (new Email())
            ->from('noreply@le21tattoo.com')
            ->to($tatoueur->getEmail())
            ->subject('Nouveau message de contact : ' . $data['sujet'])
            ->html("
                <h2>Nouveau message de contact</h2>
                <p><strong>De :</strong> {$data['nomPrenom']}</p>
                <p><strong>Email :</strong> {$data['email']}</p>
                <p><strong>Téléphone :</strong> {$data['telephone']}</p>
                <p><strong>Sujet :</strong> {$data['sujet']}</p>
                <hr>
                <p><strong>Message :</strong></p>
                <p>" . nl2br(htmlspecialchars($data['message'])) . "</p>
            ");

        try {
            $mailer->send($email);
            return new JsonResponse(['success' => true, 'message' => 'Email envoyé']);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}
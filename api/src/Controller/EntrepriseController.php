<?php

// Déclaration du namespace - permet d'organiser les classes
namespace App\Controller;

// Import des classes nécessaires
use App\Entity\Entreprise;                              // Notre entité Entreprise
use Doctrine\ORM\EntityManagerInterface;                // Interface pour accéder à la base de données
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController; // Classe de base des controllers Symfony
use Symfony\Component\HttpFoundation\JsonResponse;      // Classe pour renvoyer des réponses JSON
use Symfony\Component\Routing\Annotation\Route;         // Attribut pour définir les routes
use Symfony\Component\Serializer\SerializerInterface;   // Interface pour transformer des objets en JSON

/**
 * Controller qui gère l'endpoint unique de l'entreprise (singleton)
 * Expose l'URL /api/entreprise en lecture seule (GET)
 */
class EntrepriseController extends AbstractController
{
    /**
     * Constructeur - Symfony injecte automatiquement ces dépendances
     * 
     * @param EntityManagerInterface $em - Permet d'accéder à la base de données
     * @param SerializerInterface $serializer - Permet de convertir l'entité en JSON
     */
    public function __construct(
        private EntityManagerInterface $em,
        private SerializerInterface $serializer
    ) {}

    /**
     * Récupère les informations de l'entreprise
     * 
     * Route: GET /api/entreprise
     * 
     * @return JsonResponse - Les données de l'entreprise au format JSON
     */
    #[Route('/api/entreprise', name: 'api_entreprise', methods: ['GET'])]
    public function get(): JsonResponse
    {
        // 1. Cherche l'entreprise en base de données
        // findOneBy(['singletonKey' => 'X']) = SELECT * FROM entreprise WHERE singleton_key = 'X' LIMIT 1
        $entreprise = $this->em->getRepository(Entreprise::class)
            ->findOneBy(['singletonKey' => 'X']);

        // 2. Si aucune entreprise trouvée, renvoie une erreur 404
        if (!$entreprise) {
            return $this->json(['error' => 'Entreprise not found'], 404);
        }

        // 3. Transforme l'objet Entreprise en JSON
        // Utilise les groupes de serialization définis dans l'entité (#[Groups(['entreprise:read'])])
        // Seuls les champs avec ce groupe seront inclus dans le JSON
        $data = $this->serializer->serialize(
            $entreprise,                          // L'objet à transformer
            'json',                               // Format de sortie
            ['groups' => ['entreprise:read']]     // Groupes de serialization à utiliser
        );

        // 4. Renvoie la réponse JSON
        // new JsonResponse($data, 200, [], true)
        // - $data: le JSON (déjà sérialisé)
        // - 200: code HTTP OK
        // - []: headers supplémentaires (vide ici)
        // - true: indique que $data est déjà du JSON (pas besoin de re-encoder)
        return new JsonResponse($data, 200, [], true);
    }
}
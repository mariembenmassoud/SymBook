<?php

namespace App\Controller;

use App\Repository\CommandeRepository;
use App\Entity\Commande;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class CommandeController extends AbstractController
{
    #[Route('/commande/valider/{id}', name: 'commande_valider', methods: ['GET', 'POST'])]
    public function valider(
        int $id,
        CommandeRepository $commandeRepository,
        Request $request,
        EntityManagerInterface $em,
        SessionInterface $session
    ): Response {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Veuillez vous inscrire ou vous connecter pour valider la commande.');
            $session->set('redirect_commande_id', $id);
            return $this->redirectToRoute('app_login');
        }

        $commande = $commandeRepository->find($id);

        if (!$commande) {
            throw $this->createNotFoundException('Commande non trouvée.');
        }

        if ($request->isMethod('POST')) {
            $modePaiement = $request->request->get('modePaiement');

            if ($modePaiement) {
                $commande->setModePaiement($modePaiement);
                $commande->setStatut('Validée');
                $em->flush();

                $this->addFlash('success', 'Commande validée avec succès !');

                return $this->redirectToRoute('livre_index');
            } else {
                $this->addFlash('error', 'Veuillez choisir un mode de paiement.');
            }
        }

        return $this->render('commande/valider.html.twig', [
            'commande' => $commande,
        ]);
    }

    #[Route('/commande/valider-panier', name: 'commande_valider_panier')]
    public function validerPanier(SessionInterface $session, EntityManagerInterface $em): Response
    {
        $panier = $session->get('panier', []);

        if (empty($panier)) {
            $this->addFlash('warning', 'Votre panier est vide.');
            return $this->redirectToRoute('panier');
        }

        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Veuillez vous connecter pour valider votre panier.');
            return $this->redirectToRoute('app_login');
        }

        $montantTotal = 0;
        foreach ($panier as $livreId => $quantite) {
            $prixUnitaire = 10; // À adapter
            $montantTotal += $prixUnitaire * $quantite;
        }

        $commande = new Commande();
        $commande->setDateCommande(new \DateTime());
        $commande->setMontantTotal($montantTotal);
        $commande->setModePaiement(null);
        $commande->setStatut('En attente');
        $commande->setUtilisateur($user); // Lien avec utilisateur

        $em->persist($commande);
        $em->flush();

        $session->remove('panier');

        return $this->redirectToRoute('commande_valider', ['id' => $commande->getId()]);
    }

    #[Route('/commandes', name: 'commande_index')]
    public function index(CommandeRepository $commandeRepository): Response
    {
        $user = $this->getUser();
        $commandes = $commandeRepository->findBy(['utilisateur' => $user]);

        return $this->render('commande/index.html.twig', [
            'commandes' => $commandes,
        ]);
    }

#[Route('/commande/{id}', name: 'commande_show', requirements: ['id' => '\d+'])]
public function show(Commande $commande): Response
{
    $user = $this->getUser();

    // Vérifie si l'utilisateur connecté est bien le propriétaire de la commande
    if ($commande->getUtilisateur() !== $user) {
        throw $this->createAccessDeniedException("Vous n'avez pas accès à cette commande.");
    }

    return $this->render('commande/show.html.twig', [
        'commande' => $commande,
    ]);
}

}

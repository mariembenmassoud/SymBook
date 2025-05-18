<?php

namespace App\Controller;
use App\Repository\LivreRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class PanierController extends AbstractController
{
    #[Route('/panier/ajouter/{id}', name: 'panier_ajouter', requirements: ['id' => '\d+'])]
    public function ajouter(int $id, SessionInterface $session): RedirectResponse
    {
        // Récupérer le panier en session, ou un tableau vide si inexistant
        $panier = $session->get('panier', []);

        // Ajouter le livre au panier : on stocke la quantité
        if (!empty($panier[$id])) {
            $panier[$id]++;
        } else {
            $panier[$id] = 1;
        }

        // Sauvegarder en session
        $session->set('panier', $panier);

        // Rediriger vers la liste des livres (ou vers la page panier)
        return $this->redirectToRoute('livre_index');
    }
    #[Route('/panier', name: 'panier')]
public function afficher(SessionInterface $session, LivreRepository $livreRepo): Response
{
    $panier = $session->get('panier', []);

    // On récupère les livres correspondant aux IDs du panier
    $livres = $livreRepo->findBy(['id' => array_keys($panier)]);

    return $this->render('panier/index.html.twig', [
        'livres' => $livres,
        'quantites' => $panier,
    ]);
}
#[Route('/panier/supprimer/{id}', name: 'panier_supprimer', requirements: ['id' => '\d+'])]
public function supprimer(int $id, SessionInterface $session): RedirectResponse
{
    $panier = $session->get('panier', []);

    if (isset($panier[$id])) {
        unset($panier[$id]);
    }

    $session->set('panier', $panier);

    return $this->redirectToRoute('panier');
}

#[Route('/panier/modifier/{id}', name: 'panier_modifier', requirements: ['id' => '\d+'], methods: ['POST'])]
public function modifierQuantite(int $id, SessionInterface $session, Request $request): RedirectResponse
{
    $panier = $session->get('panier', []);

    // Récupérer la nouvelle quantité depuis le formulaire (input name='quantite')
    $nouvelleQuantite = (int) $request->request->get('quantite', 1);

    if ($nouvelleQuantite > 0) {
        // Modifier la quantité dans le panier
        $panier[$id] = $nouvelleQuantite;
    } else {
        // Si quantité 0 ou moins, supprimer le livre du panier
        unset($panier[$id]);
    }

    $session->set('panier', $panier);

    return $this->redirectToRoute('panier');
}




}

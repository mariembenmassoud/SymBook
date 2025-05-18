<?php

namespace App\Controller;

use App\Repository\LivreRepository;
use App\Repository\CategorieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LivreController extends AbstractController
{
    #[Route('/livres', name: 'livre_index')]
    public function index(LivreRepository $livreRepo, CategorieRepository $categorieRepo, Request $request): Response
    {
        $search = $request->query->get('search');
        $categorieId = $request->query->get('categorie');
        $categorieId = $categorieId !== null ? (int) $categorieId : null;

        $livres = $livreRepo->findBySearch($search, $categorieId);
        $categories = $categorieRepo->findAll();

        return $this->render('livre/index.html.twig', [
            'livres' => $livres,
            'categories' => $categories,
        ]);
    }

    #[Route('/livre/{id}', name: 'livre_detail', requirements: ['id' => '\d+'])]
    public function detail(int $id, LivreRepository $livreRepo): Response
    {
        $livre = $livreRepo->find($id);

        if (!$livre) {
            throw $this->createNotFoundException('Livre non trouvé.');
        }

        return $this->render('livre/detail.html.twig', [
            'livre' => $livre,
        ]);
    }
}

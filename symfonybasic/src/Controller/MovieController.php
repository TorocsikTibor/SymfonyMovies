<?php

namespace App\Controller;

use App\Entity\Movies;
use App\Form\MovieType;
use App\Repository\MoviesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;

class MovieController extends AbstractController
{
    public function showMovies(EntityManagerInterface $entityManager)
    {
        return $this->render('movies.html.twig', [
            'movies' => $entityManager->getRepository(Movies::class)->findAll()
        ]);
    }

    public function addMovie(Request $request, ManagerRegistry $doctrine, SluggerInterface $slugger)
    {
        $movie = new Movies();
        $form = $this->createForm(MovieType::class, $movie
            , [
            'action' => $this->generateUrl('movie')
        ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {

            $save = $doctrine->getManager();

            $imageFile = $form->get('image')->getData();

            if($imageFile) {
                $originaFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originaFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('image_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    echo "Something wrong here";
                }
                $movie->setImage($newFilename);
            }

            $save->persist($movie);
            $save->flush();

            return $this->redirectToRoute('movies');
        }

        return $this->render('addmovie.html.twig', [
            'movie' => $form->createView()
        ]);
    }

    public function editMovie(int $id, Request $request, ManagerRegistry $doctrine)
    {
        $em = $doctrine->getManager();
        $movie = $em->getRepository(Movies::class)->find($id);

        $form = $this->createForm(MovieType::class, $movie);

        $form->handleRequest($request);

        if($form->isSubmitted() &&$form->isValid()) {
            $em->flush();

            return $this->redirectToRoute('movies');
        }

        return $this->render('editmovie.html.twig', [
            'editmovie' => $form->createView(),
            'id' => $id,
        ]);
    }

    public function deleteMovie(int $id, Request $request, ManagerRegistry $doctrine)
    {
        $em = $doctrine->getManager();
        $movie = $em->getRepository(Movies::class)->find($id);

        $em->remove($movie);
        $em->flush();

        return $this->redirectToRoute('movies', [
            'id' => $movie->getId()
        ]);
    }
}
<?php

namespace App\Controller;

use App\Entity\Movies;
use App\Form\MovieType;
use App\Service\GetMoviedbApiContent;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MovieController extends AbstractController
{
    private const MAX_REQUESTED_MOVIES = 2;
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function showMovies()
    {
        return $this->render('movies.html.twig', [
            'movies' => $this->entityManager->getRepository(Movies::class)->findAll()
        ]);
    }

    public function addMovie(Request $request, SluggerInterface $slugger)
    {
        $movie = new Movies();
        $form = $this->createForm(MovieType::class, $movie
            , [
                'action' => $this->generateUrl('movie')
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $originaFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originaFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

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

            $this->entityManager->persist($movie);
            $this->entityManager->flush();

            return $this->redirectToRoute('movies');
        }

        return $this->render('addmovie.html.twig', [
            'movie' => $form->createView()
        ]);
    }

    public function editMovie(int $id, Request $request)
    {
        $movie = $this->entityManager->getRepository(Movies::class)->find($id);

        $form = $this->createForm(MovieType::class, $movie);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('movies');
        }

        return $this->render('editmovie.html.twig', [
            'editmovie' => $form->createView(),
            'id' => $id,
        ]);
    }

    public function deleteMovie(int $id, Request $request)
    {
        $movie = $this->entityManager->getRepository(Movies::class)->find($id);

        $this->entityManager->remove($movie);
        $this->entityManager->flush();

        return $this->redirectToRoute('movies', [
            'id' => $movie->getId()
        ]);
    }


    // services: request, doctrine (db feltoltes),
    //entity manager kiszervezés private
    public function fetchMovieFromApi (Request $request, GetMoviedbApiContent $getMoviedbApiContent)
    {
        $searchedMovieName = $request->get('searchBar');

        if ($searchedMovieName === "") {
            $this->addFlash('error', 'Searchbar is empty!');
            return $this->redirectToRoute('movies');
        }

        $foundMovie = $this->entityManager->getRepository(Movies::class)->searchMovie($searchedMovieName);

        if (!empty($foundMovie)) {
            return $this->render('movies.html.twig', [
                'movies' => $foundMovie
            ]);
        }

        $decodedMovieContent = $getMoviedbApiContent->getSearchedMovie($searchedMovieName);

        $movieObjects = null;

        foreach ($decodedMovieContent->results as $key => $result) // poster_path, release_date, title, vote_average, vote_count
        {
            $getMoviedbApiContent->savePoster($result->poster_path);

            $existMovie = $this->entityManager->getRepository(Movies::class)->findOneBy(['name' => $result->title, 'releaseDate' => $result->release_date]);

            if ($existMovie !== null) {
                continue;
            }

            $movieObjects[] = Movies::createFromObject($result);
            $this->entityManager->persist($movieObjects[$key]);
            $this->entityManager->flush();

            if ($key === self::MAX_REQUESTED_MOVIES) {
                break;
            }
        }

        return $this->render('movies.html.twig', [
            'movies' => $this->entityManager->getRepository(Movies::class)->findAll()
        ]);

    }
}
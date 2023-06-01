<?php
namespace App\Service;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GetMoviedbApiContent
{
    public function __construct(private HttpClientInterface $client)
    {

    }

    public function getSearchedMovie(string $searchedMovieName): object
    {
        $movieResponse = $this->client->request(
            'GET',
            'https://api.themoviedb.org/3/search/movie?api_key=bbcb1b887ed061963a75585825124fcf&query=' . $searchedMovieName  // api_key=bbcb1b887ed061963a75585825124fcf
        );
        $movieContent = $movieResponse->getContent();
        return json_decode($movieContent);
    }

    public function savePoster($posterPath): bool|int
    {
        $posterPath = ($posterPath !== null) ? ltrim($posterPath, "/") : null;

        if ($posterPath !== null) {
            $movieImage = ltrim($posterPath, "/");
            $imageUrl = 'https://image.tmdb.org/t/p/w500/' . $movieImage;
            $imagePath = 'uploads/images/' . $movieImage;
            return file_put_contents($imagePath, file_get_contents($imageUrl));
        }

        return $posterPath = null;
    }
}
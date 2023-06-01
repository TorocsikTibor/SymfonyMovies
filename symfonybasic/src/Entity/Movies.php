<?php

namespace App\Entity;

use App\Repository\MoviesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MoviesRepository::class)]
class Movies
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $releaseDate = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(nullable: true)]
    private ?float $voteAverage = null;

    #[ORM\Column(nullable: true)]
    private ?int $voteCount = null;

    #[ORM\Column]
    private bool $watched = false;

    /**
     * @param int|null $id
     * @param string|null $name
     * @param string|null $releaseDate
     * @param string|null $image
     * @param float|null $voteAverage
     * @param int|null $voteCount
     * @param bool $watched
     */
    public function __construct(?int $id, ?string $name, ?string $releaseDate, ?string $image, ?float $voteAverage, ?int $voteCount, bool $watched)
    {
        $this->id = $id;
        $this->name = $name;
        $this->releaseDate = $releaseDate;
        $this->image = $image;
        $this->voteAverage = $voteAverage;
        $this->voteCount = $voteCount;
        $this->watched = $watched;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getReleaseDate(): ?string
    {
        return $this->releaseDate;
    }

    public function setReleaseDate(?string $releaseDate): self
    {
        $this->releaseDate = $releaseDate;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function isWatched(): bool
    {
        return $this->watched;
    }

    public function setWatched(bool $watched): self
    {
        $this->watched = $watched;

        return $this;
    }

    public function getVoteAverage(): ?float
    {
        return $this->voteAverage;
    }

    public function setVoteAverage(?float $voteAverage): self
    {
        $this->voteAverage = $voteAverage;

        return $this;
    }

    public function getVoteCount(): ?int
    {
        return $this->voteCount;
    }

    public function setVoteCount(?int $voteCount): self
    {
        $this->voteCount = $voteCount;

        return $this;
    }

    public static function createFromArray(array $data): self
    {
        return new self(
            null,
            $data['title'],
            $data['release_date'],
            $data['poster_path'],
            $data['vote_average'],
            $data['vote_count'],
            false
        );
    }

    public static function createFromObject(object $data): self
    {
        return new self(
            null,
            $data->title,
            $data->release_date,
            $data->poster_path,
            $data->vote_average,
            $data->vote_count,
            false
        );
    }
}

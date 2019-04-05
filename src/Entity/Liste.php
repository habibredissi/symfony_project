<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ListeRepository")
 */
class Liste
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Assert\NotNull
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="listes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $users;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MovieList", mappedBy="listId", orphanRemoval=true)
     */
    private $movieLists;

    public function __construct()
    {
        $this->movieLists = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getUsers(): ?User
    {
        return $this->users;
    }

    public function setUsers(?User $users): self
    {
        $this->users = $users;

        return $this;
    }

    /**
     * @return Collection|MovieList[]
     */
    public function getMovieLists(): Collection
    {
        return $this->movieLists;
    }

    public function addMovieList(MovieList $movieList): self
    {
        if (!$this->movieLists->contains($movieList)) {
            $this->movieLists[] = $movieList;
            $movieList->setListId($this);
        }

        return $this;
    }

    public function removeMovieList(MovieList $movieList): self
    {
        if ($this->movieLists->contains($movieList)) {
            $this->movieLists->removeElement($movieList);
            // set the owning side to null (unless already changed)
            if ($movieList->getListId() === $this) {
                $movieList->setListId(null);
            }
        }

        return $this;
    }
}

<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProductRepository::class)
 */
class Product
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
    private $name;

    /**
     * @ORM\Column(type="text")
     */
    private $info;

    /**
     * @ORM\Column(type="date")
     */
    private $public_date;


    /**
     * @ORM\ManyToMany(targetEntity="Person")
     * @ORM\JoinTable(name="person_like_product",
     *  joinColumns={@ORM\JoinColumn(name="product_id", referencedColumnName="id")},
     *  inverseJoinColumns={@ORM\JoinColumn(name="person_id", referencedColumnName="id")}
     *  )
     */
    private $likes;

    public function __construct() {
        $this->$likes = new ArrayCollection();
    }

    public function addPersonProduct(Person $person) {
        if ($this->$likes->contains($person)) {
            return;
        }
        $this->$likes[] = $person;
    }

    public function getPersonProducts() {
        return $this->$likes;
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

    public function getInfo(): ?string
    {
        return $this->info;
    }

    public function setInfo(string $info): self
    {
        $this->info = $info;

        return $this;
    }

    public function getPublicDate(): ?\DateTimeInterface
    {
        return $this->public_date;
    }

    public function setPublicDate(\DateTimeInterface $public_date): self
    {
        $this->public_date = $public_date;

        return $this;
    }
}

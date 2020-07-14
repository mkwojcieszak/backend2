<?php

namespace App\Entity;

use App\Repository\PersonRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass=PersonRepository::class)
 */
class Person
{

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $login;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $l_name;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $f_name;

    /**
     * @ORM\Column(type="smallint")
     */
    private $state;


    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Product", mappedBy="persons")
     */
    private $products;

    public function __construct()
    {
        $this->products = new ArrayCollection();
        $this->setState(1);
    }

    public function getProducts():  Collection
    {
        return $this->products;
    }

    public function checkLike(Product $product) {
        return $this->products->contains($product);
    }

    public function getLikes() {
        $likes = array();
        forEach($this->products as $product) {
            $like = array();
            $like['personId'] = $this->getId();
            $like['productId'] = $product->getId();
            $like['personLogin'] = $this->getLogin();
            $like['productName'] = $product->getName();
            $likes[] = $like;
        }
        return $likes;
    }

    public function addProduct(Product $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products[] = $product;
            $product->addPerson($this);
        }
        return $this;
    }
    public function removeProduct(Product $product): self
    {
        if ($this->products->contains($product)) {
            $this->products->removeElement($product);
            $product->removePerson($this);
        }
        return $this;
    }






    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setLogin(string $login): self
    {
        $this->login = $login;

        return $this;
    }

    public function getLName(): ?string
    {
        return $this->l_name;
    }

    public function setLName(string $l_name): self
    {
        $this->l_name = $l_name;

        return $this;
    }

    public function getFName(): ?string
    {
        return $this->f_name;
    }

    public function setFName(string $f_name): self
    {
        $this->f_name = $f_name;

        return $this;
    }

    public function getState(): ?int
    {
        return $this->state;
    }

    public function setState(int $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function __toString() {
        return $this->login;
    }
}

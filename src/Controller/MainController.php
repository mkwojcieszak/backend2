<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

use App\Entity\Person;
use App\Entity\Product;
use App\Form\PersonType;
use App\Form\ProductType;
use App\Repository\PersonRepository;
use App\Repository\ProductRepository;

/**
 * @Route("/admin", name="admin.")
 */

class MainController extends AbstractController
{

    const PERSON_STATE_ALL = 0;
    const PERSON_STATE_ACTIVE = 1;
    const PERSON_STATE_BANNED = 2;
    const PERSON_STATE_DELETED = 3;

    /**
     * @Route("/panel", name="panel")
     */
    public function index()
    {
        return $this->render('admin/panel.html.twig');
    }

    /////////////////////////////////////////////////////////////////////////////

    /**
     * @Route("/products/{state}", name="products", defaults={"state":-1})
     */

    public function products(ProductRepository $repo, $state) {
        //States: 0 - before public date, 1 - after public date, -1 - all
        $products = array();

        if ($state == 0) {
            $products = $repo->findPrePublic();
        } else if ($state == 1) {
            $products = $repo->findPostPublic();
        } else {
            $products = $repo->findAll();
        }

        return $this->render('admin/products.html.twig', [
            'products' => $products,
            'state' => $state
        ]);
     }

    /**
     * @Route("/addProduct", name="addProduct")
     */

    public function addProduct(Request $request) {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em-> persist($product);
            $em-> flush();

            return $this->redirect($this->generateUrl('admin.products'));
        }

        return $this->render('admin/form.html.twig', [
            'form' => $form->createView()
        ]);
     }

     /**
     * @Route("/editProduct/{id}", name="editProduct")
     */

    public function editProduct(Request $request, ProductRepository $repo, $id) {
        $product = $repo->find($id);
        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em-> persist($product);
            $em-> flush();

            return $this->redirect($this->generateUrl('admin.products'));
        }

        return $this->render('admin/form.html.twig', [
            'form' => $form->createView()
        ]);
     }

     /**
     * @Route("/deleteProduct/{id}", name="deleteProduct")
     */

    public function deleteProduct(Request $request, ProductRepository $repo, $id, PersonRepository $persRepo) {
        //Deleting a products deletes his likes beforehand.

        $product = $repo->find($id);
        $em = $this->getDoctrine()->getManager();
        $productPersons = $product->getPersons();
        foreach($productPersons as $person) {
            $product->removePerson($person);
        }

        $em-> remove($product);
        $em-> flush();
        return $this->redirect($this->generateUrl('admin.products'));
    }

    /////////////////////////////////////////////////////////////////////////////

    /**
     * @Route("/persons/{state}", name="persons", defaults={"state": 0})
     */
    public function persons(PersonRepository $repo, $state)
    {
        // $this->get('twig')->addGlobal('PERSON_STATE_ALL', $this->getParameter('PERSON_STATE_ALL'));
        // $this->get('twig')->addGlobal('PERSON_STATE_ACTIVE', $this->getParameter('PERSON_STATE_ACTIVE'));
        // $this->get('twig')->addGlobal('PERSON_STATE_BANNED', $this->getParameter('PERSON_STATE_BANNED'));
        // $this->get('twig')->addGlobal('PERSON_STATE_DELETED', $this->getParameter('PERSON_STATE_DELETED'));
        //States: 1 - active, 2 - banned, 3 - deleted, 0 - all
        $persons = array();

        if ($state == MainController::PERSON_STATE_ALL) {
            $persons = $repo->findAll();
        } else {
            $persons = $repo->findBy(array('state' => $state));
        }

        return $this->render('admin/persons.html.twig', [
            'persons' => $persons,
            'state' => $state,
            'controller' => $this
        ]);
    }

    /**
     * @Route("/addPerson", name="addPerson")
     */
    public function addPerson(Request $request) {
        $person = new Person();
        $form = $this->createForm(PersonType::class, $person);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em-> persist($person);
            $em-> flush();

            return $this->redirect($this->generateUrl('admin.persons'));
        }

        return $this->render('admin/form.html.twig', [
            'form' => $form->createView()
        ]);
     }

     /**
     * @Route("/editPerson/{id}", name="editPerson")
     */

    public function editPerson(Request $request, PersonRepository $repo, $id) {
        $person = $repo->find($id);
        $form = $this->createForm(PersonType::class, $person);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em-> persist($person);
            $em-> flush();

            return $this->redirect($this->generateUrl('admin.persons'));
        }

        return $this->render('admin/form.html.twig', [
            'form' => $form->createView()
        ]);
     }

     /**
     * @Route("/setPersonState/{id}/{state}", name="setPersonState")
     */

    public function setPersonState(Request $request, PersonRepository $repo, $id, $state) {
        $person = $repo->find($id);
        $person->setState($state);
        $em = $this->getDoctrine()->getManager();
        $em-> persist($person);
        $em-> flush();

        return $this->redirect($this->generateUrl('admin.persons'));
     }

}

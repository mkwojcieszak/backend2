<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

use App\Entity\Person;
use App\Entity\Product;
use App\Repository\PersonRepository;
use App\Repository\ProductRepository;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

/**
 * @Route("/admin", name="admin.")
 */

class LikeController extends AbstractController
{
    /**
     * @Route("/likes/{productName}/{personLogin}", name="likes", defaults={"productName":-1, "personLogin":-1})
     */

    public function likes(Request $request, ProductRepository $productRepo, PersonRepository $personRepo, $productName, $personLogin) {
        /*
            Twig page displays text inputs and select fields for Product and Person.

            If personLogin input was not empty, fill Select field with persons whos logins contain that value (substring)
            If productName input was not empty, fill Select field with products which contain that value in their names (substring)

            If personLogin input was not empty and is found in database, show products he likes
            If productName input was not empty and is found in database, show persons he is liked by

        */

        $product = null;
        $person = null;
        $selectProducts = array();
        $selectPersons = array();
        $resultLikes = array();

        // *Empty input was temporarily set to -1 to generate valid route
        if ($productName == -1) { $productName = ""; }
        if ($personLogin == -1) { $personLogin = ""; }

        if ($productName != "") {
            $selectProducts = $productRepo->findByNameSubstring($productName);
            $productarr = $productRepo->findBy(array('name' => $productName));
            if (count($productarr) > 0) { $product = $productarr['0']; }
        }

        if ($personLogin != "") {
            $selectPersons = $personRepo->findByLoginSubstring($personLogin);
            $personarr = $personRepo->findBy(array('login' => $personLogin));
            if (count($personarr) > 0) { $person = $personarr['0']; }
        }

        // If Product or Person was found, generate search results

        if ($product != null && $person == null) {
            $resultLikes = $product->getLikes();
        }else if ($product == null && $person != null) {
            $resultLikes = $person->getLikes();
        } else if ($product != null && $person != null) {
            if ($product->checkLike($person)) {
                $resultLikes[] = true;
            }
        }
        

        // Create form for finding Products and Persons by substring of their name/login

        $form = $this->createFormBuilder()
            ->add('productName', TextType::class, [
                'required' => false,
                'data' => $productName,
                'empty_data' => '',
                'attr' => [ 'class' => 'product-input' ] // class used for javascript @ select Button onchange event
            ])
            ->add('personLogin', TextType::class, [
                'required' => false,
                'data' => $personLogin,
                'empty_data' => '',
                'attr' => [ 'class' => 'person-input' ] //  class used for javascript @ select Button onchange event
            ])
            ->add('search', SubmitType::class, [
                'label' => "Search",
                'attr' => ['class' => 'btn btn-success']
            ])
            ->getForm()
        ;

        $form->handleRequest($request);

        if($form->isSubmitted()) {
            $data = $form->getData();
            $productString = $data['productName'];
            $personString = $data['personLogin'];
            if ($productString == "") { $productString = -1; }
            if ($personString == "") { $personString = -1; }

            return $this->redirect($this->generateUrl('admin.likes', [
                'productName' => $productString,
                'personLogin' => $personString
            ]));
        }

        
        return $this->render('admin/likes.html.twig', [
            'productName' => $productName,
            'personLogin' => $personLogin,
            'selectProducts' => $selectProducts,
            'selectPersons' => $selectPersons,
            'product' => $product,
            'person' => $person,
            'resultLikes' => $resultLikes,
            'form' => $form->createView()
        ]);
     }

     /**
     * @Route("/addLike/{productId}/{personId}", name="addLike", defaults={"productId":-1, "personId":-1})
     */

    public function addLike(Request $request, ProductRepository $productRepo, PersonRepository $personRepo, $productId, $personId) {
        if ($productId == -1 || $personId == -1) {
            $this->addFlash('failure', 'Missing Data.');
            return $this->redirect($this->generateUrl('admin.likes'));
        } else {
            $product = $productRepo->find($productId);
            $person = $personRepo->find($personId);
            $productPersons = $product->getPersons();

            forEach($productPersons as $pp) {
                if ($pp == $person) {
                    $this->addFlash('failure', 'Like Already Exists.');
                    return $this->redirect($this->generateUrl('admin.likes'));
                }
            }

            $product->addPerson($person);
            $em = $this->getDoctrine()->getManager();
            $em->persist($product);
            $em->flush();

            $this->addFlash('success', 'Like added successfully.');
            return $this->redirect($this->generateUrl('admin.likes', [
                'productName' => $product->getName(),
                'personLogin' => $person->getLogin()
            ]));
        }
    }

    /**
     * @Route("/deleteLike/{productId}/{personId}", name="deleteLike", defaults={"productId":-1, "personId":-1})
     */

    public function deleteLike(Request $request, ProductRepository $productRepo, PersonRepository $personRepo, $productId, $personId) {
        if ($productId == -1 || $personId == -1) {
            $this->addFlash('failure', 'Missing Data.');
            return $this->redirect($this->generateUrl('admin.likes'));
        } else {
            $product = $productRepo->find($productId);
            $person = $personRepo->find($personId);

            $person->removeProduct($product);
            $em = $this->getDoctrine()->getManager();
            $em->persist($product);
            $em->flush();

            $this->addFlash('success', 'Like removed.');
            return $this->redirect($this->generateUrl('admin.likes', [
                'productName' => $product->getName(),
                'personLogin' => $person->getLogin()
            ]));
        }
    }

    /**
     * @Route("/editLikeForm/{oldProductId}/{oldPersonId}/{productName}/{personLogin}", name="editLikeForm",
     * defaults={"oldProductId":-1, "oldPersonId":-1, "productName":-1, "personLogin":-1})
     */

    public function editLikeForm(Request $request, ProductRepository $productRepo, PersonRepository $personRepo,
    $oldProductId, $oldPersonId, $productName, $personLogin) {

        if ($oldProductId == -1 || $oldPersonId == -1) {
            return $this->redirect($this->generateUrl('admin.likes'));
        }

        // *Empty input was temporarily set to -1 to generate valid route
        if ($productName == -1) { $productName = ""; }
        if ($personLogin == -1) { $personLogin = ""; }

        $oldProduct = $productRepo->find($oldProductId);
        $oldPerson = $personRepo->find($oldPersonId);

        if ($oldProduct == null || $oldPerson == null) {
            $this->addFlash('failure', 'Product od Person not found.');
            return $this->redirect($this->generateUrl('admin.likes'));
        }

        $product = null;
        $person = null;
        $selectProducts = array();
        $selectPersons = array();

        if ($productName != "") {
            $selectProducts = $productRepo->findByNameSubstring($productName);
            $productarr = $productRepo->findBy(array('name' => $productName));
            if (count($productarr) > 0) { $product = $productarr['0']; }
        }

        if ($personLogin != "") {
            $selectPersons = $personRepo->findByLoginSubstring($personLogin);
            $personarr = $personRepo->findBy(array('login' => $personLogin));
            if (count($personarr) > 0) { $person = $personarr['0']; }
        }

        // If new Product or Person was found, find if new Like already exists
        $likeAlreadyExists = false;

        if ($product != null && $person == null) {
            $likeAlreadyExists = $product->checkLike($oldPerson);
            $person = $oldPerson;
        } else if ($product == null && $person != null) {
            $likeAlreadyExists = $person->checkLike($oldProduct);
            $product = $oldProduct;
        } else if ($product != null && $person != null) {
            $likeAlreadyExists = $product->checkLike($person);
        } else {
            $person = $oldPerson;
            $product = $oldProduct;
            $likeAlreadyExists = true;
        }


        // Create form for finding Products and Persons by substring of their name/login

        $form = $this->createFormBuilder()
            ->add('productName', TextType::class, [
                'required' => false,
                'data' => $productName,
                'empty_data' => '',
                'attr' => [ 'class' => 'product-input' ] // for javascript @ select Button onchange event
            ])
            ->add('personLogin', TextType::class, [
                'required' => false,
                'data' => $personLogin,
                'empty_data' => '',
                'attr' => [ 'class' => 'person-input' ] // for javascript @ select Button onchange event
            ])
            ->add('search', SubmitType::class, [
                'label' => "Search",
                'attr' => ['class' => 'btn btn-success']
            ])
            ->getForm()
        ;

        $form->handleRequest($request);

        if($form->isSubmitted()) {
            $data = $form->getData();
            $productString = $data['productName'];
            $personString = $data['personLogin'];
            if ($productString == "") { $productString = -1; }
            if ($personString == "") { $personString = -1; }

            return $this->redirect($this->generateUrl('admin.editLikeForm', [
                'oldProductId' => $oldProductId,
                'oldPersonId' => $oldPersonId,
                'productName' => $productString,
                'personLogin' => $personString
            ]));
        }


        return $this->render('admin/editLike.html.twig', [
            'oldProduct' => $oldProduct,
            'oldPerson' => $oldPerson,
            'productName' => $productName,
            'personLogin' => $personLogin,
            'selectProducts' => $selectProducts,
            'selectPersons' => $selectPersons,
            'product' => $product,
            'person' => $person,
            'likeAlreadyExists' => $likeAlreadyExists,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/editLike/{oldProductId}/{oldPersonId}/{newProductId}/{newPersonId}", name="editLike",
     * defaults={"oldProductId":-1, "oldPersonId":-1, "newProductId":-1, "newPersonId":-1})
     */

    public function editLike(Request $request, ProductRepository $productRepo, PersonRepository $personRepo,
    $oldProductId, $oldPersonId, $newProductId, $newPersonId) {

        if ($oldProductId == -1 || $oldPersonId == -1 || $newProductId == -1 || $newPersonId == -1) {
            $this->addFlash('failure', 'Missing Data.');
            return $this->redirect($this->generateUrl('admin.likes'));
        }

        $oldProduct = $productRepo->find($oldProductId);
        $oldPerson = $personRepo->find($oldPersonId);
        $newProduct = $productRepo->find($newProductId);
        $newPerson = $personRepo->find($newPersonId);

        if ($oldProduct == null || $oldPerson == null || $newProduct == null || $newPerson == null) {
            $this->addFlash('failure', 'Product or Person not found.');
            return $this->redirect($this->generateUrl('admin.likes'));
        }

        if ($newProduct->checkLike($newPerson)) {
            $this->addFlash('failure', 'Like already exists.');
            return $this->redirect($this->generateUrl('admin.likes'));
        }

        $oldPerson->removeProduct($oldProduct);
        $newPerson->addProduct($newProduct);

        $em = $this->getDoctrine()->getManager();
        $em->persist($oldProduct);
        $em->persist($newProduct);
        $em->flush();
        $this->addFlash('success', 'Like changed successfully.');

        return $this->redirect($this->generateUrl('admin.editLikeForm', [
            'oldProductId' => $newProductId,
            'oldPersonId' => $newPersonId
        ]));

    }
}

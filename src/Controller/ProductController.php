<?php
namespace App\Controller;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class ProductController extends AbstractController
{
    // =======================
    // CREATE / ADD PRODUCT
    // =======================
   #[Route('/product/add', name: 'product_add', methods: ['POST'])]
public function add(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
{
    $token = $request->request->get('_token');
    if (!$this->isCsrfTokenValid('product_form', $token)) {
        $this->addFlash('error', 'Invalid form submission.');
        return $this->redirectToRoute('home');
    }

    $name = trim($request->request->get('name', ''));
    $description = trim($request->request->get('description', ''));
    $price = $request->request->get('price', '0');

    if ($name === '') {
        $this->addFlash('error', 'Name is required.');
        return $this->redirectToRoute('home');
    }

    $product = new Product();
    $product->setName($name);
    $product->setDescription($description);
    $product->setPrice($price);

    // ========= FILE UPLOAD (no fileinfo needed) =========
    $imageFile = $request->files->get('image');
    if ($imageFile) {
        $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $slugger->slug($originalFilename);
        $extension = pathinfo($imageFile->getClientOriginalName(), PATHINFO_EXTENSION);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $extension;

        try {
            $uploadDir = $this->getParameter('product_images_directory');
            $imageFile->move($uploadDir, $newFilename);
            $product->setImage('uploads/products/' . $newFilename);
        } catch (FileException $e) {
            $this->addFlash('error', 'Failed to upload image.');
            return $this->redirectToRoute('home');
        }
    } else {
        // No image uploaded → use fallback
        $product->setImage('images/fallback.png');
    }

    $em->persist($product);
    $em->flush();

    $this->addFlash('success', 'Product added.');
    return $this->redirectToRoute('home');
}

    // =======================
    // EDIT / UPDATE PRODUCT
    // =======================
   #[Route('/product/{id}/edit', name: 'product_edit', methods: ['GET','POST'])]
public function edit(Product $product, Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
{
    if ($request->isMethod('POST')) {
        $name = trim($request->request->get('name', ''));
        $description = trim($request->request->get('description', ''));
        $price = $request->request->get('price', '0');

        if ($name === '') {
            $this->addFlash('error', 'Name is required.');
            return $this->redirectToRoute('product_edit', ['id' => $product->getId()]);
        }

        $product->setName($name);
        $product->setDescription($description);
        $product->setPrice($price);

        // ========= FILE UPLOAD (no fileinfo needed) =========
        $imageFile = $request->files->get('image');
        if ($imageFile) {
            $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $extension = pathinfo($imageFile->getClientOriginalName(), PATHINFO_EXTENSION);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $extension;

            try {
                $uploadDir = $this->getParameter('product_images_directory');
                $imageFile->move($uploadDir, $newFilename);
                $product->setImage('uploads/products/' . $newFilename);
            } catch (FileException $e) {
                $this->addFlash('error', 'Failed to upload new image.');
                return $this->redirectToRoute('product_edit', ['id' => $product->getId()]);
            }
        }
        // If no new image uploaded → keep the existing one

        $em->flush();
        $this->addFlash('success', 'Product updated successfully!');
        return $this->redirectToRoute('home');
    }

    return $this->render('product/edit.html.twig', ['product' => $product]);
}


    // =======================
    // DELETE PRODUCT
    // =======================
    #[Route('/product/{id}/delete', name: 'product_delete', methods: ['POST'])]
    public function delete(Product $product, Request $request, EntityManagerInterface $em): Response
    {
        $token = $request->request->get('_token');
        if ($this->isCsrfTokenValid('delete' . $product->getId(), $token)) {
            $em->remove($product);
            $em->flush();
            $this->addFlash('success', 'Product deleted.');
        } else {
            $this->addFlash('error', 'Invalid CSRF token.');
        }

        return $this->redirectToRoute('home');
    }
}

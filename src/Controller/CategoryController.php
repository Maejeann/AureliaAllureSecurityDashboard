<?php
namespace App\Controller;

use App\Entity\Category;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    // GET: Display categories management page
    #[Route('/admin/categories', name: 'admin_categories', methods: ['GET'])]
    public function list(EntityManagerInterface $em): Response
    {
        $products = $em->getRepository(Product::class)->findAll();
        $categories = $em->getRepository(Category::class)->findAll();
        return $this->render('admin/categories.html.twig', [
            'products' => $products,
            'categories' => $categories
        ]);
    }

    #[Route('/category/{id}/delete', name: 'category_delete', methods: ['POST'])]
public function delete(Product $product, Request $request, EntityManagerInterface $em)
{
    $token = $request->request->get('_token');
    if (!$this->isCsrfTokenValid('delete_category' . $product->getId(), $token)) {
        $this->addFlash('error', 'Invalid CSRF token.');
        return $this->redirectToRoute('admin_categories');
    }
    // Remove category assignment (does not delete the product)
    $product->setCategory(null);
    $em->flush();

    $this->addFlash('success', 'Category removed from product.');
    return $this->redirectToRoute('admin_categories');
}


    // POST: Assign category to product
    #[Route('/category/add', name: 'category_add', methods: ['POST'])]
    public function add(Request $request, EntityManagerInterface $em): Response
    {
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('category_form', $token)) {
            $this->addFlash('error', 'Invalid form submission.');
            return $this->redirectToRoute('admin_categories');
        }

        $productId = $request->request->get('product');
        $categoryName = $request->request->get('category');

        $product = $em->getRepository(Product::class)->find($productId);
        if (!$product) {
            $this->addFlash('error', 'Product not found.');
            return $this->redirectToRoute('admin_categories');
        }

        $category = $em->getRepository(Category::class)->findOneBy(['name' => $categoryName]);
        if (!$category) {
            $category = new Category();
            $category->setName($categoryName);
            $em->persist($category);
        }

        $product->setCategory($category);
        $em->flush();

        $this->addFlash('success', 'Category assigned to product.');
        return $this->redirectToRoute('admin_categories');
    }
}

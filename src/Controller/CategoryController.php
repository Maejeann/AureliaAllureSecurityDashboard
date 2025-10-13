<?php
namespace App\Controller;

use App\Entity\Category;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    #[Route('/category/add', name: 'category_add', methods: ['POST'])]
    public function add(Request $request, EntityManagerInterface $em)
    {
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('category_form', $token)) {
            $this->addFlash('error', 'Invalid form submission.');
            return $this->redirectToRoute('home');
        }

        $productId = $request->request->get('product');
        $categoryName = $request->request->get('category');

        $product = $em->getRepository(Product::class)->find($productId);
        if (!$product) {
            $this->addFlash('error', 'Product not found.');
            return $this->redirectToRoute('home');
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
        return $this->redirectToRoute('home');
    }
}

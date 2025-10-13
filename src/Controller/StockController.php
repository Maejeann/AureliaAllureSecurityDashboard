<?php
namespace App\Controller;

use App\Entity\Stock;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class StockController extends AbstractController
{
    #[Route('/stock/add', name: 'stock_add', methods: ['POST'])]
    public function add(Request $request, EntityManagerInterface $em)
    {
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('stock_form', $token)) {
            $this->addFlash('error', 'Invalid form submission.');
            return $this->redirectToRoute('home');
        }

        $productId = $request->request->get('product');
        $quantity = (int)$request->request->get('quantity');

        $product = $em->getRepository(Product::class)->find($productId);
        if (!$product) {
            $this->addFlash('error', 'Product not found.');
            return $this->redirectToRoute('home');
        }

        $stock = new Stock();
        $stock->setProduct($product);
        $stock->setQuantity($quantity);

        $em->persist($stock);
        $em->flush();

        $this->addFlash('success', 'Stock updated.');
        return $this->redirectToRoute('home');
    }
}

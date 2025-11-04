<?php
namespace App\Controller;

use App\Entity\Stock;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StockController extends AbstractController
{
    #[Route('/admin/stocks', name: 'admin_stocks', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        $products = $em->getRepository(Product::class)->findAll();
        return $this->render('admin/stocks.html.twig', [
            'products' => $products
        ]);
    }
    // POST: Add/Update stock
    #[Route('/stock/add', name: 'stock_add', methods: ['POST'])]
    public function add(Request $request, EntityManagerInterface $em): Response
    {
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('stock_form', $token)) {
            $this->addFlash('error', 'Invalid form submission.');
            return $this->redirectToRoute('admin_stocks');
        }

        $productId = $request->request->get('product');
        $quantity = (int)$request->request->get('quantity');

        $product = $em->getRepository(Product::class)->find($productId);
        if (!$product) {
            $this->addFlash('error', 'Product not found.');
            return $this->redirectToRoute('admin_stocks');
        }

        $stock = new Stock();
        $stock->setProduct($product);
        $stock->setQuantity($quantity);

        $em->persist($stock);
        $em->flush();

        $this->addFlash('success', 'Stock updated.');
        return $this->redirectToRoute('admin_stocks');
    }

    // POST: Delete (remove) a stock entry
    #[Route('/stock/{id}/delete', name: 'stock_delete', methods: ['POST'])]
    public function delete(Stock $stock, Request $request, EntityManagerInterface $em): Response
    {
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete_stock' . $stock->getId(), $token)) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('admin_stocks');
        }
        $em->remove($stock);
        $em->flush();
        $this->addFlash('success', 'Stock entry deleted.');
        return $this->redirectToRoute('admin_stocks');
    }
}

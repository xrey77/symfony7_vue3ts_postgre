<?php

namespace App\Controller;

use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator; // You might use Pagerfanta's adapter instead
use Pagerfanta\Adapter\DoctrineORMAdapter; 
use Pagerfanta\Pagerfanta;
use Pagerfanta\Doctrine\ORM\QueryAdapter;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use DateTimeImmutable;


final class ProductController extends AbstractController
{
    #[Route('/api/addproduct', name: 'app_addproduct', methods: ['POST'])]
    public function addProduct(
        Request $request,
        EntityManagerInterface $entityManager,
        ProductRepository $productRepository        
    ): JsonResponse
    {
        $data = json_decode($request->getContent());

        $description = $entityManager->getRepository(Product::class)->findOneBy(['descriptions' => $data->descriptions]);
        if ($description) {
            return $this->json(['message' => 'Product Descriptions is already taken.'], 404);
        }
        $product = new Product();
        $product->setCategory($data->category);
        $product->setDescriptions($data->descriptions);
        $product->setQty($data->qty);
        $product->setUnit($data->unit);
        $product->setCostprice($data->costprice);
        $product->setSellprice($data->sellprice);
        $product->setSaleprice($data->saleprice);
        $product->setProductpicture($data->productpicture);
        $product->setAlertstocks($data->alertstocks);
        $product->setCriticalstocks($data->criticalstocks);
        $product->setCreatedAtValue();
        $entityManager->persist($product);            
        $entityManager->flush();
        return new JsonResponse(['message' => 'New Product successfully added.'], 201);
    }

    #[Route('/api/updateproduct/{id}', name: 'app_updateproduct', methods: ['PATCH'])]
    public function updateProduct(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        ProductRepository $productRepository        
    ): JsonResponse
    {
        $data = json_decode($request->getContent());

        $prodId = $entityManager->getRepository(Product::class)->findOneBy(['id' => $id]);
        if ($prodId) {
            $product = new Product();
            $product->setCategory($data->category);
            $product->setDescriptions($data->descriptions);
            $product->setQty($data->qty);
            $product->setUnit($data->unit);
            $product->setCostprice($data->costprice);
            $product->setSellprice($data->sellprice);
            $product->setSaleprice($data->saleprice);
            $product->setProductpicture($data->productpicture);
            $product->setAlertstocks($data->alertstocks);
            $product->setCriticalstocks($data->criticalstocks);
            $product->setUpdatedAt(new DateTimeImmutable()); 
            $product->setUpdatedAtValue();
            $entityManager->persist($product);            
            $entityManager->flush();
            return new JsonResponse(['message' => 'New Product successfully added.'], 201);

        } else {
            return $this->json(['message' => 'Product not found.'], 404);
        }
    }

    #[Route('/api/productlist/{page}', name: 'app_productlist', methods: ['GET'])]
    public function getProducts(
        int $page,
        EntityManagerInterface $em,
        SerializerInterface $serializer,
        ProductRepository $productRepository): Response
    {
        $perPage = 5;
        $offset = ($page - 1) * $perPage;
        // if ($offset === 0) {
        //     $offset = 1;
        // }
        // $query = $em->createQuery(
        //     'SELECT p.id,p.category,p.descriptions,p.qty,p.unit,p.costprice,p.sellprice,p.saleprice,p.productpicture,p.alertstocks,p.criticalstocks FROM App\\Entity\\Product p WHERE p.id = :id'
        // )->setFirstResult($offset)->setMaxResults($perPage);


        // $query = $em->getRepository(Product::class)->findBy([], ['createdAt' => 'DESC'], $perPage, $offset);
        // $products = $productRepository->findBy([], ['createdAt' => 'DESC'], $perPage, $offset);

        // $products = $productRepository->findByPagination($perPage, $offset);


        $query = $em->getRepository(Product::class)
            ->createQueryBuilder('p')
            ->orderBy('p.id', 'ASC')
            ->getQuery();

        // Use the Paginator
        $paginator = new Paginator($query);
        $paginator
            ->getQuery()
            ->setFirstResult($offset)
            ->setMaxResults($perPage);

        // Get the items for the current page
        $items = iterator_to_array($paginator->getIterator());
        
        // Get total number of items
        $totalItems = count($paginator); 
        $totpage = ceil($totalItems / $perPage);
        $totalpage = (int)$totpage;
        // Prepare the data for JSON response
        $data = [
            'totalrecs' => $totalItems,
            'totpage' => $totalpage,
            'page' => $offset == 0 ? 1 : $page,
            'products' => $items,
        ];
        $jsonContent = $serializer->serialize($data, 'json');

        return new Response(
            $jsonContent,
            Response::HTTP_OK,
            ['content-type' => 'application/json']
        );        
    }




    #[Route('/api/getproductid/{id}', name: 'app_getproductid', methods: ['GET'])]
    public function getProductId(int $id, EntityManagerInterface $em): JsonResponse
    {
        $query = $em->createQuery(
            'SELECT p.id,p.category,p.descriptions,p.qty,p.unit,p.costprice,p.sellprice,p.saleprice,p.productpicture,p.alertstocks,p.criticalstocks FROM App\\Entity\\Product p WHERE p.id = :id'
        )->setParameter('id', $id);        
        if ($query) {
            return new JsonResponse($query->getResult(),200);
        } else {
            return $this->json(['message' => 'Product not found.'], 404);
        }
    }

    //SEARCH WILD CARD USING createQueryBuilder
    #[Route('/api/productsearch/{key}', name: 'app_productsearch', methods: ['GET'])]
    public function getSearchProduct(
        string $key, EntityManagerInterface $em,
        SerializerInterface $serializer,
        ProductRepository $productRepository
        ): Response
    {
        $search = '%' . strtolower($key) . '%';
        $qb = $em->getRepository(Product::class)->createQueryBuilder('p');        
        $qb->where($qb->expr()->like('LOWER(p.descriptions)', ':keyword'))
           ->setParameter('keyword', $search)
           ->orderBy('p.descriptions', 'ASC'); 

        $jsonContent = $serializer->serialize($qb->getQuery()->getResult(), 'json');
        
        if ($jsonContent === '[]') { // || $jsonContent === '{}' || $jsonContent === '') {
            return new JsonResponse(['message' => 'No Data Found..'],404);
        } else {
            return new Response(
                $jsonContent,
                Response::HTTP_OK,
                ['content-type' => 'application/json']
            );        

        }
    }

    //SEARCH WILD CARD USING EntityManagerInterface
    // #[Route('/api/productlist2/{key}', name: 'app_productlist', methods: ['GET'])]
    // public function getProductList(string $key,EntityManagerInterface $em): Response
    // {
    //     $search = '%' . $key . '%';
    //     $query = $em->createQuery(
    //         'SELECT p.id,p.category,p.descriptions,p.qty,p.unit,p.costprice,p.sellprice,p.saleprice,p.productpicture,p.alertstocks,p.criticalstocks
    //         FROM App\Entity\Product p WHERE p.descriptions LIKE :keyword ORDER BY p.id')->setParameter('keyword', $search);        
    //     return new JsonResponse($query->getResult());
    // }


}

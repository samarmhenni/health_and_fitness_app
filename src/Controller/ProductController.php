<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

class ProductController extends AbstractController
{
    #[Route('/products', name: 'app_products')]
    public function index(ProductRepository $repo): Response
    {
        $products = $repo->findAll();

        return $this->render('product/index.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/product/{id}', name: 'app_product_show', requirements: ['id' => '\\d+'])]
    public function show($id, ProductRepository $repo): Response
    {
        $id = (int) $id;
        $product = $repo->find($id);

        if (!$product) {
            throw $this->createNotFoundException('Produit non trouvé');
        }

        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/product/new', name: 'app_product_new')]
    #[IsGranted('ROLE_ADMIN', message: 'Accès réservé aux administrateurs')]
    public function new(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile|null $imageFile */
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                $targetDir = $this->getParameter('kernel.project_dir').'/public/uploads/products';
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0775, true);
                }

                $imageFile->move($targetDir, $newFilename);

                $product->setImage('/uploads/products/'.$newFilename);
            }

            $em->persist($product);
            $em->flush();

            return $this->redirectToRoute('app_products');
        }

        return $this->render('product/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/product/{id}/edit', name: 'app_product_edit', requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_ADMIN', message: 'Accès réservé aux administrateurs')]
    public function edit(int $id, Request $request, ProductRepository $repo, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $product = $repo->find($id);

        if (!$product) {
            throw $this->createNotFoundException('Produit non trouvé');
        }

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile|null $imageFile */
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                // Delete old image if exists
                $oldImage = $product->getImage();
                if ($oldImage) {
                    $oldImagePath = $this->getParameter('kernel.project_dir').'/public'.$oldImage;
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }

                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                $targetDir = $this->getParameter('kernel.project_dir').'/public/uploads/products';
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0775, true);
                }

                $imageFile->move($targetDir, $newFilename);
                $product->setImage('/uploads/products/'.$newFilename);
            }

            $em->flush();

            $this->addFlash('success', 'Produit modifié avec succès');
            return $this->redirectToRoute('app_product_show', ['id' => $product->getId()]);
        }

        return $this->render('product/edit.html.twig', [
            'form' => $form->createView(),
            'product' => $product,
        ]);
    }

    #[Route('/product/{id}/delete', name: 'app_product_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Accès réservé aux administrateurs')]
    public function delete(int $id, Request $request, ProductRepository $repo, EntityManagerInterface $em): Response
    {
        $product = $repo->find($id);

        if (!$product) {
            throw $this->createNotFoundException('Produit non trouvé');
        }

        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            // Delete image file if exists
            $image = $product->getImage();
            if ($image) {
                $imagePath = $this->getParameter('kernel.project_dir').'/public'.$image;
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            $em->remove($product);
            $em->flush();

            $this->addFlash('success', 'Produit supprimé avec succès');
        }

        return $this->redirectToRoute('app_products');
    }
}
<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoriesRepository;

use App\Utils\Slugger;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller qui permet la gestion des categories
 * Class CategoriesController
 * @package App\Controller\Admin
 * @Route("/admin/categories")
 * @IsGranted("ROLE_ADMIN")
 */
class CategoriesController extends AbstractController
{
    /**
     * @param CategoriesRepository $categoriesRepository
     * @return Response
     * @Route("/", methods={"GET"}, name="admin_categories_index")
     */
    public function index(CategoriesRepository $categoriesRepository): Response{
        $categories = $categoriesRepository->findAll();
        return $this->render('admin/blog/category/index.html.twig', ['categories' => $categories]);
    }


    /**
     * Creates a new Post entity.
     *
     * @Route("/new", methods={"GET", "POST"}, name="admin_category_new")
     *
     * NOTE: the Method annotation is optional, but it's a recommended practice
     * to constraint the HTTP methods each controller responds to (by default
     * it responds to all methods).
     * @param Request $request
     * @return Response
     */
    public function new (Request $request): Response{
        $category = new Category();
        $form = $this->createForm(CategoryType::class , $category);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($category);
            $em->flush();

            // Flash messages are used to notify the user about the result of the
            // actions. They are deleted automatically from the session as soon
            // as they are accessed.
            // See https://symfony.com/doc/current/book/controller.html#flash-messages
            $this->addFlash('success', 'category.created_successfully');

            return $this->redirectToRoute('admin_categories_index');
        }
        return $this->render('admin/blog/category/new.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Displays a form to edit an existing Post entity.
     *
     * @Route("/{id<\d+>}/edit",methods={"GET", "POST"}, name="admin_category_edit")
     * @param Request $request
     * @param Category $categories
     * @return Response
     */
    public function edit(Request $request, Category $categories): Response
    {
        $form = $this->createForm(CategoryType::class, $categories);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $categories->setSlug(Slugger::slugify($categories->getName()));
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'post.updated_successfully');

            return $this->redirectToRoute('admin_categories_index');
        }

        return $this->render('admin/blog/category/edit.html.twig', [
            'category' => $categories,
            'form' => $form->createView(),
        ]);
    }

}
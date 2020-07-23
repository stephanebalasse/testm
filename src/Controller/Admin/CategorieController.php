<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Admin;


use App\Security\PostVoter;
use App\Utils\Slugger;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Categorie;
use App\Form\CategorieType;

use App\Repository\CategorieRepository;

/**

 * @Route("/admin/categorie")
 * @IsGranted("ROLE_ADMIN")
 *

 */
class CategorieController extends AbstractController
{
    /**
     * Lists all Post entities.
     *
     * This controller responds to two different routes with the same URL:
     *   * 'admin_post_index' is the route with a name that follows the same
     *     structure as the rest of the controllers of this class.
     *   * 'admin_index' is a nice shortcut to the backend homepage. This allows
     *     to create simpler links in the templates. Moreover, in the future we
     *     could move this annotation to any other controller while maintaining
     *     the route name and therefore, without breaking any existing link.
     *
     * @Route("/", methods={"GET"}, name="admin_categorie_index")
     */
    public function index(CategorieRepository $catRepo): Response
    {
        $categories = $catRepo->findBy([], ['name' => 'ASC']);

        return $this->render('admin/categories/index.html.twig', ['categories' => $categories]);
    }

    /**
     * Creates a new Categorie entity.
     *
     * @Route("/new", methods={"GET", "POST"}, name="admin_categorie_new")
     *
     * NOTE: the Method annotation is optional, but it's a recommended practice
     * to constraint the HTTP methods each controller responds to (by default
     * it responds to all methods).
     */
    function new (Request $request): Response {

        $categorie = new Categorie();
        // See https://symfony.com/doc/current/book/forms.html#submitting-forms-with-multiple-buttons
        $form = $this->createForm(CategorieType::class, $categorie)
            ->add('saveAndCreateNew', SubmitType::class);

        $form->handleRequest($request);

        // the isSubmitted() method is completely optional because the other
        // isValid() method already checks whether the form is submitted.
        // However, we explicitly add it to improve code readability.
        // See https://symfony.com/doc/current/best_practices/forms.html#handling-form-submits
        if ($form->isSubmitted() && $form->isValid()) {
            $categorie->setSlug(Slugger::slugify($categorie->getName()));
            $categorie->setDateAdd(new  \DateTime());
            $em = $this->getDoctrine()->getManager();
            $em->persist($categorie);
            $em->flush();

            // Flash messages are used to notify the user about the result of the
            // actions. They are deleted automatically from the session as soon
            // as they are accessed.
            // See https://symfony.com/doc/current/book/controller.html#flash-messages
            $this->addFlash('success', 'categorie.created_successfully');

            if ($form->get('saveAndCreateNew')->isClicked()) {
                return $this->redirectToRoute('admin_categorie_new');
            }

            return $this->redirectToRoute('admin_categorie_index');
        }

        return $this->render('admin/categories/new.html.twig', [
            'categorie' => $categorie,
            'form' => $form->createView(),
        ]);
    }

 

}

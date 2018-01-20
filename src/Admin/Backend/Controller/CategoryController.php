<?php

namespace Admin\Backend\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Admin\Backend\Entity\Category;
use Admin\Backend\Form\CategoryType;
use Admin\Backend\Model\Pagination;

/**
 * Category controller.
 *
 */
class CategoryController extends Controller {
    /**
     * Lists all Category entities.
     *
     */
    public function indexAction() {
        $pageIdx = !array_key_exists('page', $_GET) ? 1 : $_GET['page'];
        $perPage = 10;
        $em = $this->getDoctrine()->getManager();
        $builder = $em->createQueryBuilder();
        $q = $builder->select('x')
            ->from(Category::class, 'x')
            ->setMaxResults($perPage)
            ->setFirstResult($pageIdx)
            ->getQuery();

        $fanta = Pagination::fromQuery($q, $perPage, $pageIdx);
        $entities = $q->getResult();

        return $this->render('BackendBundle:Category:index.html.twig', array(
            'entities' => $entities,
            'paginate' => $fanta,
        ));
    }
    /**
     * Creates a new Category entity.
     *
     */
    public function createAction(Request $request) {
        $entity = new Category();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            return $this->redirect($this->generateUrl('administration_category_show', array('id' => $entity->getId())));
        }

        return $this->render('BackendBundle:Category:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a Category entity.
     * @param Category $entity The entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Category $entity) {
        $form = $this->createForm(new CategoryType(), $entity, array(
            'action' => $this->generateUrl('administration_category_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));
        return $form;
    }

    /**
     * Displays a form to create a new Category entity.
     *
     */
    public function newAction() {
        $entity = new Category();
        $form   = $this->createCreateForm($entity);

        return $this->render('BackendBundle:Category:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Category entity.
     *
     */
    public function showAction($id) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('BackendBundle:Category')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Category entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        return $this->render('BackendBundle:Category:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Category entity.
     *
     */
    public function editAction($id) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('BackendBundle:Category')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Category entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('BackendBundle:Category:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a Category entity.
    * @param Category $entity The entity
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Category $entity) {
        $form = $this->createForm(new CategoryType(), $entity, array(
            'action' => $this->generateUrl('administration_category_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));
        return $form;
    }

    /**
     * Edits an existing Category entity.
     *
     */
    public function updateAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BackendBundle:Category')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Category entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('administration_category_edit', array('id' => $id)));
        }

        return $this->render('BackendBundle:Category:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
}

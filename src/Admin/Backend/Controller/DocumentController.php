<?php

namespace Admin\Backend\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Admin\Backend\Entity\Document;
use Admin\Backend\Form\DocumentType;
use Admin\Backend\Model\Settings;

/**
 * Document controller.
 *
 */
class DocumentController extends Controller {
    /**
     * Lists all Document entities.
     *
     */
    public function indexAction() {
        $em = $this->getDoctrine()->getManager();

        $pageParam = 'page';
        $pageIdx = !array_key_exists($pageParam, $_GET) ? 1 : $_GET[$pageParam];
        $perPage = Settings::PER_PAGE;

        $q = $this->container
            ->get('sga.admin.filter')
            ->from($em, Document::class, $perPage,
                ($pageIdx-1)*$perPage,
                ['context' => Settings::SGRS_CTX]);

        $fanta = $this->container
            ->get('sga.admin.table.pagination')
            ->fromQuery($q, $perPage, $pageIdx);

        $entities = $q->getResult();
        
        return $this->render('BackendBundle:Document:index.html.twig', array(
            'entities' => $entities,
            'fanta' => $fanta
        ));
    }

    /**
     * Creates a new Document entity.
     *
     */
    public function createAction(Request $request) {
        $entity = new Document();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity->setCreatedBy($this->getUser());            
            $em->persist($entity);
            $em->flush();
            return $this->redirect($this->generateUrl('administration_Document_edit', 
                array('id' => $entity->getId(), 'is_new' => true)));
        }

        return $this->render('BackendBundle:Document:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a Document entity.
     *
     * @param Document $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Document $entity) {
        $entity->setCreatedAt(new \DateTime);
        $form = $this->createForm(new DocumentType(), $entity, array(
            'action' => $this->generateUrl('administration_Document_create'),
            'method' => 'POST',
        ));
        return $form;
    }

    /**
     * Displays a form to create a new Document entity.
     *
     */
    public function newAction() {
        $entity = new Document();
        $form   = $this->createCreateForm($entity);

        return $this->render('BackendBundle:Document:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Document entity.
     *
     */
    public function showAction($id) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('BackendBundle:Document')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Document entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('BackendBundle:Document:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Document entity.
     *
     */
    public function editAction($id) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('BackendBundle:Document')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Document entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('BackendBundle:Document:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a Document entity.
    *
    * @param Document $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Document $entity) {
        $form = $this->createForm(new DocumentType(), $entity, array(
            'action' => $this->generateUrl('administration_Document_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        return $form;
    }

    /**
     * Edits an existing Document entity.
     *
     */
    public function updateAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('BackendBundle:Document')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Document entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('administration_Document_edit', 
                array('id' => $id,
                    'is_updated' => true))
            );
        }

        return $this->render('BackendBundle:Document:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Document entity.
     *
     */
    public function deleteAction(Request $request, $id) {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('BackendBundle:Document')->find($id);
            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Document entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('administration_Document'));
    }

    /**
     * Creates a form to delete a Document entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id) {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('administration_Document_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}

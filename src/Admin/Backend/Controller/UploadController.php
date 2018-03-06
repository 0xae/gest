<?php

namespace Admin\Backend\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

use Admin\Backend\Entity\Upload;
use Admin\Backend\Form\UploadType;

/**
 * Upload controller.
 *
 */
class UploadController extends Controller {
    /**
     * Lists all Upload entities.
     *
     */
    public function indexAction() {
        $em = $this->getDoctrine()->getManager();
        $pageIdx = !array_key_exists('page', $_GET) ? 1 : $_GET['page'];
        $perPage = 10;

        $q = $this->container
            ->get('sga.admin.filter')
            ->from($em, Upload::class, $perPage, ($pageIdx-1)*$perPage);

        $fanta = $this->container
            ->get('sga.admin.table.pagination')
            ->fromQuery($q, $perPage, $pageIdx);

        $entities = $q->getResult();         

        return $this->render('BackendBundle:Upload:index.html.twig', array(
            'entities' => $entities,
            'paginate' => $fanta
        ));
    }

    /**
     * Creates a new Upload entity.
     */
    public function createAction(Request $request) {
        $entity = new Upload();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $userId = $this->getUser();
            $entity->setCreatedBy($userId);
            $entity->setCreatedAt(new \DateTime);

            $file = $entity->getFile();
            $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();

            // moves the file to the directory where brochures are stored
            $file->move(
                $this->getParameter('models_directory'),
                $fileName
            );

            $entity->setFilename($fileName);

            $em->persist($entity);
            $em->flush();

            return $this->redirect(
                $this->generateUrl('administration_Upload_show', 
                array('id' => $entity->getId()))
            );
        }

        return $this->render('BackendBundle:Upload:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * @return string
     */
    private function generateUniqueFileName() {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }

    /**
     * Creates a form to create a Upload entity.
     *
     * @param Upload $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Upload $entity) {
        $form = $this->createForm(new UploadType(), $entity, array(
            'action' => $this->generateUrl('administration_Upload_create'),
            'method' => 'POST',
        ));
        return $form;
    }

    public function removeAction($id) {  
        $em = $this->getDoctrine()->getManager();                
        $ent = $em->getRepository('BackendBundle:Upload')->find($id);

        if (!$ent) {
            throw $this->createNotFoundException("Anexo invalido!");
        }

        $em->remove($ent);
        $em->flush();

        return new JsonResponse([
            'msg' => 'Anexo eliminado com sucesso'
        ]);
    }

    /**
     * Displays a form to create a new Upload entity.
     */
    public function newAction() {
        $entity = new Upload();
        $form = $this->createCreateForm($entity);

        return $this->render('BackendBundle:Upload:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Upload entity.
     *
     */
    public function showAction($id) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('BackendBundle:Upload')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Upload entity.');
        }

        return $this->render('BackendBundle:Upload:show.html.twig', array(
            'entity' => $entity
        ));
    }

    /**
     * Displays a form to edit an existing Upload entity.
     *
     */
    public function editAction($id) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('BackendBundle:Upload')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Upload entity.');
        }

        $editForm = $this->createEditForm($entity);

        return $this->render('BackendBundle:Upload:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView()
        ));
    }

    /**
    * Creates a form to edit a Upload entity.
    *
    * @param Upload $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Upload $entity) {
        $form = $this->createForm(new UploadType(), $entity, array(
            'action' => $this->generateUrl('administration_Upload_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        return $form;
    }

    /**
     * Edits an existing Upload entity.
     *
     */
    public function updateAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('BackendBundle:Upload')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Upload entity.');
        }

        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();
            return $this->redirect($this->generateUrl('administration_Upload_edit', array('id' => $id)));
        }

        return $this->render('BackendBundle:Upload:edit.html.twig', array(
            'entity' => $entity,
            'edit_form' => $editForm->createView()
        ));
    }

    /**
     * Deletes a Upload entity.
     *
     */
    public function deleteAction(Request $request, $id) {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('BackendBundle:Upload')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Upload entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('administration_Upload'));
    }
}

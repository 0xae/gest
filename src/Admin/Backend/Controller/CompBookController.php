<?php
namespace Admin\Backend\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Admin\Backend\Entity\CompBook;
use Admin\Backend\Entity\Upload;
use Admin\Backend\Entity\Stage;
use Admin\Backend\Form\CompBookType;
use Admin\Backend\Form\UploadType;
use Admin\Backend\Model\Settings;

/**
 * CompBook controller.
 */
class CompBookController extends Controller {
    /**
     * Lists all CompBook entities.
     */
    public function indexAction() {
        $em = $this->getDoctrine()->getManager();
        $pageIdx = !array_key_exists('page', $_GET) ? 1 : $_GET['page'];
        $perPage = Settings::PER_PAGE;

        $uploadObj = new CompBook;
        $uploadObj->setId(1);
        $uploadObj->setAnnexReference('AAA');
        $uploadForm = $this->uploadForm($uploadObj);

        $q = $this->container
            ->get('sga.admin.filter')
            // ->from($em, CompBook::class, $perPage, ($pageIdx-1)*$perPage);
            ->from($em, CompBook::class, Settings::LIMIT, 0);

        // $fanta = $this->container
        //     ->get('sga.admin.table.pagination')
        //     ->fromQuery($q, $perPage, $pageIdx);

        $entities = $q[1];

        return $this->render('BackendBundle:CompBook:index.html.twig', array(
            'entities' => $entities,
            'pageIdx' => $pageIdx,
            'upload_form' => $uploadForm
        ));
    }

	public function excelDataAction() {
        $em = $this->getDoctrine()->getManager();
        $perPage = Settings::PER_PAGE;
        $pageIdx = $_GET['page'];
        $header = array(
            "Código #",
            "Utente",
            "Fornecedor",
            "Data de recepção",
            "Data de prevista (10 dias)",
            "Criado por"
        );

        $q = $this->container
            ->get('sga.admin.filter')
            ->from($em, CompBook::class, $perPage, ($pageIdx-1)*$perPage);

        $entities = $q->getResult();
        $rows = [];

        foreach ($entities as $ent) {
            $rows[] = [
                $ent->getObjCode(),
                $ent->getClientName(),
                $ent->getSupplierName(),
                $ent->getCreatedAt()->format(Settings::DATE_FMT),
                $ent->getRespDate()->format(Settings::DATE_FMT),
                $ent->getCreatedBy()->getName() . '/' . $ent->getCreatedBy()->getEntity()->getName(),
            ];
        }

        $this->container
             ->get('sga.admin.exporter')
             ->dumpExcel($header, $rows);
    }

    public function byStateAction($state) {
        $em = $this->getDoctrine()->getManager();
        $pageIdx = !array_key_exists('page', $_GET) ? 1 : $_GET['page'];
        $perPage = 10;

        $uploadObj = new CompBook;
        $uploadObj->setId(1);
        $uploadObj->setAnnexReference('AAA');
        $uploadForm = $this->uploadForm($uploadObj);

        $tpl = 'listing';
        $label = $state;

        if ($state == Stage::ACOMPANHAMENTO) {
            $label = 'Em acompanhamento';
        } else if ($state == Stage::RESPONDIDO) {
            $label = 'Arquivo concluído com resposta';
        } else if ($state == Stage::NO_CONFOR) {
            $label = 'Não Conformidades';
        }

        $obj = $this->container
            ->get('sga.admin.filter')
            ->ByState($em, CompBook::class, $state);

        return $this->render('BackendBundle:CompBook:' . $tpl . '.html.twig', array(
            'objects' => $obj[0],
            'fanta' => $obj[1],
            'label' => $label,
            'state' => $state,
            'upload_form' => $uploadForm
        ));
    }

    public function receiptAction($id) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('BackendBundle:CompBook')->find($id);
        $type = @$_GET['type'];
        $tpl = 'receipt';

        if (!$entity) {
            throw $this->createNotFoundException('Objecto nao encontrado!');
        }

        if ($type=='response') {
            $tpl = 'response';
        } else if ($type == Stage::NO_COMP) {
            $tpl = 'no_competence';
        }

        return $this->render('BackendBundle:CompBook:docs/'.$tpl.'.html.twig', array(
            'entity' => $entity
        ));
    }

    public function showJsonAction($id) {
        $em = $this->getDoctrine()->getManager();
        $entity = $this->getDoctrine()
            ->getRepository('BackendBundle:CompBook')
            ->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Livro de reclamacao nao encontrado!');
        }

        $files = $em->getRepository('BackendBundle:Upload')
                    ->findBy(['reference' => $entity->getAnnexReference()]);

        
        $sendDate = '';
        if ($entity->getSendDate()) {
            $sendDate = $entity
                ->getSendDate()
                ->format(Settings::DATE_FMT);
        }

        $cb = $entity->getCreatedBy();
        $obj = [
            "id" => $entity->getId(),
            "clientName" => $entity->getClientName(),
            "clientPhone" => $entity->getClientPhone(),
            "clientEmail" => $entity->getClientEmail(),
            "supplierName" => $entity->getSupplierName(),
            "supplierAddress" => $entity->getSupplierAddress(),
            "complaintDate" => $entity->getComplaintDate()->format(Settings::DATE_FMT),
            "complaint" => $entity->getComplaint(),
            "annexReference" => $entity->getAnnexReference(),
            "createByName" => $cb->getName(),
            "createByEnt" => $cb->getEntity()->getName(),
            "createdAt" => $entity->getCreatedAt()->format(Settings::DATE_FMT),
            "sendDate" => $sendDate,
            "sendTo" => $entity->getSendTo(),
            "files" => []
        ];

        if ($entity->getResponseAuthor()) {
            $obj['response_date']  = $entity->getResponseDate()->format(Settings::DATE_FMT);
            $obj['response_author'] = $entity->getResponseAuthor()->getName();
            $obj['response_author_entity'] = $entity->getResponseAuthor()->getEntity()->getName();            
        }

        foreach ($files as $f) {
            $obj["files"][] = [
                "id" => $f->getId(),
                "description" => $f->getDescription(),
                "createdAt" => $f->getCreatedAt()->format(Settings::DATE_FMT),
                "createdBy" => $f->getCreatedBy()->getName(),
                "path" => $f->getFilename(),
                "filename" => $f->getFilename()
            ];
        }        

        return new JsonResponse($obj);
    }

    public function updateAcompAction($id) {
        $em = $this->getDoctrine()->getManager();
        $content = $this->get("request")->getContent();
        $object = json_decode($content, true);
        $entity = $em->getRepository('BackendBundle:CompBook')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Livro de reclamacao nao encontrado.');
        }

        if ($object['sendDate']) {
            $entity->setSendDate(new \DateTime($object['sendDate']));
        } else {
            $entity->setSendDate(new \DateTime);
        }

        $entity->setSendTo($object['sendTo']);
        // response fields
        $entity->setResponseDate(new \DateTime);
        $entity->setResponseAuthor($this->getUser());
        $entity->setState(Stage::RESPONDIDO);

        $em->persist($entity);
        $em->flush();

        return new JsonResponse($object);
    }

    /**
     * Creates a new CompBook entity.
     */
    public function createAction(Request $request) {
        $entity = new CompBook();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $userId = $this->getUser();
            $entity->setState(Stage::ACOMPANHAMENTO);
            $entity->setCreatedBy($userId);
            $entity->setCreatedAt(new \DateTime);
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('administration_CompBook_edit', array('id' => $entity->getId(), 'is_new' => true)));
        }

        return $this->render('BackendBundle:CompBook:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Displays a form to create a new CompBook entity.
     */
    public function newAction() {
        $entity = new CompBook();
        $form   = $this->createCreateForm($entity);

        return $this->render('BackendBundle:CompBook:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a CompBook entity.
     */
    public function showAction($id) {
        return $this->redirect(
            $this->generateUrl('administration_CompBook_edit', 
            array('id' => $id))
        );        
        // $em = $this->getDoctrine()->getManager();
        // $entity = $em->getRepository('BackendBundle:CompBook')->find($id);

        // if (!$entity) {
        //     throw $this->createNotFoundException('Unable to find CompBook entity.');
        // }

        // $deleteForm = $this->createDeleteForm($id);
        // return $this->render('BackendBundle:CompBook:show.html.twig', array(
        //     'entity'      => $entity,
        //     'delete_form' => $deleteForm->createView(),
        // ));
    }

    /**
     * Displays a form to edit an existing CompBook entity.
     */
    public function editAction($id) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('BackendBundle:CompBook')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find CompBook entity.');
        }

        if (@$_GET['search']) {
            $entity->isDisabled=true;
        }

        $editForm = $this->createEditForm($entity);

        $files = $em->getRepository('BackendBundle:Upload')
                    ->findBy(['reference' => $entity->getAnnexReference()]);

        return $this->render('BackendBundle:CompBook:edit.html.twig', array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'upload_form' => $this->uploadForm($entity),
            'files' => $files
        ));
    }

    /**
     * Creates a form to create a CompBook entity.
     *
     * @param CompBook $entity The entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(CompBook $entity) {
        $entity->setComplaintDate(new \DateTime);
        $entity->setAnnexReference(md5(uniqid()));
        $form = $this->createForm(new CompBookType(), $entity, array(
            'action' => $this->generateUrl('administration_CompBook_create'),
            'method' => 'POST',
        ));
        return $form;
    }    

    /**
    * Creates a form to edit a CompBook entity.
    *
    * @param CompBook $entity The entity
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(CompBook $entity) {
        $form = $this->createForm(new CompBookType(), $entity, array(
            'action' => $this->generateUrl('administration_CompBook_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        return $form;
    }

    /**
     * Edits an existing CompBook entity.
     */
    public function updateAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('BackendBundle:CompBook')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find CompBook entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();
            return $this->redirect($this->generateUrl('administration_CompBook_edit', array('id' => $id)));
        }

        return $this->render('BackendBundle:CompBook:edit.html.twig', array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView()
        ));
    }

    /**
     * Deletes a CompBook entity.
     */
    public function deleteAction(Request $request, $id) {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('BackendBundle:CompBook')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find CompBook entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('administration_CompBook'));
    }

    /**
     * Creates a form to delete a CompBook entity by id.
     *
     * @param mixed $id The entity id
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id) {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('administration_CompBook_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }

    private function uploadForm($model) {
        $entity = new Upload();
        $entity->setReference($model->getAnnexReference());

        $entity->setContext(json_encode([
            "path" => 'administration_CompBook_edit',
            "path_args" => array(
                'id' => $model->getId(),
                'upload_added' => true
            )
        ]));

        return $this->createForm(new UploadType(), $entity, array(
                'action' => $this->generateUrl('administration_Upload_create'),
                'method' => 'POST',
            ))->createView();
    }
}

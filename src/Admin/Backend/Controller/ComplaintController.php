<?php
namespace Admin\Backend\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;

use Admin\Backend\Entity\Complaint;
use Admin\Backend\Entity\Stage;
use Admin\Backend\Entity\Upload;
use Admin\Backend\Form\ComplaintType;
use Admin\Backend\Form\UploadType;
use Admin\Backend\Model\ExportDataExcel;
use Admin\Backend\Model\Settings;

/**
 * Complaint controller.
 */
class ComplaintController extends Controller {
    public function indexAction() {
        $em = $this->getDoctrine()->getManager();
        $pageIdx = !array_key_exists('page', $_GET) ? 1 : $_GET['page'];
        $perPage = Settings::PER_PAGE;

        $q = $this->container
            ->get('sga.admin.filter')
            // ->from($em, Complaint::class, $perPage, ($pageIdx-1)*$perPage);
            ->from($em, Complaint::class, Settings::LIMIT, 0);

        $entities = $q->getResult();

        return $this->render('BackendBundle:Complaint:index.html.twig', array(
            'entities' => $entities,
            'pageIdx' => $pageIdx
        ));
    }

	public function excelDataAction() {
        $em = $this->getDoctrine()->getManager();
        $pageIdx = $_GET['page'];
        $perPage = 10;

        $header = array(
            "Código #",
            "Data de recepção",
            "Data prevista de resposta",
            "Nome do utente",
            "Telefone",
            "Operador Económico",
            "Criado por"
        );

        $q = $this->container
            ->get('sga.admin.filter')
            ->from($em, Complaint::class, $perPage, ($pageIdx-1)*$perPage);

        $entities = $q->getResult();
        $rows = [];

        foreach ($entities as $ent) {
            $rows[] = [
                $ent->getObjCode(),
                $ent->getCreatedAt()->format(Settings::DATE_FMT),
                $ent->getRespDate()->format(Settings::DATE_FMT),
                $ent->getName(),
                $ent->getPhone(),
                $ent->getOpName(),
                $ent->getCreatedBy()->getName() . '/' . $ent->getCreatedBy()->getEntity()->getName(),
            ];
        }

        $this->container
             ->get('sga.admin.exporter')
             ->dumpExcel($header, $rows);
    }

    public function receiptAction($id) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('BackendBundle:Complaint')->find($id);
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

        return $this->render('BackendBundle:Complaint:docs/'.$tpl.'.html.twig', array(
            'entity' => $entity
        ));
    }

    public function parecerAction($id) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('BackendBundle:Complaint')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Objecto nao encontrado!');
        }

        return $this->render('BackendBundle:Complaint:docs/parecer.html.twig', array(
            'entity' => $entity
        ));
    }

    public function byStateAction($state) {
        $em = $this->getDoctrine()->getManager();
        $tpl = 'listing';
        $label = $state;

        if ($state == Stage::ACOMPANHAMENTO) {
            $tpl = 'acomp';
        } else if ($state == Stage::TRATAMENTO) {
            $tpl = 'treat';
        } else if ($state == Stage::SEM_RESPOSTA) {
            $label = 'Arquivo concluído sem resposta';
            $tpl = 'sem_resposta';
        } else if ($state == Stage::RESPONDIDO) {
            $label = 'Arquivo concluído com resposta';
            $tpl = 'respondidas';
        } else if ($state == Stage::NO_COMP) {
            $label = 'Competência de terceiros';
        } else if ($state == Stage::NO_FAVORABLE) {
            $label = 'Não favoráveis';
        } else if ($state == Stage::NO_CONFOR) {
            $label = 'Não Conformidades';
        }

        $obj = $this->container
            ->get('sga.admin.filter')
            ->ByState($em, 'Complaint', $state);

        return $this->render('BackendBundle:Complaint:' . $tpl . '.html.twig', array(
            'objects' => $obj[0],
            'fanta' => $obj[1],
            'type' => $label,
            'state' => $state,
            'upload_form' => $this->uploadForm(new Complaint)
        ));
    }

    public function updateStateAction($id) {
        $content = $this->get("request")->getContent();
        $object = json_decode($content, true);
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('BackendBundle:Complaint')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Objecto nao encontrado!');
        }

        $state = $object['state'];
        if ($state == Stage::TRATAMENTO) {
            $entity->setState(Stage::TRATAMENTO);
        } else if ($state == Stage::REJEITADO) { 
            $entity->setState(Stage::REJEITADO);
            $entity->setRejectionReason($object['rejectionReason']);
        } else if ($state == Stage::SEM_RESPOSTA) { 
            $entity->setState(Stage::SEM_RESPOSTA);
        } else if ($state == Stage::NO_FAVORABLE) { 
            $entity->setState(Stage::NO_FAVORABLE);
        } else if ($state == Stage::NO_COMP) {
            $entity->setState(Stage::NO_COMP);
        } else {
            throw $this->createNotFoundException('Invalid state provided: "'.$state.'"');
        }

        $em->persist($entity);       
        $em->flush();
        return new JsonResponse($object);
    }

    public function updateParAction($id) {
        $content = $this->get("request")->getContent();
        $data = json_decode($content, true);

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('BackendBundle:Complaint')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Essa Queixa/Reclamacao nao foi encontrada.');
        }
        
        if (@$data['parCode']) 
            $entity->setParCode($data['parCode']);
        if (@$data['parSubject'])
            $entity->setParSubject($data['parSubject']);
        if (@$data['parDestination'])
            $entity->setParDest($data['parDestination']);
        if (@$data['parDescription'])
            $entity->setParDescription($data['parDescription']);

        $entity->setParType($data['type']);
        $entity->setParAuthor($this->getUser());
        $entity->setParDate(new \DateTime());

        // sends it back to acomp
        $entity->setState(Stage::ACOMPANHAMENTO);   
        $em->persist($entity);       
        $em->flush();

        return new JsonResponse([
            "id" => $entity->getId()
        ]);
    }

    public function showJsonAction($id) {
        $em = $this->getDoctrine()->getManager();

        $entity = $this->getDoctrine()
                       ->getRepository('BackendBundle:Complaint')
                       ->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Essa Queixa/Reclamacao nao foi encontrada.');
        }

        $files = $em->getRepository('BackendBundle:Upload')
                    ->findBy(['reference' => $entity->getAnnexReference()]);

        $cb = $entity->getCreatedBy();
        $obj = [
            "id" => $entity->getId(),
            "name" => $entity->getName(),
            "factDetail" => $entity->getFactDetail(),
            "factDate" => $entity->getFactDate(),
            "opPhone" => $entity->getOpPhone(),
            "opEmail" => $entity->getOpEmail(),
            "opName" => $entity->getOpName(),
            "phone" => $entity->getPhone(),
            "email" => $entity->getEmail(),
            "type" => $entity->getType(),
            "objCode" => $entity->getObjCode(),
            "annexReference" => $entity->getAnnexReference(),
            "createByName" => $cb->getName(),
            "createByEnt" => $cb->getEntity()->getName(),
        ];

        if ($entity->getParAuthor()) {
            $obj["parCode"] = $entity->getParCode();
            $obj["parDate"] = $entity->getParDate()->format(Settings::DATE_FMT);
            $obj["parAuthorName"] = $entity->getParAuthor()->getName();
            $obj["parSubject"] = $entity->getParSubject();
            $obj["parDest"] = $entity->getParDest();
            $obj["parDescription"] = $entity->getParDescription();
            $obj["parType"] = $entity->getParType();            
        }

        foreach ($files as $f) {
            $obj["files"][] = [
                "id" => $f->getId(),
                "description" => $f->getDescription(),
                "createdAt" => $f->getCreatedAt()->format(Settings::DATE_FMT),
                "createdBy" => $f->getCreatedBy()->getName(),
                "path" => $f->getFilename()
            ];
        }

        return new JsonResponse($obj);
    }

    public function respondAction($id) {
        $content = $this->get("request")->getContent();
        $object = json_decode($content, true);
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('BackendBundle:Complaint')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Essa Queixa/Reclamacao nao foi encontrada.');
        }

        $entity->setState(Stage::RESPONDIDO);
        $entity->setResponseDate(new \DateTime);
        $entity->setResponseAuthor($this->getUser());
        $entity->setClientResponse($object['response']);

        $em->persist($entity);
        $em->flush();

        return new JsonResponse($object);
    }

    /**
     * Creates a new Complaint entity.
     *
     */
    public function createAction(Request $request) {
        $entity = new Complaint();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $userId = $this->getUser();
            $entity->setCreatedBy($userId);
            $entity->setCreatedAt(new \DateTime);
            $entity->setState(Stage::ACOMPANHAMENTO);

            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('administration_Complaint_edit', 
                array('id' => $entity->getId(),
                    'is_new' => true)));
        }

        return $this->render('BackendBundle:Complaint:new.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView()
        ));
    }

    /**
     * Displays a form to create a new Complaint entity.
     *
     */
    public function newAction() {
        $entity = new Complaint();
        $entity->setCreatedAt(new \DateTime);
        $entity->setSector('farm');  
        $form = $this->createCreateForm($entity);

        return $this->render('BackendBundle:Complaint:new.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView(),
            'fact_annex' => ""
        ));
    }

    /**
     * Finds and displays a Complaint entity.
     *
     */
    public function showAction($id) {
        return $this->redirect(
            $this->generateUrl('administration_Complaint_edit', 
            array('id' => $id))
        );
        // $em = $this->getDoctrine()->getManager();
        // $entity = $em->getRepository('BackendBundle:Complaint')->find($id);

        // if (!$entity) {
        //     throw $this->createNotFoundException('Unable to find Complaint entity.');
        // }
        // $annex = $entity->getFactAnnex();        
        // $path = false;
        // if ($annex) {
        //     $path = $this->getParameter('complaints_directory') . '/' . $annex;
        //     $entity->setFactAnnex(new File($path));
        // }        
        // return $this->render('BackendBundle:Complaint:show.html.twig', array(
        //     'entity' => $entity
        // ));
    }

    /**
     * Displays a form to edit an existing Complaint entity.
     *
     */
    public function editAction($id) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('BackendBundle:Complaint')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Complaint entity.');
        }

        if (@$_GET['search']) {
            $entity->isDisabled=true;
        }

        $editForm = $this->createEditForm($entity);
        $files = $em->getRepository('BackendBundle:Upload')
                    ->findBy(['reference' => $entity->getAnnexReference()]);

        return $this->render('BackendBundle:Complaint:edit.html.twig', array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'upload_form' => $this->uploadForm($entity),
            'files' => $files
        ));
    }

    /**
     * Edits an existing Complaint entity.
     *
     */
    public function updateAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('BackendBundle:Complaint')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Complaint entity.');
        }

        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();
            return $this->redirect($this->generateUrl('administration_Complaint_edit', array('id' => $id)));
        }

        return $this->render('BackendBundle:Complaint:edit.html.twig', array(
            'entity' => $entity,
            'edit_form' => $editForm->createView()
        ));
    }

    /**
     * Deletes a Complaint entity.
     *
     */
    public function deleteAction(Request $request, $id) {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('BackendBundle:Complaint')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Complaint entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('administration_Complaint'));
    }

    /**
    * Creates a form to edit a Complaint entity.
    *
    * @param Complaint $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Complaint $entity) {
        $form = $this->createForm(new ComplaintType(), $entity, array(
            'action' => $this->generateUrl('administration_Complaint_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        return $form;
    }

    /**
     * Creates a form to create a Complaint entity.
     *
     * @param Complaint $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Complaint $entity) {
        $dev = true;
        // if ($dev) {
        //     $entity->setName("Ayrton Gomes");
        //     $entity->setAddress("Praia, Cabo Verde");
        //     $entity->setLocality("Palmarejo");
        //     $entity->setPhone("255 12 90");
        //     $entity->setEmail("com.ayrton@gmail.com");

        //     $entity->setOpName("Farmacia 2000");
        //     $entity->setOpAddress("Praia, Cabo Verde");
        //     $entity->setOpLocality("Achada St. Antonio");
        //     $entity->setOpPhone("262 64 10");
        //     $entity->setOpEmail("arfa@arfa.gov.cv");

        //     $entity->setFactLocality("Praia, Cabo Verde");
        //     $entity->setFactDetail("teste 123");            
        // }

        $entity->setAnnexReference(md5(uniqid()));
        $entity->setFactDate(new \DateTime);
        $entity->setComplaintCategory('m1');
        $form = $this->createForm(new ComplaintType(), $entity, array(
            'action' => $this->generateUrl('administration_Complaint_create'),
            'method' => 'POST',
        ));

        return $form;
    }

    private function uploadForm($model) {
        $entity = new Upload();

        if ($model->getAnnexReference()) {
            $entity->setReference($model->getAnnexReference());
        }

        if ($model->getId()) {
            $entity->setContext(json_encode([
                "path" => 'administration_Complaint_edit',
                "path_args" => array(
                    'id' => $model->getId(),
                    'upload_added' => true
                )
            ]));
        }

        return $this->createForm(new UploadType(), $entity, array(
                'action' => $this->generateUrl('administration_Upload_create'),
                'method' => 'POST',
            ))->createView();
    }

    /**
     * Creates a form to delete a Complaint entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id) {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('administration_Complaint_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}

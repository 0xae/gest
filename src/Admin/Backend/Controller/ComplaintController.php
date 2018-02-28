<?php

namespace Admin\Backend\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;

use Admin\Backend\Entity\Complaint;
use Admin\Backend\Entity\Stage;
use Admin\Backend\Form\ComplaintType;

/**
 * Complaint controller.
 *
 */
class ComplaintController extends Controller {
    /**
     * Lists all Complaint entities.
     *
     */
    public function indexAction() {
        $em = $this->getDoctrine()->getManager();
        $pageIdx = !array_key_exists('page', $_GET) ? 1 : $_GET['page'];
        $perPage = 10;

        $q = $this->container
            ->get('sga.admin.filter')
            ->from($em, Complaint::class, $perPage, ($pageIdx-1)*$perPage);

        $fanta = $this->container
            ->get('sga.admin.table.pagination')
            ->fromQuery($q, $perPage, $pageIdx);

        $entities = $q->getResult();            

        return $this->render('BackendBundle:Complaint:index.html.twig', array(
            'entities' => $entities,
            'paginate' => $fanta
        ));
    }

    public function byStateAction($state) {
        $em = $this->getDoctrine()->getManager();        
        $tpl = '';
        if ($state == Stage::ACOMPANHAMENTO) {
            $tpl = 'acomp';
        } else if ($state == Stage::TRATAMENTO) {
            $tpl = 'treat';
        } else if ($state == Stage::SEM_RESPOSTA) {
            $tpl = 'sem_resposta';
        } else if ($state == Stage::RESPONDIDO) {
            $tpl = 'respondidas';
        } else {
            $tpl = 'acomp';
        }

        $ary = $em->getRepository('BackendBundle:Complaint')
                  ->findBy(['state' => $state]);

        return $this->render('BackendBundle:Complaint:' . $tpl . '.html.twig', array(
            'objects' => $ary
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
            // throw new Exception
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

        $entity->setParCode($data['parCode']);
        $entity->setParSubject($data['parSubject']);
        $entity->setParDest($data['parDestination']);
        $entity->setParDescription($data['parDescription']);
        $entity->setParType($data['type']);
        $entity->setParAuthor($this->getUser());
        $entity->setParDate(new \DateTime());    
        // sends it back to acomp
        // $entity->setState(Stage::ACOMPANHAMENTO);

        $em->persist($entity);       
        $em->flush();

        return new JsonResponse([
            "id" => $entity->getId()
        ]);
    }

    public function showJsonAction($id) {
        $entity = $this->getDoctrine()
                    ->getRepository('BackendBundle:Complaint')                
                    ->find($id);

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
            "createByName" => $cb->getName(),
            "createByEnt" => $cb->getEntity()->getName(),
        ];

        if ($entity->getParCode()) {            
            $obj["parCode"] = $entity->getParCode();
            if ($entity->getParDate())
                $obj["parDate"] = $entity->getParDate()->format("Y-m-d");            
            $obj["parAuthorName"] = $entity->getParAuthor()->getName();
            $obj["parSubject"] = $entity->getParSubject();
            $obj["parDest"] = $entity->getParDest();
            $obj["parDescription"] = $entity->getParDescription();
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
        $entity->setClientResponse($object['clientResponse']);
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
            $entity->setState(Stage::ACOMPANHAMENTO);

            // $file = $entity->getFactAnnex();
            // if ($file) {
            //     $fileName = md5(uniqid()).'.'.$file->guessExtension();
            //     $file->move(
            //         $this->getParameter('complaints_directory'),
            //         $fileName
            //     );
            //     $entity->setFactAnnex($fileName);
            // }
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('administration_Complaint_show', 
                array('id' => $entity->getId(),
                    'is_new' => true)));
        }

        return $this->render('BackendBundle:Complaint:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Displays a form to create a new Complaint entity.
     *
     */
    public function newAction() {
        $entity = new Complaint();
        $entity->setCreatedAt(new \DateTime);        
        $form = $this->createCreateForm($entity);

        return $this->render('BackendBundle:Complaint:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Complaint entity.
     *
     */
    public function showAction($id) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('BackendBundle:Complaint')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Complaint entity.');
        }

        // $annex = $entity->getFactAnnex();        
        // $path = false;
        // if ($annex) {
        //     $path = $this->getParameter('complaints_directory') . '/' . $annex;
        //     $entity->setFactAnnex(new File($path));
        // }
        return $this->render('BackendBundle:Complaint:show.html.twig', array(
            'entity' => $entity
        ));
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

        // $annex = $entity->getFactAnnex();
        // $path = false;
        // if ($annex) {
        //     $path = $this->getParameter('complaints_directory') . '/' . $annex;
        //     $entity->setFactAnnex(new File($path));
        // }

        $editForm = $this->createEditForm($entity);
        return $this->render('BackendBundle:Complaint:edit.html.twig', array(
            'entity' => $entity,
            'edit_form' => $editForm->createView()
            // 'upload_path' => $path
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

        // $annex = $entity->getFactAnnex();
        // $path = false;
        // if ($annex) {
        //     $path = $this->getParameter('complaints_directory') . '/' . $annex;
        //     $entity->setFactAnnex(new File($path));
        // }
        // $name = $entity->getFactAnnex();
        // if ($name) {
        //     $path = $this->getParameter('complaints_directory') . '/' . $name;
        //     $file=new File($path);
        //     $fileName = md5(uniqid()).'.'.$file->guessExtension();
        //     $file->move(
        //         $this->getParameter('complaints_directory'),
        //         $fileName
        //     );
        //     $entity->setFactAnnex($file);
        // }    

        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();
            return $this->redirect($this->generateUrl('administration_Complaint_edit', array('id' => $id)));
        }

        return $this->render('BackendBundle:Complaint:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView()
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

        $entity->setFactDate(new \DateTime);
        $form = $this->createForm(new ComplaintType(), $entity, array(
            'action' => $this->generateUrl('administration_Complaint_create'),
            'method' => 'POST',
        ));

        return $form;
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

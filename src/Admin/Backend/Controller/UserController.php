<?php

namespace Admin\Backend\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Admin\Backend\Entity\User;
use Admin\Backend\Form\UserType;
use Admin\Backend\Entity\Upload;
use Admin\Backend\Form\UploadType;
use Admin\Backend\Model\Settings;

/**
 * User controller.
 */
class UserController extends Controller {
    /**
     * Lists all User entities.
     *
     */
    public function indexAction() {
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('BackendBundle:User')->findAll();

        return $this->render('BackendBundle:User:index.html.twig', array(
            'entities' => $entities,
        ));
    }

    /**
     * Creates a new User entity.
     *
     */
    public function createAction(Request $request) {
        $entity = new User();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);
        $loginFieldsOk = true;

        if ($this->alreadyExists('email', $entity->getEmail())) {
            $form->get('email')
                ->addError(new FormError('Email nao disponivel!'));
            $loginFieldsOk = false;
        } 

        if ($this->alreadyExists('username', $entity->getUsername())) {
            $form->get('username')
                ->addError(new FormError('Username nao disponivel!'));
            $loginFieldsOk = false;
        } 
        
        if ($entity->getPlainPassword() != $entity->getPasswordConf()) {
            $form->get('plainPassword')
                ->addError(new FormError('As passwords nao coincidem'));
            $form->get('passwordConf')
                ->addError(new FormError('As passwords nao coincidem'));
            $loginFieldsOk = false;                
        } 
        
        if ($form->isValid() && $loginFieldsOk) {
            $em = $this->getDoctrine()->getManager();
            $entity->setContext(Settings::SGRS_CTX);
            $entity->setEnabled(true);
            $user = $this->getUser();
            $entity->setCreatedBy($user);
            $em->persist($entity);
            $em->flush();

            // supposed to generate password after create ?
            $this->container->get('fos_user.user_manager')
                ->updateUser($entity);

            $newRoles = $this->getRoles($entity->getProfile()->getId());
            foreach ($newRoles as $role) {
                $entity->addRole($role);
            }

            // return $this->redirect($this->generateUrl('administration_user_show', array('id' => $entity->getId())));
            return $this->redirect($this->generateUrl('backend_administration_main', array(
                'tab' => 'list_user',
                'is_new' => true
            )));            
        }

        return $this->render('BackendBundle:User:new.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView(),
        ));
    }

    private function alreadyExists($field, $value) {        
        $em = $this->getDoctrine()->getManager();        
        $resp = $em->getRepository('BackendBundle:User')
            ->findBy([$field=>$value]);
        return $resp;
    }

    /**
     * Creates a form to create a User entity.
     *
     * @param User $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(User $entity) {
        $entity->setIsActive(true);
        $form = $this->createForm(new UserType(), $entity, array(
            'action' => $this->generateUrl('administration_user_create'),
            'method' => 'POST',
        ));
        return $form;
    }

    /**
     * Displays a form to create a new User entity.
     *
     */
    public function newAction() {
        $entity = new User();
        $form = $this->createCreateForm($entity);

        return $this->render('BackendBundle:User:new.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a User entity.
     *
     */
    public function showAction($id) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('BackendBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('BackendBundle:User:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing User entity.
     *
     */
    public function editAction($id) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('BackendBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);
        $photo = false;
        $fotos = $em->getRepository('BackendBundle:Upload')
            ->findBy(['reference' => 'user_'.$entity->getId()]);

        $changePassword = $this->container->get('fos_user.change_password.form');

        foreach ($fotos as $f) {
            $photo = $f->getFilename();
        }

        $user = $this->getUser();
        // if (!is_object($user) || !$user instanceof UserInterface) {
        //     throw new AccessDeniedException('This user does not have access to this section.');
        // }
        $form = $this->container->get('fos_user.change_password.form');
        $formHandler = $this->container->get('fos_user.change_password.form.handler');
        $process = $formHandler->process($user);
        $tab='';
        $flashMsg='';

        if ($process) {
            $flashMsg = 'Password actualizado com sucesso';
        } else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $tab='tab4';
        }

        return $this->render('BackendBundle:User:edit.html.twig', array(
            'entity' => $entity,
            'edit_form'  => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'photo_form' => $this->uploadForm($entity),
            'user_photo' => $photo,
            'user_id' => $user->getId(),
            'change_password_form' => $changePassword->createView(),
            'tab' => $tab,
            'flashMsg' => $flashMsg
        ));
    }

    /**
     * Edits an existing User entity.
     *
     */
    public function updateAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('BackendBundle:User')->find($id);
        $oldUsername = $entity->getUsername();

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $_oldRules = $entity->getProfile()->getId();
        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            if ($_oldRules != $entity->getProfile()->getId()) {
                $oldRoles = $this->getRoles($_oldRules);
                $newRoles = $this->getRoles($entity->getProfile()->getId());
                
                foreach ($oldRoles as $role) {
                    $entity->removeRole($role);                    
                }
                
                foreach ($newRoles as $role) {
                    $entity->addRole($role);
                }
            }

            $entity->setUserName($oldUsername);
            if (!$entity->getCreatedBy()) {
                $user = $this->getUser();
                $entity->setCreatedBy($user);    
            }

            $em->flush();
            return $this->redirect($this->generateUrl('backend_administration_main', array('tab' => 'list_user')));                        
        }

        return $this->render('BackendBundle:User:edit.html.twig', array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    private function getRoles($profileId) {
        $em = $this->getDoctrine()->getManager();
        $results = $em->getRepository('BackendBundle:ProfilePermission')
            ->findBy(array(
                'profile' => $profileId
            ));

        $ary = [];
        foreach ($results as $val) {
            $ary[]=$val->getPermission();
        }
        return $ary;
    }

    /**
     * Deletes a User entity.
     *
     */
    public function deleteAction(Request $request, $id) {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('BackendBundle:User')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find User entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('administration_user'));
    }

    /**
    * Creates a form to edit a User entity.
    *
    * @param User $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(User $entity) {
        $form = $this->createForm(new UserType(), $entity, array(
            'action' => $this->generateUrl('administration_user_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));
        return $form;
    }

    private function uploadForm($model) {
        $entity = new Upload();
        $entity->setReference('user_' . $model->getId());
        $entity->setDescription('Foto de ' . $model->getName());

        return $this->createForm(new UploadType(), $entity, array(
                'action' => $this->generateUrl('administration_Upload_create'),
                'method' => 'POST',
            ))->createView();
    }

    /**
     * Creates a form to delete a User entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id) {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('administration_user_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm();
    }
}

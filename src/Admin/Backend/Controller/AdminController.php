<?php
namespace Admin\Backend\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Admin\Backend\Entity\User;
use Admin\Backend\Form\UserType;
use Admin\Backend\Entity\Profile;
use Admin\Backend\Form\ProfileType;
use Admin\Backend\Entity\UserProfile;
use Admin\Backend\Form\UserProfileType;
use Admin\Backend\Model\Settings;
use Admin\Backend\Entity\ProfilePermission;

/**
 * Admin controller.
 */
class AdminController extends Controller {
    public function indexAction() {
        $em = $this->getDoctrine()->getManager();
        $userForm = $this->createUserForm();
        $profileForm = $this->createProfileForm();
        $users = $this->paginate($em, User::class, 'page_user');
        $profiles = $this->paginate($em, Profile::class, 'page_profile');
        $assocProfile = $this->createAssocProfileForm();
        $permissions = Settings::getPermissions();

        $currentTab='create_user';
        if (@$_GET['tab']) {
            $currentTab=$_GET['tab'];
        }

        return $this->render('BackendBundle:Admin:index.html.twig', array(
            'user_list' => $users->getResult(),
            'profile_list' => $profiles->getResult(),
            'user_form' => $userForm->createView(),            
            'profile_form' => $profileForm->createView(),            
            'assoc_profile_form' => $assocProfile->createView(),
            'permissions' => $permissions,
            'current_tab' => $currentTab
        ));
    }

    public function searchAction() {
        $em = $this->getDoctrine()->getManager();
        $query = @$_GET['q'];
        $obj = $this->container
            ->get('sga.admin.filter')
            ->ByCode($em, $query);

        return $this->render('BackendBundle:Admin:search.html.twig', array(
            'q' => $query,
            'results' => $obj
        ));
    }

   /**
     * Fetches all permissions of a profile
     * @param User $entity The entity
     * @return \Symfony\Component\Form\Form The form
     */
    public function permissionsOfAction($id) {
        $em = $this->getDoctrine()->getManager();
        $results = $em->getRepository('BackendBundle:ProfilePermission')
        ->findBy(array('profile' => $id));

        $ary = [];
        foreach ($results as $val) {
            $ary[] = [
                'id' => $val->getId(),
                'profile' => $id,
                'permission' => $val->getPermission(),
                'permissionLabel' => $val->getPermissionLabel(),
                'label'
            ];
        }

        return new JsonResponse($ary);
    }

    public function removePermissionAction($id) {  
        $em = $this->getDoctrine()->getManager();                
        $ent = $em->getRepository('BackendBundle:ProfilePermission')->find($id);

        if ($ent) {
            $users = $em->getRepository('BackendBundle:User')
                        ->findBy(['profile' => $ent->getProfile()->getId()]);

            $permission = $ent->getPermission();

            foreach ($users as $user) {
                $user->removeRole($permission);
            }

            $em->remove($ent);
            $em->flush();        
        }

        return new JsonResponse([
            'msg' => 'Permissao removida'
        ]);
    }

    public function addPermissionAction() {
        $em = $this->getDoctrine()->getManager();        
        $permission = trim(strtolower($_GET['permission']));
        $profileId = $_GET['profile_id'];
        $permissionLabel = $_GET['permission_label'];

        $ent = $em->getRepository('BackendBundle:ProfilePermission')
            ->findBy(array(
                'profile' => $profileId,
                'permission' => $permission
            ));

        // this profile already has that permission
        if ($ent) {
            $resp = [
                'id' => $ent[0]->getId(),
                'permission' => $ent[0]->getPermission(),
                'permissionLabel' => $ent[0]->getPermissionLabel(),
                'profile' => $ent[0]->getProfile()->getId(),
            ];
        } else {
            $profile = $em->getRepository('BackendBundle:Profile')
                ->find($profileId);

            if (!$profile) {
                throw $this->createNotFoundException("Perfil ($profileId) invalido!");
            }        

            $ent = new ProfilePermission;
            $ent->setPermission($permission);
            $ent->setCreatedBy($this->getUser());
            $ent->setCreatedAt(new \DateTime);
            $ent->setPermissionLabel($permissionLabel);
            $ent->setProfile($profile);
            
            $users = $em->getRepository('BackendBundle:User')
                        ->findBy(['profile' => $profileId]);
            
            foreach ($users as $user) {
                $user->addRole($permission);
            }

            $em->persist($ent);
            $em->flush();          
            
            $resp = [
                'id' => $ent->getId(),
                'permission' => $ent->getPermission(),
                'permissionLabel' => $ent->getPermissionLabel(),
                'profile' => $ent->getProfile()->getId(),
            ];
        }

        return new JsonResponse($resp);
    }

    public function permissionMapAction() {
        return new JsonResponse(Settings::getPermissions());
    }

   /**
     * Creates a form to create a User entity.
     *
     * @param User $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createUserForm() {
        $form = $this->createForm(new UserType(), new User, array(
            'action' => $this->generateUrl('administration_user_create'),
            'method' => 'POST',
        ));
        return $form;
    }

    /**
     * Creates a form to create a Profile entity.
     *
     * @param Profile $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createProfileForm() {
        $form = $this->createForm(new ProfileType(), new Profile(), array(
            'action' => $this->generateUrl('administration_Profile_create'),
            'method' => 'POST'
        ));
        return $form;
    }

    /**
     * Creates a form to create a UserProfile entity.
     *
     * @param UserProfile $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createAssocProfileForm() {
        $form = $this->createForm(new UserProfileType(), new UserProfile(), array(
            'action' => $this->generateUrl('administration_UserProfile_create'),
            'method' => 'POST'
        ));
        return $form;
    }

    private function paginate($em, $class, $pageParam) {
        $pageIdx = !array_key_exists($pageParam, $_GET) ? 1 : $_GET[$pageParam];
        $perPage = Settings::PER_PAGE;

        $q = $this->container
            ->get('sga.admin.filter')
            ->from($em, $class, Settings::LIMIT, 0,
                ['context' => Settings::SGRS_CTX]);
        // $fanta = $this->container
        //     ->get('sga.admin.table.pagination')
        //     ->fromQuery($q, $perPage, $pageIdx);
        return $q;
    }
}

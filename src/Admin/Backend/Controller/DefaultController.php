<?php
namespace Admin\Backend\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Admin\Backend\Entity\Model;
use Admin\Backend\Entity\Stage;
use Admin\Backend\Model\ExportDataExcel;

class DefaultController extends Controller {
    public function indexAction() {
        $em = $this->getDoctrine()->getManager();
        $userId = $this->getUser()->getId();
        $month = $this->getCurrentMonth();
        $fotos = $em->getRepository('BackendBundle:Upload')
                    ->findBy(['reference' => 'user_'.$userId]);
        $photo = false;

        foreach ($fotos as $f) {
            $photo = $f->getFilename();
        }

        if ($photo) {
            $user = $em->getRepository('BackendBundle:User')->find($userId);
            $user->setPhotoDir($photo);
            $em->persist($user);       
            $em->flush();
        }

        return $this->render('BackendBundle:Home:dashboard.html.twig', array(
            "month" => $month,
            "globalCounters" => $this->getGlobalCounts()
        ));
    }

    public function getGlobalCounts() {
        $all = [
            (int) $this->count(Model::DENOUNCE, 'complaint'),
            (int) $this->count(Model::COMPLAINT, 'complaint'),
            (int) $this->count(Model::RECLAMATION_EXTERN, 'sugestion'),
            (int) $this->count(Model::SUGESTION, 'sugestion'),
            (int) $this->count(Model::COMP_BOOK, 'comp_book'),          
            (int) $this->countIRECL(),
        ];

        $params=['state'=>Stage::RESPONDIDO];
        $answered = [
            (int) $this->count(Model::DENOUNCE, 'complaint', $params),
            (int) $this->count(Model::COMPLAINT, 'complaint', $params),
            (int) $this->count(Model::RECLAMATION_EXTERN, 'sugestion', $params),
            (int) $this->count(Model::SUGESTION, 'sugestion', $params),
            (int) $this->count(Model::COMP_BOOK, 'comp_book', $params),         
            (int) $this->countIRECL($params),
        ];

        $params=['state'=>Stage::SEM_RESPOSTA];
        $noAnswered = [
            (int) $this->count(Model::DENOUNCE, 'complaint', $params),
            (int) $this->count(Model::COMPLAINT, 'complaint', $params),
            (int) $this->count(Model::RECLAMATION_EXTERN, 'sugestion', $params),
            (int) $this->count(Model::SUGESTION, 'sugestion', $params),
            (int) $this->countIRECL($params),
        ];

        $params=['state'=>Stage::ACOMPANHAMENTO];
        $acomp = [
            (int) $this->count(Model::DENOUNCE, 'complaint', $params),
            (int) $this->count(Model::COMPLAINT, 'complaint', $params),
            (int) $this->count(Model::RECLAMATION_EXTERN, 'sugestion', $params),
            (int) $this->count(Model::SUGESTION, 'sugestion', $params),
            (int) $this->count(Model::COMP_BOOK, 'comp_book', $params),
            (int) $this->countIRECL($params),
        ];

        $params=['state'=>Stage::NO_CONFOR];
        $nc = [
            (int) $this->count(Model::DENOUNCE, 'complaint', $params),
            (int) $this->count(Model::COMPLAINT, 'complaint', $params),
            (int) $this->count(Model::RECLAMATION_EXTERN, 'sugestion', $params),
            (int) $this->count(Model::SUGESTION, 'sugestion', $params),
            (int) $this->count(Model::COMP_BOOK, 'comp_book', $params),
            (int) $this->countIRECL($params),
        ];

        return [
            "total" => array_sum($all),
            "answered" => array_sum($answered),
            "noAnswered" => array_sum($noAnswered),
            "acomp" => array_sum($acomp),
            "notConfor" => array_sum($nc)
        ];
    }

    private function count($type, $model, $opts=[]) {
        $q = '
            select id
            from ' . $model . '
            where year(created_at) = year(current_date)
                and month(created_at) = month(current_date) 
        ';
        $params = [];

        if ($type!='comp_book') {
            $q .= ' and type = "'.$type.'"';
        }

        if (@$opts['state']) {
            $q .= ' and state=:state ';
            $params['state']=$opts['state'];
        }

        $rows=$this->fetchAll($q, $params);
        $count=count($rows);
        return $count;
    }

    private function countIRECL($opts=[]) {
        $q = '
            select 
                count(1) as count,
                date_format(created_at, "%Y-%m") as period
            from reclamation_internal
            where year(created_at) = year(current_date)
                and month(created_at) = month(current_date) '
            ;

        $params = [];
            
        if (@$opts['state']) {
            $q .= ' and state=:state ';
            $params['state']=$opts['state'];
        }

        return $this->fetchAll($q, $params);
    }

    private function fetchAll($sql, $params) {
        $em = $this->getDoctrine()->getManager();
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    private function getCurrentMonth() {
        $currMonth = date('m');
        if ($currMonth == '1') {
            return 'Janeiro';
        } else if ($currMonth == '2') {
            return 'Fevereiro';
        } else if ($currMonth == '3') {
            return 'Março';
        } else if ($currMonth == '4') {
            return 'Abril';
        } else if ($currMonth == '5') {
            return 'Maio';
        } else if ($currMonth == '6') {
            return 'Junho';
        } else if ($currMonth == '7') {
            return 'Julho';
        } else if ($currMonth == '8') {
            return 'Agosto';
        } else if ($currMonth == '9') {
            return 'Setembro';
        } else if ($currMonth == '10') {
            return 'Outubro';
        } else if ($currMonth == '11') {
            return 'Novembro';
        } else if ($currMonth == '12') {
            return 'Dezembro';
        }
    }
}

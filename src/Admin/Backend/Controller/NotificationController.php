<?php

namespace Admin\Backend\Controller;

// use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Admin\Backend\Entity\Notification;
use Admin\Backend\Form\NotificationType;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Notification controller.
 *
 */
class NotificationController extends Controller {
    /**
     * Lists all Notification entities.
     *
     */
    public function indexAction() {
        $em=$this->getDoctrine()->getManager();
        $type=@$_GET['type']?$_GET['type'] : 'complaint';
        $oType='type';
        $days=15;

        if ($type=='comp_book') {
            $oType="'comp_book'";
            $days=10;
        }else if($type=='reclamation_internal') {
            $oType="'reclamation_internal'";
        }

        $SQL = "
            select 
                id, $oType as type,
                created_at,
                date_add(created_at, interval 15 day) as resp_date,
                state,
                date_format(created_at, '%Y-%m-%d') as created_at_fmt,
                datediff(date_add(created_at, interval $days day), date_format(now(), '%Y-%m-%d')) as days_left,
                (select codigo from app_entity where id = (select entity from user where id=T.created_by)) as department,
                date_format(created_at, '%Y') as year
            from $type T
            where response_date is null
        ;";

        $stmt = $em->getConnection()->prepare($SQL);
        $stmt->execute([]);        
        $resp=$stmt->fetchAll();

        return new JsonResponse($resp);
    }

    private function getObjCode($type, $obj) {
    }

    private function calculateNextDate($sdate, $edate, $holidays) {
        $start = new DateTime($sdate);
        $end = new DateTime($edate);
        // otherwise the  end date is excluded (bug?)
        // $end->modify('+1 day');

        $interval = $end->diff($start);

        // total days
        $days = $interval->days;

        // create an iterateable period of date (P1D equates to 1 day)
        $period = new DatePeriod($start, new DateInterval('P1D'), $end);

        // best stored as array, so you can add more than one
        //$holidays = ['2019-02-13'];

        foreach($period as $dt) {
            $curr = $dt->format('D');
            $fmt=$dt->format('Y-m-d');

            // substract if Saturday or Sunday
            if ($curr == 'Sat' || $curr == 'Sun') {
                $days--;
            } elseif (in_array($dt->format('Y-m-d'), $holidays)) {
                $days--;
            }
        }

        return $days;
    }
}

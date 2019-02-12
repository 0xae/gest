<?php
namespace Admin\Backend\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use Admin\Backend\Entity\Category;
use Admin\Backend\Entity\Stage;
use Admin\Backend\Entity\Model;
use Admin\Backend\Form\CategoryType;

/**
 * Statistics controller.
 */
class StatsController extends Controller {
    public function indexAction() {
    	$start=date('Y-01-01');
    	$end=date('Y-12-31');
		$pie = $this->getTypeDistribution($start, $end);

        return $this->render('BackendBundle:Stats:index.html.twig', array(
			'thirdy_party' => $this->getThirdPartyCounts($start, $end),
			'pie' => $pie['pie']
        ));
	}

	public function ajaxAction($type) {
		$year = date('Y');
		$month = date('m');
		$data = [];

		if (@$_GET['year']) {
			$year = $_GET['year'];
		}

		if (@$_GET['month']) {
			$month = $_GET['month'];
		}

		if ($type == 'avgResponseTime') {
			$start=$_GET['start'];
			$end=$_GET['end'];
			$data = $this->getAvgResponseTime($start, $end);
		} else if ($type == 'by_department'){
			$start=$_GET['start'];
			$end=$_GET['end'];
			$data = $this->groupByDepartment($start, $end);
		} else if ($type == 'by_incump') {
			$start=$_GET['start'];
			$end=$_GET['end'];
			$data = $this->getIncumprimentoByDepartment($start, $end);
		} else if ($type == 'by_type') {
			$start=$_GET['start'];
			$end=$_GET['end'];
			$data = $this->getTypeDistribution($start, $end);
		}  else if ($type == 'by_month') {
			$data = $this->groupByMonth($year);
		}

		return new JsonResponse($data);
	}

	private function getTypeDistribution($start, $end) {	
		$counters = $this->getCounters($start, $end);

		$total = (int) $counters[Model::DENOUNCE][0]["count"] +
			(int) $counters[Model::COMPLAINT][0]["count"] +
			(int) $counters[Model::RECLAMATION_EXTERN][0]["count"] +
			(int) $counters[Model::SUGESTION][0]["count"] +
			(int) $counters[Model::RECLAMATION_INTERNAL][0]["count"] + 
			(int) $counters[Model::COMP_BOOK][0]["count"]	
		;

		if ($total==0) {
			$total=1;
		}

		$pie = [
			Model::DENOUNCE => (int) $counters[Model::DENOUNCE][0]["count"] / $total,
			Model::COMPLAINT => (int) $counters[Model::COMPLAINT][0]["count"] / $total,
			Model::RECLAMATION_EXTERN => (int) $counters[Model::RECLAMATION_EXTERN][0]["count"] / $total,
			Model::RECLAMATION_INTERNAL => (int) $counters[Model::RECLAMATION_INTERNAL][0]["count"] / $total,			
			Model::SUGESTION => (int) $counters[Model::SUGESTION][0]["count"] / $total,
			Model::COMP_BOOK => (int) $counters[Model::COMP_BOOK][0]["count"] / $total,
		];

		return [
			'total' => $total,
			'pie' => $pie,
			'counts' => [
				Model::DENOUNCE => (int) $counters[Model::DENOUNCE][0]["count"],
				Model::COMPLAINT => (int) $counters[Model::COMPLAINT][0]["count"],
				Model::RECLAMATION_EXTERN => (int) $counters[Model::RECLAMATION_EXTERN][0]["count"],
				Model::RECLAMATION_INTERNAL => (int) $counters[Model::RECLAMATION_INTERNAL][0]["count"],
				Model::SUGESTION => (int) $counters[Model::SUGESTION][0]["count"],
				Model::COMP_BOOK => (int) $counters[Model::COMP_BOOK][0]["count"],					
			]
		];
	}

	private function getIncumprimentoByDepartment($start, $end) {
		$complaints = '
			select COUNT(c.type) as count,
				c.type,
				a.codigo as code
			from complaint c
			join app_entity a ON a.id = (select entity from user where id=c.created_by)
			where c.created_at >= :start and c.created_at <= :end
				  and state=:state
			group by c.type,a.codigo
		';

		$sugestions = '
			select COUNT(c.type) as count,
				c.type,
				a.codigo as code
			from sugestion c
			join app_entity a ON a.id = (select entity from user where id=c.created_by)
			where c.created_at >= :start and c.created_at <= :end
				and state=:state
			group by c.type,a.codigo
		';

		$internalRecl = '
			select COUNT(1) as count,
				"reclamacao_interna" as type,
				a.codigo as code
			from reclamation_internal c
			join app_entity a ON a.id = (select entity from user where id=c.created_by)
			where c.created_at >= :start and c.created_at <= :end
				and state=:state
			group by type,a.codigo
		';

		$params = [
			'state' => Stage::NO_CONFOR,
			'start'=>$start,
			'end'=>$end
		];

		$ary1 = $this->fetchAll($complaints, $params);
		$ary2 = $this->fetchAll($sugestions, $params);
		$ary3 = $this->fetchAll($internalRecl, $params);

		$ary = array_merge($ary1, $ary2, $ary3);

		$table = [];
		foreach($ary as $val) {
			$entry = [
				'count' => $val['count'],
				'type' => $val['type']	
			];
			$key = $val['code'];
			if (array_key_exists($key, $table)) {
				$table[$key][] = $entry;
			} else {
				$table[$key] = [$entry];
			}
		}

		return ['rows' => $table];
	}

	private function groupByMonth($year) {
        $em = $this->getDoctrine()->getManager();
		$service = $this->container->get('sga.admin.stats');
		$ary1 = $service->groupByMonth($em, 'complaint');
		$ary2 = $service->groupByMonth($em, 'sugestion');
		$ary3 = $service->groupByMonth($em, 'comp_book', ['type'=>'comp_book']);
		$ary4 = $service->groupByMonth($em, 'reclamation_internal', ['type'=>'reclamacao_interna']);
		$ary = array_merge($ary1, $ary2, $ary3, $ary4);

		$table = [];
		foreach($ary as $val) {
			$entry = [
				'count' => $val['count'],
				'type' => $val['type']	
			];
			$key = $val['period'];
			if (array_key_exists($key, $table)) {
				$table[$key][] = $entry;
			} else {
				$table[$key] = [$entry];
			}
		}

		for ($i=1; $i<13; $i++) {
			$key = "$year-" . str_pad($i, 2, '0', STR_PAD_LEFT);
			if (!array_key_exists($key, $table)) {
				$table[$key] = [];
			}
		}

		$db = ["rows" => $table];	
		return $db;
	}

	private function groupByDepartment($start, $end) {
        $em = $this->getDoctrine()->getManager();
		$service = $this->container->get('sga.admin.stats');

		$ary1 = $service->groupByDepartment($em, 'complaint', ['start'=>$start, 'end'=>$end]);
		$ary2 = $service->groupByDepartment($em, 'sugestion', ['start'=>$start, 'end'=>$end]);
		$ary3 = $service->groupByDepartment($em, 'reclamation_internal', ['type'=>'reclamacao_interna', 'start'=>$start, 'end'=>$end]);
		$ary4 = $service->groupByDepartment($em, 'comp_book', ['type'=>'comp_book', 'start'=>$start, 'end'=>$end]);
		$ary = array_merge($ary1, $ary2, $ary3, $ary4);

		$table = [];
		foreach($ary as $val) {
			$entry = [
				'count' => $val['count'],
				'type' => $val['type']	
			];
			$key = $val['code'];
			if (array_key_exists($key, $table)) {
				$table[$key][] = $entry;
			} else {
				$table[$key] = [$entry];
			}
		}

		return ["rows" => $table];
	}

	public function getAvgResponseTime($start, $end) {
		$em = $this->getDoctrine()->getManager();		
		$service = $this->container->get('sga.admin.stats');

		$ary1 = $service->responseAvg($em, 'complaint', ['type' => Model::DENOUNCE, 'start'=>$start, 'end'=>$end]);
		$ary2 = $service->responseAvg($em, 'complaint', ['type' => Model::COMPLAINT, 'start'=>$start, 'end'=>$end]);
		$ary3 = $service->responseAvg($em, 'reclamation_internal', ['type'=>Model::RECLAMATION_INTERNAL, 'start'=>$start, 'end'=>$end]);
		$ary4 = $service->responseAvg($em, 'sugestion', ['type' => Model::RECLAMATION_EXTERN, 'start'=>$start, 'end'=>$end]);
		$ary5 = $service->responseAvg($em, 'sugestion', ['type' => Model::SUGESTION, 'start'=>$start, 'end'=>$end]);
		$ary6 = $service->responseAvg($em, 'comp_book', ['type' => 'comp_book', 'start'=>$start, 'end'=>$end]);

		$ary = array_merge($ary1, $ary2, $ary3, $ary4, $ary5, $ary6);

		$table = [];
		foreach($ary as $val) {
			$entry = [
				'count' => $val['count'],
				'type' => $val['type']	
			];
			$key = $val['code'];
			if (array_key_exists($key, $table)) {
				$table[$key][] = $entry;
			} else {
				$table[$key] = [$entry];
			}
		}

		return ["rows" => $table];
	}

	public function getThirdPartyCounts($start, $end) {
		$ary = [
			Model::DENOUNCE => 
				(int) $this->count('complaint', ['state'=>Stage::NO_COMP, 'type'=>Model::DENOUNCE, 'start'=>$start, 'end'=>$end])
				[0]['count'],
			Model::COMPLAINT => 
				(int) $this->count('complaint', ['state'=>Stage::NO_COMP, 'type'=>Model::COMPLAINT, 'start'=>$start, 'end'=>$end])
				[0]['count'],
			Model::RECLAMATION_EXTERN => 
				(int) $this->count('sugestion', ['state'=>Stage::NO_COMP, 'type'=>Model::RECLAMATION_EXTERN, 'start'=>$start, 'end'=>$end])
				[0]['count'],
			Model::SUGESTION => 
				(int) $this->count('sugestion', ['state'=>Stage::NO_COMP, 'type'=>Model::SUGESTION, 'start'=>$start, 'end'=>$end])
				[0]['count'],
			// Model::RECLAMATION_INTERNAL => 
			// 	(int) $this->count('reclamation_internal', ['state'=>Stage::NO_COMP])
			// 	[0]['count'],
			// Model::COMP_BOOK => 
			// 	 $this->count('comp_book', ['state' => Stage::NO_COMP])
			// 	 [0]['count'],
		];

		return $ary;
	}

    public function getCounters($start, $end) {
		$ary = [
			Model::DENOUNCE => $this->count('complaint', ['type' => Model::DENOUNCE, 'start'=>$start, 'end'=>$end]),
			Model::COMPLAINT => $this->count('complaint', ['type' => Model::COMPLAINT, 'start'=>$start, 'end'=>$end]),
			Model::RECLAMATION_INTERNAL => $this->count('reclamation_internal', ['start'=>$start, 'end'=>$end]),
			Model::RECLAMATION_EXTERN => $this->count('sugestion', ['type' => Model::RECLAMATION_EXTERN, 'start'=>$start, 'end'=>$end]),
			Model::SUGESTION => $this->count('sugestion', ['type' => Model::SUGESTION, 'start'=>$start, 'end'=>$end]),
			Model::COMP_BOOK => $this->count('comp_book', ['start'=>$start, 'end'=>$end]),	
		];
		return $ary;
	}

    private function count($model, $opts=[]) {
		$em = $this->getDoctrine()->getManager();
		return $this->container
			->get('sga.admin.stats')
			->count($em, $model, $opts);
	}

	private function fetchAll($sql, $params) {
        $em = $this->getDoctrine()->getManager();
		$stmt = $em->getConnection()->prepare($sql);
		$stmt->execute($params);
		return $stmt->fetchAll();
	}
}

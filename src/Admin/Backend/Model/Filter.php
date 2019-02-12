<?php
namespace Admin\Backend\Model;

use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Admin\Backend\Entity\Stage;
use Admin\Backend\Model\Settings;
use \DatePeriod;
use \DateInterval;

class Filter {
    public function __construct(Container $container) {    
        $this->container = $container;
    } 

    /**
     * Common filter used in pagination
     */
    public function from($em, $klass, $limit, $offset, $opts=[]) {
        $builder = $em->createQueryBuilder();
        $q=$builder->select('x')
            ->from($klass, 'x')
            ->orderBy('x.id', 'asc')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        foreach ($opts as $key => $value) {
            $q->where($q->expr()->eq('x.' . $key, "'$value'"));
        }

        $results=$q->getQuery()->getResult();
        $TYPES=[
            'Complaint', 'Sugestion',
            'IReclamation', 'CompBook'
        ];

        $classname=$this->classname($klass);
        if (in_array($classname, $TYPES)) {
            $holidays=$this->fetchHolidays($em);
            foreach ($results as $res) {
                $createdAt=clone $res->getCreatedAt();;
                $days=$this->countDays($holidays, $classname, $createdAt);
                $date = clone $res->getCreatedAt();
                $date->add(new \DateInterval("P".$days."D"));
                $res->setRespDate($date);
            }
        }

        return [$q, $results];
    }

    public function ByCode($em, $code) {
        $pageIdx = !array_key_exists('page', $_GET) ? 1 : $_GET['page'];
        $perPage = 5;
        $codeName='';

        $ary1 = $this->fetchByCode($em, 'sugestion', $code, 'SG');
        $ary2 = $this->fetchByCode($em, 'sugestion', $code, 'RE');
        $ary3 = $this->fetchByCode($em, 'complaint', $code, 'QX');
        $ary4 = $this->fetchByCode($em, 'complaint', $code, 'DN');
        $ary5 = $this->fetchByCode($em, 'comp_book', $code, 'LR');
        $ary5 = $this->fetchByCode($em, 'reclamation_internal', $code, 'RI');
        
        $ary = array_merge($ary1, $ary2, $ary3, $ary4, $ary5);
        return $ary;
    }

    public function ByState($em, $model, $state) {
        $all = $em->getRepository($model)->findAll();
        $today = new \DateTime;
        $batchSize = 20;
        $i = 0;
        foreach ($all as $obj) {
            $responseDate = $obj->getRespDate();
            $state = $obj->getState();

            if ($today > $responseDate && (
                $state == Stage::ACOMPANHAMENTO ||
                $state == Stage::TRATAMENTO)) 
            {
                $obj->setState(Stage::NO_CONFOR);
                $i++;
            }

            if ($i>0 && ($i % $batchSize) === 0) {
                $em->flush();
                $em->clear();
            }
        }

        $em->flush();
        $em->clear();

        $pageIdx = !array_key_exists('page', $_GET) ? 1 : $_GET['page'];
        $perPage = Settings::PER_PAGE;

        $q = $this->from($em, 
            $model, 
            Settings::LIMIT, 
            0, 
            ['state' => $state]
        );

        $fanta = $this->container
            ->get('sga.admin.table.pagination')
            ->fromQuery($q[0], $perPage, $pageIdx);

        $entities = $q[1];
        return [$entities, $fanta];
    }

    private function fetchHolidays($em) {
        $all = $em->getRepository('BackendBundle:Category')->findAll();
        $holies=[];
        foreach ($all as $cat) {
            # code...
            $_dt=explode("/", $cat->getName());
            $dt="{$_dt[2]}-{$_dt[1]}-{$_dt[0]}";
            $holies[]=$dt;
        }
        return $holies;
    }

    private function fetchByCode($em, $model, $codeParam, $codeType) {
        $sql = "
            select * from (
                select *, concat(
                    lpad(id, '3', '0'),
                    '/',
                    :codeType,
                    '/',
                    (select codigo from app_entity where 
                    id=(select entity from user where id=kkk.created_by)),
                    '/',
                    year(created_at)
                ) as code_label,
                :codeType as code_type
            from $model kkk) s1
            where concat('%',code_label,'%') like concat('%',trim(:code),'%')
        ";
        $params=[
            'code'=>$codeParam, 
            'codeType'=>$codeType
        ];
		$stmt = $em->getConnection()->prepare($sql);
		$stmt->execute($params);
		return $stmt->fetchAll();
	}

    private function countDays($holidays, $type, $start) {
        // $start = new DateTime('2019-02-06');
        // $end = new DateTime('2019-02-20');
        // otherwise the  end date is excluded (bug?)
        // $end->modify('+1 day');
        $incr=0;
        if ($type=='Complaint'||$type=='Sugestion'||$type=='IReclamation') {            
            $incr=15;
        } else {
            $incr=10;
        }

        $hs = array_unique($holidays);
        $dt = (clone $start);
        // // var_dump($hs);
        // var_dump(in_array('2018-11-12', $hs));
        // die;
        // $end->modify($incr." day");
        // $interval = $end->diff($start);

        // total days

        // create an iterateable period of date (P1D equates to 1 day)
        //$period = new DatePeriod($start, new DateInterval('P1D'), $end);
        // var_dump($hs);
        // echo "<br>";

        $days = $incr;
        while ($incr >= 0) {
            $curr = $dt->format('D');
            $fmt=$dt->format('Y-m-d');

            if ($curr=='Sat' || $curr=='Sun') {
                // weekend
                $days += 1;
            } elseif (in_array($fmt, $hs)) {
                // holiday
                $days += 1;
            } else {
                // its not a weekend nor a holiday
                $incr -= 1;
            }

            $dt->modify("+1 day");
        }

        return $days;
    }

    private function classname($classname) {
        // code is to be done
        return (substr($classname, strrpos($classname, '\\') + 1));;
    }
}

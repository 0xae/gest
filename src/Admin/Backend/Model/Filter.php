<?php
namespace Admin\Backend\Model;

use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Admin\Backend\Entity\Stage;
use Admin\Backend\Model\Settings;

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

        return $q->getQuery();
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
        $all = $em->getRepository('BackendBundle:' . $model)->findAll();
        $today = new \DateTime;
        $batchSize = 20;
        $i = 0;
        foreach ($all as $obj) {
            $responseDate = $obj->getRespDate();

            if ($today > $responseDate && (
                $obj->getState() == Stage::ACOMPANHAMENTO ||
                $obj->getState() == Stage::TRATAMENTO)) 
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
            'BackendBundle:'.$model, 
            Settings::LIMIT, 
            0, 
            ['state' => $state]
        );

        $fanta = $this->container
            ->get('sga.admin.table.pagination')
            ->fromQuery($q, $perPage, $pageIdx);

        $entities = $q->getResult();
        return [$entities, $fanta];
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
}

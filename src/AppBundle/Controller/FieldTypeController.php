<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use AppBundle\Document\FieldType;

/**
 * Class FieldTypeController
 * @package AppBundle\Controller
 * @Route("/admin/field_types")
 */
class FieldTypeController extends StorageControllerAbstract
{

    /**
     * @param $data
     * @param int $itemId
     * @return array
     */
    public function validateData($data, $itemId = 0)
    {
        if( empty($data) ){
            return ['success' => false, 'msg' => 'Data is empty.'];
        }
        if( empty($data['title']) ){
            return ['success' => false, 'msg' => 'Title is empty.'];
        }
        if( empty($data['name']) ){
            return ['success' => false, 'msg' => 'System name is empty.'];
        }
        if($this->checkNameExists($data['name'], $itemId)){
            return ['success' => false, 'msg' => 'System name already exists.'];
        }

        return ['success' => true];
    }

    /**
     * @param $data
     * @param int $itemId
     * @return array
     */
    public function createUpdate($data, $itemId = 0){

        if(empty($data['inputProperties'])){
            $data['inputProperties'] = [];
        }
        if(empty($data['outputProperties'])){
            $data['outputProperties'] = [];
        }

        if($itemId){
            $item = $this->getRepository()->find($itemId);
            if(!$item){
                return [
                    'success' => false,
                    'msg' => 'Item not found.'
                ];
            }
        } else {
            $item = new FieldType();
        }

        $item
            ->setTitle($data['title'])
            ->setName($data['name'])
            ->setDescription($data['description'])
            ->setInputProperties($data['inputProperties'])
            ->setOutputProperties($data['outputProperties']);

        /** @var \Doctrine\ODM\MongoDB\DocumentManager $dm */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $dm->persist($item);
        $dm->flush();

        return [
            'success' => true,
            'data' => $item->toArray()
        ];
    }

    /**
     * @return \AppBundle\Repository\FieldTypeRepository
     */
    public function getRepository()
    {
        return $this->get('doctrine_mongodb')
            ->getManager()
            ->getRepository('AppBundle:FieldType');
    }

}
<?php
/**
 * Created by PhpStorm.
 * User: aprudnikov
 * Date: 2019-07-31
 * Time: 15:31
 */

namespace App\Controller;

use App\Orm\Finder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use SleekDB\SleekDB;

class ApiController extends AbstractController
{

    protected $finder;

    protected $dataDir = __DIR__.'/../../var/db';

    /**
     * StoreController constructor.
     */
    public function __construct()
    {
        try {
            $storage = SleekDB::store('items', $this->dataDir);
            $this->finder = new Finder($storage);
        } catch (\Exception $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
    }

    /**
     * @Route("/api/get/items", name="api_get_items")
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function getItems(Request $request): Response
    {
        $ids = $request->get('ids');
        $items = [];
        if(!empty($ids)) {
            $items = $this->finder->findByIds($ids);
        }
        return new Response(json_encode($items));
    }
}
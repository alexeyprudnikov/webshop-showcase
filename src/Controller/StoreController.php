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

class StoreController extends AbstractController
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
            #$this->insertTestData();
        } catch (\Exception $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
    }

    /**
     * @Route("/", name="start")
     * @return Response
     * @throws \Exception
     */
    public function start(): Response
    {
        $items = $this->finder->findAll();

        return $this->render('store/index.html.twig', ['items' => $items]);
    }

    /**
     * @Route("/i/{hash}", name="show_item")
     * @param string $hash
     * @return Response
     * @throws \Exception
     */
    public function showItem(string $hash): Response
    {
        $item = $this->finder->findByHash($hash);

        return $this->render('store/item.html.twig', ['item' => $item]);
    }
}
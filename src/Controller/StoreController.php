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

    public static $categories = [
        1 => 'Кольца',
        2 => 'Браслеты',
        3 => 'Серьги'
    ];

    public static $sorting = [
        1 => ['title' => 'Стандартная', 'orderby' => 'desc:_id'],
        2 => ['title' => 'Новинки', 'orderby' => 'desc:isnew'],
        3 => ['title' => 'Дешевые', 'orderby' => 'asc:price'],
        4 => ['title' => 'Дорогие', 'orderby' => 'desc:price']
    ];

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
     * @Route("/", name="start")
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function start(Request $request): Response
    {
        $obId = $request->get('orderby', 1);
        $orderby = $this->getOrderBy($obId);

        $this->finder->setOrderBy($orderby);
        $items = $this->finder->findAll();

        $args = [
            'items' => $items,
            'categories' => self::$categories,
            'sorting' => self::$sorting,
            'catId' => 0,
            'orderbyId' => $obId
        ];

        return $this->render('store/index.html.twig', $args);
    }

    /**
     * @Route("/c/{id}", name="show_category")
     * @param int $id
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function showCategory(int $id, Request $request): Response
    {
        $obId = $request->get('orderby', 1);
        $orderby = $this->getOrderBy($obId);

        $this->finder->setOrderBy($orderby);
        $items = $this->finder->findByCategoryId($id);

        $args = [
            'items' => $items,
            'categories' => self::$categories,
            'sorting' => self::$sorting,
            'catId' => $id,
            'orderbyId' => $obId
        ];

        return $this->render('store/index.html.twig', $args);
    }

    /**
     * @Route("/i/{hash}", name="show_item")
     * @param string $hash
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function showItem(string $hash, Request $request): Response
    {
        $isAjax = $request->isXmlHttpRequest();

        $item = $this->finder->findByHash($hash);

        $template = $isAjax ? 'item.html.twig' : 'itemfullpage.html.twig';

        $args = [
            'item' => $item,
            'categories' => self::$categories,
            'isAjax' => $isAjax
        ];

        return $this->render('store/' . $template, $args);
    }

    /**
     * @param int $id
     * @return string
     */
    protected function getOrderBy(int $id): string
    {
       return array_key_exists($id, self::$sorting) ? self::$sorting[$id]['orderby']: 'desc:_id';
    }

    /**
     * @Route("/wishlist", name="show_wishlist")
     * @param Request $request
     * @return Response
     */
    public function showWishList(Request $request): Response
    {
        $message = '';
        if($request->get('a') === 'send') {
            $message = 'Запрос отправлен';
        }
        $args = [
            'message' => $message,
            'clear' => $request->get('clear') === '1' ? 1 : 0
        ];
        return $this->render('store/wishlist.html.twig', $args);
    }

    /**
     * @Route("/wishlist/items", name="api_get_items")
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function getWishListItems(Request $request): Response
    {
        $ids = $request->get('ids');
        $items = [];
        if(!empty($ids)) {
            $items = $this->finder->findByIds($ids);
        }
        return $this->render('store/wishlistitems.html.twig', ['items' => $items]);
    }

    /**
     * @Route("/send", name="send_request")
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function send(Request $request): Response
    {
        $ids = $request->get('ids');
        $name = $request->get('name');
        $email = $request->get('email');
        $isCopy = $request->get('iscopy') === '1' ? 1 : 0;

        $items = [];
        if(!empty($ids)) {
            $items = $this->finder->findByIds($ids);
        }

        // send mail

        // send copy

        // redirect
        $args = [
            'a' => 'send'
        ];

        if($request->get('isclear') === '1') {
            $args['clear'] = 1;
        }

        return $this->redirectToRoute('show_wishlist', $args);
    }
}
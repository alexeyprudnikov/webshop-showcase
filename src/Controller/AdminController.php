<?php
/**
 * Created by PhpStorm.
 * User: aprudnikov
 * Date: 2019-07-31
 * Time: 15:31
 */

namespace App\Controller;

use App\Orm\Finder;
use App\Orm\Manager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use SleekDB\SleekDB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AdminController extends AbstractController
{
    protected $finder;

    protected $manager;

    protected $dataDir = __DIR__.'/../../var/db';

    protected $imgDir = 'images';

    /**
     * AdminController constructor.
     */
    public function __construct()
    {
        try {
            $storage = SleekDB::store('items', $this->dataDir);
            $this->finder = new Finder($storage);
            $this->manager = new Manager($storage);
        } catch (\Exception $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
    }

    /**
     * @Route("/admin", name="admin")
     * @return Response
     * @throws \Exception
     */
    public function list(): Response
    {
        $args = [];

        $items = $this->finder->findAll();
        $args['items'] = $items;

        return $this->render('admin/index.html.twig', $args);
    }

    /**
     * @Route("/admin/item/update", name="update_item", methods={"POST"})
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function update(Request $request): Response
    {
        $id = $request->get('id');
        $title = $request->get('title');
        $number = $request->get('number');
        $price = $request->get('price');

        if(empty($id) || empty($title) || empty($number) || empty($price)) {
            throw new \UnexpectedValueException('Data not complete');
        }
        $update = [
            'title' => $title,
            'number' => $number,
            'price' => $price,
            'category' => $request->get('category'),
            #'sort' => 1
        ];

        $this->manager->update($id, $update);

        // refind and return rendered template
        $item = $this->finder->findById($id);
        return $this->render('admin/item.html.twig', ['item' => $item]);
    }

    /**
     * @Route("/admin/item/create", name="create_item")
     * @return Response
     */
    public function create(): Response
    {
        return $this->render('admin/add.html.twig');
    }

    /**
     * @Route("/admin/item/insert", name="insert_item", methods={"POST"})
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function insert(Request $request): Response
    {
        $file = $request->files->get('file');
        $title = $request->get('title');
        $number = $request->get('number');
        $price = $request->get('price');

        if(empty($file) || empty($title) || empty($number) || empty($price)) {
            throw new \UnexpectedValueException('Data not complete');
        }

        $filename = $this->uploadFile($file);
        $count = $this->finder->count();
        
        $insert = [
            'filename' => $filename,
            'hash' => $this->getUniqueHash(),
            'title' => $title,
            'number' => $number,
            'price' => $price,
            'category' => $request->get('category'),
            'sort' => $count+1
        ];

        $item = $this->manager->insert($insert);

        return new Response(json_encode($item));
    }

    /**
     * @param UploadedFile $file
     * @return string
     */
    protected function uploadFile(UploadedFile $file): string
    {
        $fileName = $this->getUniqueHash().'.'.$file->guessExtension();

        $file->move($this->imgDir, $fileName);

        return $fileName;
    }

    /**
     * @param int $length
     * @return bool|string
     */
    protected function getUniqueHash(int $length = 12)
    {
        return substr(str_shuffle(md5(microtime()).strtoupper(md5(microtime()))), 0, $length);
    }
}
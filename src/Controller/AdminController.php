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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class AdminController extends AbstractController
{
    /** @var SessionInterface $session */
    private $session;

    protected $finder;

    protected $manager;

    protected $dataDir = __DIR__.'/../../var/db';

    protected $imgDir = 'images/items';

    protected $secretKey = 'znamenka';

    /**
     * AdminController constructor.
     * @param SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        try {
            $this->session = $session;
            $storage = SleekDB::store('items', $this->dataDir);
            $this->finder = new Finder($storage);
            $this->manager = new Manager($storage);
        } catch (\Exception $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
    }

    /**
     * @Route("/admin/login", name="login")
     * @param Request $request
     * @return Response
     */
    public function login(Request $request): Response
    {
        if ($this->session->get('authenticated') === true) {
            return $this->redirectToRoute('list');
        }
        $error = '';
        $secret = $request->get('secret');
        if (md5($secret) === md5($this->secretKey)) {
            $this->session->set('authenticated', true);
            return $this->redirectToRoute('list');
        }
        if ($secret !== null) {
            $error = 'Error: wrong secret key!';
        }
        return $this->render('admin/login.html.twig',
            [
                'error' => $error
            ]
            );
    }

    /**
     * @Route("/admin/logout", name="logout")
     * @return RedirectResponse
     */
    public function logout(): RedirectResponse
    {
        $this->session->remove('authenticated');
        return $this->redirectToRoute('login');
    }

    /**
     * @Route("/admin", name="list")
     * @return Response
     * @throws \Exception
     */
    public function list(): Response
    {
        if ($this->session->get('authenticated') !== true) {
            return $this->redirectToRoute('login');
        }

        $args = [];

        $items = $this->finder->findAll();
        $args['items'] = $items;

        return $this->render('admin/list.html.twig', $args);
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
        $file = $request->files->get('file');
        $title = $request->get('title');
        $number = $request->get('number');
        $price = $request->get('price');

        if(empty($id) || empty($title) || empty($number) || empty($price)) {
            return new Response('Data not complete', 422);
        }

        $update = [
            'title' => $title,
            'number' => $number,
            'price' => $price,
            'category' => $request->get('category'),
            'isnew' => $request->get('isnew') === '1' ? 1 : 0
        ];

        if(!empty($file)) {
            $update['filename'] = $this->uploadFile($file);
            // find old filename
            $item = $this->finder->findById($id);
            // delete old image
            $this->deleteImage($item['filename']);
        }

        $this->manager->update($id, $update);

        // refind and return rendered template
        $item = $this->finder->findById($id);
        return $this->render('admin/item.html.twig', ['item' => $item]);
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
            return new Response('Data not complete', 422);
        }
        
        $insert = [
            'filename' => $this->uploadFile($file),
            'hash' => $this->getUniqueHash(),
            'title' => $title,
            'number' => $number,
            'price' => $price,
            'category' => $request->get('category'),
            'isnew' => $request->get('isnew') === '1' ? 1 : 0
        ];

        $item = $this->manager->insert($insert);

        return new Response(json_encode($item));
    }

    /**
     * @Route("/admin/item/delete", name="delete_item", methods={"POST"})
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function delete(Request $request): Response
    {
        $id = $request->get('id');

        if(empty($id)) {
            throw new \UnexpectedValueException('Data not complete');
        }

        $item = $this->finder->findById($id);

        $this->manager->delete($id);

        // delete image
        $this->deleteImage($item['filename']);

        return new Response();
    }

    /**
     * @param string $filename
     */
    protected function deleteImage(string $filename): void
    {
        unlink($this->imgDir . DIRECTORY_SEPARATOR . $filename);
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
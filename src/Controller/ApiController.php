<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Exception\GuzzleException;

class ApiController extends AbstractController
{
    /**
     * @var GuzzleHttpClient
     */
    protected $HttpClient;
    protected $sid; // session id for Stadtplan Api

    public function __construct()
    {
        if($this->HttpClient === null && $this->sid === null) {
            $this->initClient();
        }
    }

    /**
     *
     */
    public function initClient(): void
    {
        $this->HttpClient = new GuzzleHttpClient(['base_uri' => 'https://www.stadtplandienst.de/spdxml']);

        try {
            $requestUri = '/getsid.aspx';
            $response = $this->HttpClient->request('GET', $requestUri, [
                'form_params' => [
                    'user' => 'neusta'
                ]
            ]);
            var_dump($response);
        } catch (GuzzleException $e) {
            echo $e->getMessage();
        }
    }

    /**
     * @Route("/api/getCity", name="getCity", methods={"GET"})
     * @param Request $request
     * @return Response
     */
    public function getCity(Request $request): Response
    {
        $city = $request->query->get('city');

        try {
            $requestUri = '/findcity.aspx';
            $response = $this->HttpClient->request('GET', $requestUri, [
                'form_params' => [
                    'sid' => $this->sid,
                    'country' => 'de',
                    'city' => $city
                ]
            ]);
            var_dump($response);
        } catch (GuzzleException $e) {
            echo $e->getMessage();
        }

        return new Response($city);
    }

    /**
     * @Route("/api/getAddress", name="getAddress", methods={"GET"})
     * @param Request $request
     * @return Response
     */
    public function getAddress(Request $request): Response
    {
        $citykey = $request->query->get('citykey');
        $address = $request->query->get('address');

        try {
            $requestUri = '/findaddress.aspx';
            $response = $this->HttpClient->request('GET', $requestUri, [
                'form_params' => [
                    'sid' => $this->sid,
                    'citykey' => $citykey,
                    'address' => $address
                ]
            ]);
            var_dump($response);
        } catch (GuzzleException $e) {
            echo $e->getMessage();
        }

        return new Response($address);
    }

    /**
     * @Route("/api/getMapForPrint", name="getMapForPrint", methods={"GET"})
     * @param Request $request
     * @return Response
     */
    public function getMapByAddressForPrint(Request $request): Response
    {
        $citykey = $request->query->get('citykey');
        $addressid = $request->query->get('addressid');

        try {
            $requestUri = '/getmapbyaddressforprint.aspx';
            $response = $this->HttpClient->request('GET', $requestUri, [
                'form_params' => [
                    'sid' => $this->sid,
                    'citykey' => $citykey,
                    'addressid' => $addressid,
                    'scale' => 'dedatlas',
                    'w' => 500,
                    'h' => 500
                ]
            ]);
            var_dump($response);
        } catch (GuzzleException $e) {
            echo $e->getMessage();
        }

        return new Response($addressid);
    }
}

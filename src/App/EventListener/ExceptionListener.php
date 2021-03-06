<?php

namespace App\EventListener;

use App\Controller\CatalogController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ExceptionListener
{
    protected $twig;
    protected $container;

    public function __construct(\Twig_Environment $twig, ContainerInterface $container)
    {
        $this->twig = $twig;
        $this->container = $container;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        $request = $event->getRequest();
        $environment = $this->container->get('kernel')->getEnvironment();
        $message = '';

        if ($request->get('_route') == 'setup'
            || strpos($request->get('_route'), 'omnipay_') !== false) {
                return;
        }

        $headers = [];
        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
            $headers = $exception->getHeaders();
        } else {
            $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        }
        if ($environment === 'dev') {
            $message = $exception->getMessage();
        }

        if($request->isXmlHttpRequest()) {
            $content = [
                'error' => $message ?: 'Not found.',
                'statusCode' => $statusCode
            ];
        } else {

            //$catalogController = new CatalogController();
            //$catalogController->setContainer($this->container);
            //$categoriesTopLevel = $catalogController->getCategoriesTree();

            $content = $this->twig->render('/errors/404.html.twig', [
                'message' => $message,
                'currentUri' => '404'
            ]);
        }

        if (is_array($content)) {
            $response = new JsonResponse($content);
        } else {
            $response = new Response($content);
        }
        $response->setStatusCode($statusCode);
        if (!empty($headers)) {
            $response->headers->replace($headers);
        }

        $event->setResponse($response);
    }
}

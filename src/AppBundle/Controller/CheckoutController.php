<?php

namespace AppBundle\Controller;

use AppBundle\Document\Category;
use AppBundle\Document\FileDocument;
use AppBundle\Document\Order;
use AppBundle\Document\OrderContent;
use AppBundle\Document\Setting;
use AppBundle\Document\User;
use AppBundle\Events;
use AppBundle\Repository\CategoryRepository;
use AppBundle\Service\SettingsService;
use AppBundle\Service\ShopCartService;
use AppBundle\Service\UtilsService;
use Doctrine\ODM\MongoDB\Cursor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use AppBundle\Form\Type\OrderType;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/checkout")
 */
class CheckoutController extends BaseController
{

    /**
     * @Route("", name="page_checkout")
     * @param Request $request
     * @param UtilsService $utilsService
     * @param TranslatorInterface $translator
     * @param EventDispatcherInterface $eventDispatcher
     * @return RedirectResponse|Response
     */
    public function checkoutAction(Request $request, UtilsService $utilsService, TranslatorInterface $translator, EventDispatcherInterface $eventDispatcher)
    {
        /** @var User $user */
        $user = $this->getUser();
        /** @var SettingsService $settingsService */
        $settingsService = $this->get('app.settings');
        $settings = $settingsService->getAll();

        $currency = ShopCartService::getCurrency();

        $order = new Order();
        if ($user) {
            $order
                ->setEmail($user->getEmail())
                ->setFullName($user->getFullName())
                ->setPhone($user->getPhone())
                ->setOptions($user->getOptions());
        }

        $form = $this->createForm(OrderType::class, $order, [
            'choiceDelivery' => isset($settings[Setting::GROUP_DELIVERY])
                ? $settings[Setting::GROUP_DELIVERY]
                : [],
            'choicePayment' => isset($settings[Setting::GROUP_PAYMENT])
                ? $settings[Setting::GROUP_PAYMENT]
                : [],
            'currency' => $currency,
            'noDeliveryFirst' => true
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var Setting $delivery */
            $delivery = $form->get('deliveryName')->getNormData();
            $deliveryPrice = $delivery ? $delivery->getOption('price') : 0;

            /** @var Setting $payment */
            $payment = $form->get('paymentName')->getNormData();
            $paymentName = $payment ? $payment->getOption('value') : '';

            /** @var ShopCartService $shopCartService */
            $shopCartService = $this->get('app.shop_cart');
            $shopCartData = $shopCartService->getContent();
            if (empty($shopCartData)) {
                $form->addError(new FormError('Your cart is empty.'));
            }
            if ($form->isValid()) {

                $statusName = !empty($settings[Setting::GROUP_ORDER_STATUSES])
                    ? $settings[Setting::GROUP_ORDER_STATUSES][0]->getName()
                    : '';

                $order
                    ->setDeliveryPrice($deliveryPrice)
                    ->setPaymentValue($paymentName)
                    ->setContentFromCart($shopCartData)
                    ->setStatus($statusName)
                    ->setCurrency($currency);

                // Save user data
                if ($user) {
                    $order->setUserId($user->getId());
                    if (!$user->getFullName()) {
                        $user->setFullName($order->getFullName());
                    }
                    if (!$user->getPhone()) {
                        $user->setPhone($order->getPhone());
                    }

                    $optionsData = $form->get('options')->getData();
                    $userOptions = $user->getOptions();

                    if (empty($userOptions)) {
                        $userOptions = [];
                    }
                    foreach ($optionsData as $option) {
                        $index = array_search($option['name'], array_column($userOptions, 'name'));
                        if ($index === false) {
                            $userOptions[] = $option;
                        } else {
                            $userOptions[$index]['value'] = $option['value'];
                        }
                    }
                    $user->setOptions($userOptions);
                }

                /** @var \Doctrine\ODM\MongoDB\DocumentManager $dm */
                $dm = $this->get('doctrine_mongodb')->getManager();
                $dm->persist($order);
                $dm->flush();

                // Save order files
                $this->saveOrderFiles($order);

                // Dispatch event
                $event = new GenericEvent($order);
                $eventDispatcher->dispatch(Events::ORDER_CREATED, $event);

                $shopCartService->clearContent();
                $utilsService->orderSendMail(
                    $this->getParameter('app_name') . ' - ' . $translator->trans('mail_subject.new_order'),
                    $order
                );

                $request->getSession()
                    ->getFlashBag()
                    ->add('messages', 'Thanks for your order!');

                return $this->redirectToRoute('page_checkout_success');
            }
        }

        return $this->render('page_checkout.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @param Order $order
     * @return bool
     */
    public function saveOrderFiles(Order $order)
    {
        $orderId = $order->getId();
        $orderContent = $order->getContent();

        /** @var \Doctrine\ODM\MongoDB\DocumentManager $dm */
        $dm = $this->get('doctrine_mongodb')->getManager();

        /** @var OrderContent $item */
        foreach ($orderContent as $item) {
            $files = $item->getFiles();
            if (empty($files)) {
                continue;
            }
            foreach ($files as $file) {
                /** @var FileDocument $fileDocument */
                $fileDocument = $this->getFileRepository()->findOneBy([
                    'id' => $file['fileId'],
                    'ownerType' => FileDocument::OWNER_ORDER_TEMPORARY
                ]);
                if ($fileDocument) {
                    $fileDocument
                        ->setOwnerType(FileDocument::OWNER_ORDER_PRODUCT)
                        ->setOwnerId($orderId);

                    $dm->flush();
                }
            }
        }

        return true;
    }

    /**
     * @return \AppBundle\Repository\FileDocumentRepository
     */
    public function getFileRepository()
    {
        return $this->get('doctrine_mongodb')
            ->getManager()
            ->getRepository(FileDocument::class);
    }

    /**
     * @Route("/success", name="page_checkout_success")
     * @param Request $request
     * @return Response
     */
    public function checkoutSuccessAction(Request $request)
    {


        return $this->render('page_checkout_success.html.twig', [

        ]);
    }
}
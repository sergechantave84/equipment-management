<?php

namespace App\Subscriber;

use App\Entity\Equipement;
use App\Exception\BaseException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Security;

class ControlePathSubscriber implements EventSubscriberInterface
{
    private $container;
    private $router;
    private $security;
    private $auth;

    /**
     * @param ContainerInterface $container
     * @param RouterInterface $router
     * @param Security $security
     * @param AuthorizationCheckerInterface $checker
     */
    public function __construct(
        ContainerInterface $container,
        RouterInterface $router,
        Security $security,
        AuthorizationCheckerInterface $checker
    ) {
        $this->container = $container;
        $this->router = $router;
        $this->security = $security;
        $this->auth = $checker;
    }

    /**
     * @param RequestEvent $event
     *
     * @throws BaseException
     */
    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        $route = $request->get('_route');
        if (in_array($route, ['api_users_GET_item', 'api_users_PUT_item'])) {
            $user = $this->security->getUser();
            if ($user instanceof Equipement) {
                if ($user->getId() !== $request->attributes->get('id')) {
                    throw new BaseException(
                        $user,
                        'You have not access this ressource',
                        Response::HTTP_UNAUTHORIZED
                    );
                }
            }
        }
    }

    /**
     * @param ResponseEvent $event
     */
    public function onKernelResponse(ResponseEvent $event)
    {
        $request = $event->getRequest();
        if ($request->getUri() == 'http://weboconsulting.org'
            || $request->getUri() == 'http://weboconsulting.org/'
        ) {
            $response = new RedirectResponse($this->router->generate('accueil'));
            $event->setResponse($response);
        } else if ($request->getUri() == 'https://weboconsulting.org'
            || $request->getUri() == 'https://weboconsulting.org/'
            || $request->getUri() == 'https://weboconsulting.legtux.org'
            || $request->getUri() == 'https://weboconsulting.legtux.org/'
            || $request->getUri() == 'https://www.weboconsulting.org'
            || $request->getUri() == 'https://www.weboconsulting.org/'
            || $request->getUri() == 'https://www.weboconsulting.legtux.org'
            || $request->getUri() == 'https://www.weboconsulting.legtux.org/'
            || $request->getUri() == 'http://weboconsulting.legtux.org'
            || $request->getUri() == 'http://weboconsulting.legtux.org/'
            || $request->getUri() == 'http://www.weboconsulting.org'
            || $request->getUri() == 'http://www.weboconsulting.org/'
            || $request->getUri() == 'http://www.weboconsulting.legtux.org'
            || $request->getUri() == 'http://www.weboconsulting.legtux.org/'
        ) {
            $response = new RedirectResponse('https://weboconsulting.org/public/accueil');
            $event->setResponse($response);
        }
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            // must be registered before (i.e. with a higher priority than) the default Locale listener
            KernelEvents::REQUEST => array(array('onKernelRequest', 20)),
            KernelEvents::RESPONSE => array(array('onKernelResponse', 20)),
        );
    }
}

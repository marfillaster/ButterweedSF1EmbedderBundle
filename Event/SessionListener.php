<?php
namespace Butterweed\SF1EmbedderBundle\Event;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Butterweed\SF1EmbedderBundle\User\GuardUserInterface;

class SessionListener implements ContainerAwareInterface
{
    protected $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function onPreContext()
    {
        $session = $this->container->get('session');
        if ($session->isStarted()) {
            $session->save();
        }
    }

    public function onPreDispatch(ContextEvent $event)
    {
        $context = $this->container->get('security.context');
        if ($context->getToken()) {
            $sfUser = $event->getContext()->getUser();
            $user = $context->getToken()->getUser();
            if ($sfUser instanceof \sfGuardSecurityUser && $user instanceof GuardUserInterface) {
                if ($context->isGranted('IS_AUTHENTICATED_FULLY')) {
                    if ($sfUser->isAuthenticated()) {
                        if (!$user->equalsGuard($sfUser)) {
                            $sfUser->signOut();
                            $sfUser->signIn($user->getGuardUser());
                        }
                    } else {
                        $sfUser->signIn($user->getGuardUser());
                    }
                } else {
                    $sfUser->signOut();
                }
            }
        }
    }

    public function onPostDispatch(ContextEvent $event)
    {
        $event->getContext()->getUser()->shutdown();
    }
}
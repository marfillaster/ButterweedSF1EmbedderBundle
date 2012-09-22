<?php
namespace Butterweed\SF1EmbedderBundle\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Butterweed\SF1EmbedderBundle\User\GuardUserInterface;

class SessionSubscriber implements EventSubscriberInterface, ContainerAwareInterface
{
    protected $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    static public function getSubscribedEvents()
    {
        return array(
            'butterweed_sf1_embedder.pre_context' => array('onPreContext', 0),
            'butterweed_sf1_embedder.pre_dispatch' => array('onPreDispatch', 0),
            'butterweed_sf1_embedder.post_dispatch' => array('onPostDispatch', 0),
        );
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
        if ($this->container->has('security_context')) {
            $sfUser = $event->getContext()->getUser();
            $user = $this->container->get('security_context')->getToken()->getUser();

            if ($sfUser instanceof \sfGuardSecurityUser && $user instanceof GuardUserInterface) {
                if ($user->isAuthenticated()) {
                    if ($sfUser->isAuthenticated()) {
                        if (!$user->equalsGuard($sfUser)) {
                            $sfUser->signOut();
                            $sfUser->signIn($user->getGuardUsername());
                        }
                    } else {
                        $sfUser->signIn($user->getGuardUsername());
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
# symfony 1.x Embedder Bundle

This bundle embedds symfony applications inside a Symfony2 application.
It will allow developers who want to port their legacy projects to Symfony2 in an
iterative manner by providing automatic fallback of requests. Take note that there
will be some runtime overhead.

This is still beta and currently only tested(manually) on symfony 1.2.

In order for this to work, a symfony plugin must be installed in the legacy app.

## Features

* Supports multiple applications from multiple projects (provided they are all accessible locally on disk).
* Automatically matches debug and env settings.
* Auto signin of sfuser and even supports user switching.
* All symfony exception will be handled by Symfony2.
* Both symfony and Symfony2 debug toolbar shows!

## Installation

Add a requirement for `butterweed/sf1-embedder-bundle` to your
composer.json and add the bundle in your AppKernel.php

    new Symfony\Cmf\Bundle\RoutingExtraBundle\SymfonyCmfRoutingExtraBundle(),
    new Butterweed\SF1EmbedderBundle\ButterweedSF1EmbedderBundle()


## Configuration

    # config.yml
    butterweed_sf1_embedder:
        map:
            main:
                prefix: /  # uses strpos to match againts pathinfo
                app: frontend
                path: "%kernel.root_dir%/../legacy"

    symfony_cmf_routing_extra:
        chain:
            routers_by_id:
                router.default: 100
                butterweed_sf1_embedder.router: 0



## Auto signin of sfGuardUser

Fos user and custom implementations are supported. Automatic signin happens
when `GuardUserInterface` is implemented on the User model. `security.context` token is required.

    interface GuardUserInterface
    {
        /**
         * return \sfGuardUser
         */
        public function getGuardUser();

        /**
         * return boolean
         */
        public function equalsGuard(\sfGuardSecurityUser $user);
    }

## Making symfony embed aware

The objective of this bundle is to not touch the legacy app as much as possible
given the fact that the main task is porting to the newer version. The embedded app
can remain as a separated project. However, there are internal changes that needs to be applied.
Luckily, symfony is very pluggable via factories.yml and filters.yml. This bundle provides a symfony plugin
that has the custom classes.

To install plugin, copy or better symlink `butterweedEmbeddedAwarePlugin` located in `Resources/extra` into the plugins directory the hook up the new classes.

    # apps/frontend/config/filters.yml
    rendering:
        class: EmbeddedAwareRenderingFilter

    # apps/frontend/config/factories.yml
    controller:
        class: EmbeddedAwareFrontWebController

That's it! You may need to `./symfony cc --env=prod`.

## Gothas

Assets may not be served from symfony especially when mouting it on root prefix `/`.
You will have to symlink or copy the web dir into a subdirectory and use redirection.

    # Example
    cd <project-path>/web
    ln -s <project-path>/legacy/web legacy

    # nginx conf
    location ~ ^/(?!legacy).+\.(gif|css|js|png|jpeg|jpg)$ {
        try_files $uri /legacy$uri;
    }






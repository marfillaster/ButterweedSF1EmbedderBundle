<?php

namespace Butterweed\SF1EmbedderBundle\User;

interface GuardUserInterface
{
    /**
     * return \sfGuardUser
     */
    public function getGuardUser();

    /**
     * return boolean
     */
    public function equalsGuard($user);
}
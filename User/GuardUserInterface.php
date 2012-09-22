<?php

use Butterweed\SF1EmbedderBundle\User;

interface GuardUserInterface
{
    public function getGuardUsername();

    public function equalsGuard(\sfGuardSecurityUser $user);
}
<?php

namespace addon\shop\app\upgrade\v648;


class Upgrade
{

    public function handle()
    {
        $Upgrade = new \app\upgrade\v156\Upgrade();
        $Upgrade->handle();
    }

}

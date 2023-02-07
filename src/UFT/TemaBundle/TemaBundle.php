<?php

namespace UFT\TemaBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class TemaBundle extends Bundle
{
    public function getParent()
    {
        return 'AvanzuAdminThemeBundle';
    }
}

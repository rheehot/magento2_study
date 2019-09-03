<?php

namespace SimplifiedMagento\FirstModule\NotMangento;

use SimplifiedMagento\FirstModule\NotMangeto\PencilInterface;

class BigPencil implements PencilInterface
{

    public function getPencilType()
    {
       return "Big Pencil";
    }
}
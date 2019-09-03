<?php


namespace SimplifiedMagento\FirstModule\NotMangento;


use SimplifiedMagento\FirstModule\NotMangeto\PencilInterface;

class YellowPencil implements PencilInterface
{

    public function getPencilType()
    {
        return "Yellow Pencil";
    }

}
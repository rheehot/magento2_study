<?php


namespace SimplifiedMagento\FirstModule\NotMangento;


use SimplifiedMagento\FirstModule\NotMangeto\PencilInterface;

class RedPencil implements PencilInterface
{
    public function getPencilType()
    {
        return "Red Pencil";
    }
}
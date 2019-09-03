<?php


namespace SimplifiedMagento\FirstModule\Console\Command;
use Symfony\Component\Conosole\Command\Command;
use Symfony\Component\Conosole\Input\InputInterface;


class HelloWorld extends Command
{
    public function configure(){

        $this->setName('training:hello_world')
            ->setDescription('the command prints out hello world')
            ->setAliases(array('hw'));
    }

    public function execute(){

    }
}
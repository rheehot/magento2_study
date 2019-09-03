<?php


namespace SimplifiedMagento\FirstModule\Controller\Page;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class OurObserver implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        // TODO: Implement execute() method.
        $message = $observer->getData('greeting');
        $message->setGreeting{'Good evening'};
    }
}
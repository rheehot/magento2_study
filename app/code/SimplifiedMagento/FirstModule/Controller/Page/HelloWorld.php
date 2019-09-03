<?php

//use Magento\Framework\App\ResponseInterface;
namespace SimplifiedMagento\FirstModule\Controller\Page;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use SimplifiedMagento\FirstModule\NotMagento\PencilInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use SimplifiedMagento\FirstModule\Model\PencilFactory;
use Magento\Framework\App\Http;
use SimplifiedMagento\FirstModule\Model\HeavyService;

class HelloWorld extends \Magento\Framework\App\Action\Action
{

    protected  $pencilInterface;
    protected $productRepository;
    protected $productFactory;
    protected $_eventManager;
    protected $http;
    protected $heavyService;

    public function __construct(Context $context,
                                heavyService $heavyService,
                                http $http,
                                PencilInterface $pencilInterface,
                                _eventManager $_eventManager,
                                ProductRepositoryInterface $productRepository)
    {
//        $this->pencilInterface
        $this->pencilInterface = $pencilInterface;
        $this->productRepository = $productRepository;
        $this->_eventManager = $_eventManager;
        $this->heavyService = $heavyService;

        parent::__construct($context);
    }

    public function execute()
    {
//        echo "Hello World";
//        echo $this->pencilInterface->getPencilType();
//        echo get_class($this->productRepository);
          $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
//          $pencil = $this->pencilInterface->create(array("name"=>"Bob", "school"=>"Internation School"));
//          var_dump($pencil);
//         $product = $this->productFactory->create()->load(1);
//         $product->setName("iphone 8XR");
//         $productName = $product->getName();
//         echo $productName;

//        $message = new \Magento\Framework\DataObject(array(("greeting"=>"Good Afternoon"));
//        $this->_eventManager->dispatch('custom_event',['greeting'=>$message]);
//        echo $message->getGreeting();

        $id = $this->http->http->getParam('id', 0);
        if($id==1){
            $this->heavyService->printHeavyServiceMessage();
        }else{
            echo "Heavy service not used";
        }
    }
}
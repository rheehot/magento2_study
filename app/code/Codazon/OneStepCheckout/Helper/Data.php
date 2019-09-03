<?php

namespace Codazon\OneStepCheckout\Helper;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address\Renderer;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_TRANS_EMAIL_GENERAL_EMAIL = 'trans_email/ident_general/email';

    const XML_PATH_TRANS_EMAIL_GENERAL_NAME = 'trans_email/ident_general/name';

    protected $_storeManager;
	
    protected $_subscriberFactory;
    
    protected $_objectManager;

    protected $_giftMessageFactory;

    protected $_transportBuilder;

    protected $_addressRenderer;

    protected $_paymentHelperData;
	
    protected $inlineTranslation;

    protected $_checkoutSession;

    protected $_priceCurrency;

    protected $_configHelper;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        Renderer $addressRenderer,
        \Magento\Payment\Helper\Data $paymentHelperData,
        \Codazon\OneStepCheckout\Helper\Config $configHelper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\GiftMessage\Model\MessageFactory $giftMessageFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
    )
    {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->_subscriberFactory = $subscriberFactory;
        $this->_transportBuilder = $transportBuilder;
        $this->_paymentHelperData = $paymentHelperData;
        $this->_addressRenderer = $addressRenderer;
        $this->_configHelper = $configHelper;
        $this->_objectManager = $objectManager;
        $this->_giftMessageFactory = $giftMessageFactory;
        $this->inlineTranslation = $inlineTranslation;
        $this->_checkoutSession = $checkoutSession;
        $this->_priceCurrency = $priceCurrency;
    }

    public function addSubscriber($email)
    {
        if ($email) {
            $subscriberModel = $this->_subscriberFactory->create()->loadByEmail($email);
            if ($subscriberModel->getId() === null) {
                try {
                    $this->_subscriberFactory->create()->subscribe($email);
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->_objectManager->get('Psr\Log\LoggerInterface')->notice($e->getMessage());
                } catch (\Exception $e) {
                    $this->_objectManager->get('Psr\Log\LoggerInterface')->notice($e->getMessage());
                }

            } elseif ($subscriberModel->getData('subscriber_status') != 1) {
                $subscriberModel->setData('subscriber_status', 1);
                try {
                    $subscriberModel->save();
                } catch (\Exception $e) {
                    $this->_objectManager->get('Psr\Log\LoggerInterface')->notice($e->getMessage());
                }
            }
        }
    }
	
    protected function getPaymentHtml(Order $order, $storeId)
    {
        return $this->_paymentHelperData->getInfoBlockHtml(
            $order->getPayment(),
            $storeId
        );
    }

    protected function getFormattedShippingAddress($order)
    {
        return $order->getIsVirtual()
            ? null
            : $this->_addressRenderer->format($order->getShippingAddress(), 'html');
    }

    protected function getFormattedBillingAddress($order)
    {
        return $this->_addressRenderer->format($order->getBillingAddress(), 'html');
    }

    public function sendNewOrderEmail(\Magento\Sales\Model\Order $order)
    {
        $storeId = $order->getStore()->getId();
        if ($this->_configHelper->isEnableSendEmailAdmin()) {
            $emailArray = explode(',', $this->_configHelper->notifyToEmail());
            $sendTo = [];
            if (!empty($emailArray)) {
                foreach ($emailArray as $email) {
                    $sendTo[] = ['email' => trim($email), 'name' => ''];
                }
            }
            $this->inlineTranslation->suspend();
            foreach ($sendTo as $recipient) {
                try {
                    $transport = $this->_transportBuilder->setTemplateIdentifier(
                        $this->_configHelper->getEmailTemplate()
                    )->setTemplateOptions(
                        ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId]
                    )->setTemplateVars(
                        [
                            'order'                    => $order,
                            'billing'                  => $order->getBillingAddress(),
                            'payment_html'             => $this->getPaymentHtml($order, $storeId),
                            'store'                    => $order->getStore(),
                            'formattedShippingAddress' => $this->getFormattedShippingAddress($order),
                            'formattedBillingAddress'  => $this->getFormattedBillingAddress($order),
                        ]
                    )->setFrom(
                        [
                            'email' => $this->scopeConfig->getValue(
                                self::XML_PATH_TRANS_EMAIL_GENERAL_EMAIL,
                                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                                $storeId
                            ),
                            'name'  => $this->scopeConfig->getValue(
                                self::XML_PATH_TRANS_EMAIL_GENERAL_NAME,
                                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                                $storeId
                            ),
                        ]
                    )->addTo(
                        $recipient['email'],
                        $recipient['name']
                    )->getTransport();
                    $transport->sendMessage();
                } catch (\Magento\Framework\Exception\MailException $ex) {
                    $this->_objectManager->get('Psr\Log\LoggerInterface')->notice($ex->getMessage());
                }
            }
            $this->inlineTranslation->resume();
        }
    }

    public function isContainDownloadableProduct()
    {
        if ($this->scopeConfig->isSetFlag('catalog/downloadable/disable_guest_checkout')) {
            $quote = $this->getOnepage()->getQuote();
            foreach ($quote->getAllItems() as $item) {
                if (($product = $item->getProduct())
                    && $product->getTypeId() == \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE
                ) {
                    return true;
                }
            }
        }

        return false;
    }
	
    public function getQuote()
    {
        if (empty($this->_quote)) {
            $this->_quote = $this->_checkoutSession->getQuote();
        }

        return $this->_quote;
    }
}
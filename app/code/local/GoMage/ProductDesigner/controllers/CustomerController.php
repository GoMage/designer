<?php
require_once Mage::getModuleDir('controllers', 'Mage_Customer') . DS . "AccountController.php";

class GoMage_ProductDesigner_CustomerController extends Mage_Customer_AccountController
{
    /**
     * Dispatch action
     *
     * @param string $action Action name
     * @return void
     */
    public function dispatch($action)
    {
        $moduleEnabled = Mage::getStoreConfig('gmpd/general/enabled', Mage::app()->getStore());
        if (!$moduleEnabled) {
            $action = 'noRoute';
        }
        parent::dispatch($action);
    }

    /**
     * Login customer and save images
     *
     * @return void
     */
    public function loginPostAction()
    {
        $request = $this->getRequest();
        $isAjax = $request->isAjax();
        if (!$isAjax) {
            $this->norouteAction();
            return;
        }

        try {
            $session = $this->_getSession();
            $login = $this->getRequest()->getParam('login');
            if (!$session->isLoggedIn()) {
                if (!empty($login['username']) && !empty($login['password'])) {
                    $session->login($login['username'], $login['password']);
                    $customer = $session->getCustomer();
                    if ($customer->getIsJustConfirmed()) {
                        $customer->sendNewAccountEmail('confirmed', '', Mage::app()->getStore()->getId());
                    }
                    $design = Mage::helper('designer')->saveProductDesignedImages();
                    Mage::helper('designer/ajax')->sendSuccess(array(
                        'welcome_text' => $this->_getWelcomeTextHtml(),
                        'top_links' => $this->_getTopLinksHtml(),
                        'design_id' => $design->getId()
                    ));
                } else {
                    throw new Exception($this->__('Login and password are required.'));
                }
            }
        } catch (Exception $e) {
            Mage::helper('designer/ajax')->sendError($e->getMessage());
        } catch (Mage_Core_Exception $e) {
            switch ($e->getCode()) {
                case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
                    $value = Mage::helper('customer')->getEmailConfirmationUrl($login['username']);
                    $message = Mage::helper('customer')->__('This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', $value);
                    break;
                case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
                    $message = $e->getMessage();
                    break;
                default:
                    $message = $e->getMessage();
                    Mage::helper('designer/ajax')->sendError($message);
            }
        }
    }

    /**
     * Create customer and save images
     *
     * @return void
     */
    public function createPostAction()
    {
        $request = $this->getRequest();
        $isAjax = $request->isAjax();
        $session = $this->_getSession();
        if (!$isAjax) {
            $this->norouteAction();
            return;
        }

        if ($session->isLoggedIn()) {
            Mage::helper('designer/ajax')->sendSuccess();
            return;
        }
        $session->setEscapeMessages(true); // prevent XSS injection in user input
        if ($this->getRequest()->isPost()) {
            $errors = array();

            if (!$customer = Mage::registry('current_customer')) {
                $customer = Mage::getModel('customer/customer')->setId(null);
            }
            /* @var $customerForm Mage_Customer_Model_Form */
            $customerForm = Mage::getModel('customer/form');
            try {
                $customerErrors = $this->_prepareCustomerData($customer, $customerForm);
                $addressErrors = $this->_prepareCustomerAddress($customer);

                if ($addressErrors !== true) {
                    $errors = array_merge($errors, $result);
                }
                if ($customerErrors !== true) {
                    $errors = array_merge($customerErrors, $errors);
                }

                $validationResult = count($errors) == 0;

                if (true === $validationResult) {
                    $errors = $this->_createCustomer($customer);
                }

                if ($errors !== true) {
                    if (is_array($errors)) {
                        Mage::helper('designer/ajax')->sendError($errors);
                    } else {
                        Mage::helper('designer/ajax')->sendError($this->__($this->__('Invalid customer data')));
                    }
                } else {
                    $design = Mage::helper('designer')->saveProductDesignedImages();
                }
                if ($customer->isConfirmationRequired()) {
                    Mage::helper('designer/ajax')->sendRedirect(array(
                        'url' => Mage::getUrl('customer/account/index')
                    ));
                    $session->addSuccess($this->__('Design successful save'));
                } else {
                    $responseData = array(
                        'welcome_text' => $this->_getWelcomeTextHtml(),
                        'top_links' => $this->_getTopLinksHtml()
                    );
                    if (isset($design)) {
                        $responseData['design_id'] = $design->getId();
                    }
                    Mage::helper('designer/ajax')->sendSuccess($responseData);
                }

            } catch (Mage_Core_Exception $e) {
                $session->setCustomerFormData($this->getRequest()->getPost());
                if ($e->getCode() === Mage_Customer_Model_Customer::EXCEPTION_EMAIL_EXISTS) {
                    $url = Mage::getUrl('customer/account/forgotpassword');
                    $message = $this->__('There is already an account with this email address. If you are sure that it is your email address, <a href="%s">click here</a> to get your password and access your account.', $url);
                } else {
                    $message = $e->getMessage();
                }
                Mage::helper('designer/ajax')->sendError($message);
            } catch (Exception $e) {
                Mage::helper('designer/ajax')->sendError($e->getMessage());
            }
        }
    }

    /**
     * Prepare customer data from request
     *
     * @param Mage_Customer_Model_Customer $customer     Customer
     * @param Mage_Customer_Model_Form     $customerForm Customer form
     * @return bool|array
     */
    protected function _prepareCustomerData($customer, $customerForm)
    {
        $customerForm->setFormCode('customer_account_create')
            ->setEntity($customer);
        $customerData = $customerForm->extractData($this->getRequest());

        if ($this->getRequest()->getParam('is_subscribed', false)) {
            $customer->setIsSubscribed(1);
        }
        $customer->getGroupId();

        $errors = $customerForm->validateData($customerData);
        if ($errors === true) {
            $customerForm->compactData($customerData);
            return true;
        } else {
            return $errors;
        }
    }

    /**
     * Prepare customer request
     *
     * @param Mage_Customer_Model_Customer $customer customer
     * @return array|bool
     */
    protected function _prepareCustomerAddress($customer)
    {
        $errors = array();
        if ($this->getRequest()->getParam('create_address')) {
            /* @var $address Mage_Customer_Model_Address */
            $address = Mage::getModel('customer/address');
            /* @var $addressForm Mage_Customer_Model_Form */
            $addressForm = Mage::getModel('customer/form');
            $addressForm->setFormCode('customer_register_address')
                ->setEntity($address);

            $addressData    = $addressForm->extractData($this->getRequest(), 'address', false);
            $addressErrors  = $addressForm->validateData($addressData);
            if ($addressErrors === true) {
                $address->setId(null)
                    ->setIsDefaultBilling($this->getRequest()->getParam('default_billing', false))
                    ->setIsDefaultShipping($this->getRequest()->getParam('default_shipping', false));
                $addressForm->compactData($addressData);
                $customer->addAddress($address);

                $addressErrors = $address->validate();
                if (is_array($addressErrors)) {
                    $errors = array_merge($errors, $addressErrors);
                }
            } else {
                $errors = array_merge($errors, $addressErrors);
            }
        }

        if (empty($errors)) {
            return true;
        }

        return $errors;
    }

    /**
     * Create customer
     *
     * @param Mage_Customer_Model_Customer $customer Customer
     * @return array|bool
     */
    protected function _createCustomer($customer)
    {
        $session = $this->_getSession();
        $errors = array();
        $customer->setPassword($this->getRequest()->getPost('password'));
        $customer->setConfirmation($this->getRequest()->getPost('confirmation'));
        $customerErrors = $customer->validate();
        if (is_array($customerErrors)) {
            $errors = array_merge($customerErrors, $errors);
            return $errors;
        } else {
            $customer->save();
            Mage::dispatchEvent('customer_register_success',
                array('account_controller' => $this, 'customer' => $customer)
            );

            if ($customer->isConfirmationRequired()) {
                $customer->sendNewAccountEmail(
                    'confirmation',
                    '',
                    Mage::app()->getStore()->getId()
                );
                $session->addSuccess($this->__('Account confirmation is required. Please, check your email for the confirmation link. To resend the confirmation email please <a href="%s">click here</a>.', Mage::helper('customer')->getEmailConfirmationUrl($customer->getEmail())));
            } else {
                $session->setCustomerAsLoggedIn($customer);
                $this->_welcomeCustomer($customer);
            }

            return true;
        }
    }

    /**
     * Return top links html for logged in customer
     *
     * @return string
     */
    protected function _getTopLinksHtml()
    {
        $layout = $this->getLayout();
        $update = $layout->getUpdate();
        $update->load('customer_logged_in_top_links');
        $layout->generateXml();
        $layout->generateBlocks();
        return $layout->getOutput();
    }

    /**
     * Return welcome text for customer
     *
     * @return string
     */
    protected function _getWelcomeTextHtml()
    {
        $block = $this->getLayout()->createBlock('page/html_header');
        return $block->getWelcome() . $block->getAdditionalHtml();
    }

    public function designsAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($this->__('My Saved Designs'));
        if ($block = $this->getLayout()->getBlock('customer.account.link.back')) {
            $block->setRefererUrl($this->_getRefererUrl());
        }
        $this->renderLayout();
    }

    public function deleteDesignAction()
    {
        $session = $this->_getSession();
        $designId = (int) $this->getRequest()->getParam('design_id');
        if (!$session->isLoggedIn()) {
            $session->addError($this->__('Please, login'));
            $this->_redirect('');
            return;
        }

        try {
            if ($designId) {
                $design = Mage::getModel('gmpd/design')->load($designId);
                if ($design && $design->getId()) {
                    if ($session->getCustomerId() == $design->getCustomerId()) {
                        $design->delete();
                    }
                }
            }
            $this->_redirectReferer('*/*/designs');
        } catch (Exception $e) {
            $session->addError($e->getMessage());
            $this->_redirect('');
        }
    }

    /**
     * Identify referer url via all accepted methods (HTTP_REFERER, regular or base64-encoded request param)
     *
     * @return string
     */
    protected function _getRefererUrl()
    {
        $refererUrl = $this->getRequest()->getServer('HTTP_REFERER');
        Mage::log($refererUrl);
        if ($url = $this->getRequest()->getParam(self::PARAM_NAME_REFERER_URL)) {
            $refererUrl = $url;
        }
        if ($url = $this->getRequest()->getParam(self::PARAM_NAME_BASE64_URL)) {
            $refererUrl = Mage::helper('core')->urlDecode($url);
        }
        if ($url = $this->getRequest()->getParam(self::PARAM_NAME_URL_ENCODED)) {
            $refererUrl = Mage::helper('core')->urlDecode($url);
        }

        if (!$this->_isUrlInternal($refererUrl)) {
            $refererUrl = Mage::app()->getStore()->getBaseUrl();
        }
        return $refererUrl;
    }
}
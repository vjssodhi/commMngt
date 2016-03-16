<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Authentication\AuthenticationService;
use Zend\Session\Container;
use Application\Model\AgentORM;
use Zend\Mvc\MvcEvent;
use Application\Form\AgentAdd;
use Application\Utilities\NumberPlay;
use Application\Exception\AuthenticationRequired;
use Zend\Json\Json;
use Zend\Http\Request;
use Application\Entity\Agent;
use Application\Form\AgentPayments;

class AgentController extends AbstractActionController
{

    /**
     *
     * @var AuthenticationService
     */
    protected $empAuthServiceService;

    /**
     *
     * @var AgentORM
     */
    protected $modelAccessor;

    /**
     *
     * @var Container
     */
    protected $userInfoContainer;

    public function onDispatch(MvcEvent $e)
    {
        $this->userInfoContainer = new Container(USER_INFO_CONTAINER_NAME);
        $this->modelAccessor = $this->getServiceLocator()->get('AgentModel');
        $empAuthServiceService = $this->getServiceLocator()->get('EmpAuthService');
        $this->empAuthServiceService = $empAuthServiceService;
        return parent::onDispatch($e);
    }

    public function indexAction()
    {
        $totalCommission = 0;
        $event = $this->getEvent();
        $application = $event->getApplication();
        $router = $this->serviceLocator->get('Router');
        $routeMatch = $router->match($this->request);
        $routeMatchParams = $routeMatch->getParams();
        if (empty($routeMatchParams['agentId'])) {
            return $this->redirect()->toUrl('agent/list');
        } else {
            
            $agentId = $routeMatchParams['agentId'];
            /* @var $agent Agent */
            
            $agent = $this->modelAccessor->fetchAll(array(
                'id' => $agentId
            ))[0];
            if (empty($agent)) {
                $errorMessage = 'Forbidden Resource';
                $event->setError(ERROR_NEED_AUTHENTICATED_USER);
                $event->setParam('exception', new AuthenticationRequired($errorMessage));
                $event->setParam('redirectUri', '/');
                return $application->getEventManager()->trigger(MvcEvent::EVENT_DISPATCH_ERROR, $event);
            }
            $agentEmail = $agent['emailId'];
            $agentsByEmail = $agent = $this->modelAccessor->fetchAll(array(
                'emailId' => $agentEmail
            ));
            $agentDetails = array();
            
            foreach ($agentsByEmail as $key => $agentInfo) {
                if (! isset($agentDetails['emails']) || ! is_array($agentDetails['emails'])) {
                    $agentDetails['emails'] = array();
                }
                if (! in_array($agentInfo['emailId'], $agentDetails['emails'])) {
                    $agentDetails['emails'][] = $agentInfo['emailId'];
                }
                if (! isset($agentDetails['mobiles']) || ! is_array($agentDetails['mobiles'])) {
                    $agentDetails['mobiles'] = array();
                }
                if (! in_array($agentInfo['mobile'], $agentDetails['mobiles'])) {
                    $agentDetails['mobiles'][] = $agentInfo['mobile'];
                }
                
                if (! isset($agentDetails['addresses']) || ! is_array($agentDetails['addresses'])) {
                    $agentDetails['addresses'] = array();
                }
                if (! in_array($agentInfo['address'], $agentDetails['addresses'])) {
                    $agentDetails['addresses'][] = $agentInfo['address'];
                }
                if (! isset($agentDetails['institutes']) || ! is_array($agentDetails['institutes'])) {
                    $agentDetails['institutes'] = array();
                }
                $institueInfo = $this->modelAccessor->getInstituteModel()->fetchAll(array(
                    'id' => $agentInfo['instituteId']
                ))[0];
                $institues[$agentInfo['instituteId']] = $institueInfo;
                $agentDetails['institutes'][] = $institueInfo;
                $students = $this->modelAccessor->fetchStudents(array(
                    'agentId' => $agentInfo['id']
                ));
                if (! empty($students)) {
                    if (! isset($agentDetails['students']) || ! is_array($agentDetails['students'])) {
                        $agentDetails['students'] = array();
                    }
                    foreach ($students as $student) {
                        $agentDetails['students'][] = $student;
                        $cmsToPePaid = $student['commissionToBePaidByInstitute'];
                        $courseFeeCurrency = $student['feeCurrency'];
                        $totalCommission = $totalCommission + $cmsToPePaid;
                    }
                }
                $agentDetails['name'] = $agentInfo['name'];
            }
            $test = NumberPlay::getAlphaNumericPassword(6);
            $container = new Container('transaction');
            if (empty($container->transactionSentinel)) {
                $container->transactionSentinel = $test;
            }
            $paymens = $this->modelAccessor->fetchAgentPaymentInfo(array(
                'emailId' => $agentEmail
            ));
            $paidAmount = 0;
            if (! empty($paymens)) {
                foreach ($paymens as $paymen) {
                    $p = intval($paymen['paidAmmount'], 10);
                    $paidAmount = $paidAmount + $p;
                }
            }
            
            $form = new AgentPayments($container->transactionSentinel);
            if ($this->getRequest()->isPost()) {
                $data = $this->getRequest()->getPost();
                $form->setData($data);
                if ($form->isValid()) {
                    $errors = false;
                    $formData = $form->getData();
                    $newpaidAmount = $paidAmount + intval($formData['paymentAmount'], 10);
                    if ($newpaidAmount > $totalCommission) {
                        $form->get('paymentAmount')->setMessages(array(
                            sprintf('By paying %s, the net payment Amount becomes %s. Net payment cannot be greater than: %s', $formData['paymentAmount'], $newpaidAmount, $totalCommission)
                        ));
                        $errors = true;
                    }
                    if ($paidAmount >= $totalCommission) {
                        $message = 'All payment has been paid for this agent';
                    }
                    if ($formData['verifyAction'] !== $container->transactionSentinel) {
                        $form->get('verifyAction')->setMessages(array(
                            'Please recheck the transaction password'
                        ));
                        $errors = true;
                    }
                    if (! $errors) {
                        unset($container->transactionSentinel);
                        $this->modelAccessor->updatePayment($agentEmail, $totalCommission, $formData['paymentAmount']);
                        return $this->redirect()->toRoute('agent/details', array(
                            'agentId' => $agentId
                        ));
                    }
                } else {
                    var_dump($formData);
                    die();
                }
            }
            $vm = new ViewModel(array(
                'transactionPassword' => $container->transactionSentinel,
                'form' => $form,
                'totalCommission' => $totalCommission,
                'agentDetails' => $agentDetails,
                'institutes' => $institues,
                'paidAmount' => $paidAmount,
                'courseFeeCurrency' => $courseFeeCurrency
            ));
            $vmMenu = new ViewModel();
            $vmMenu->setTemplate('application/menu/adminSideMenu');
            $vm->addChild($vmMenu, 'adminSideMenu');
            return $vm;
        }
    }

    public function getagentsAction()
    {
        $event = $this->getEvent();
        $application = $event->getApplication();
        $router = $this->serviceLocator->get('Router');
        $routeMatch = $router->match($this->request);
        $routeMatchParams = $routeMatch->getParams();
        $instituteId = $routeMatchParams['instituteId'];
        
        $existingInstitute = $this->modelAccessor->getInstituteModel()->fetchAll(array(
            'id' => $instituteId
        ), null, true)[0];
        if (empty($existingInstitute)) {
            $errorMessage = 'Forbidden Resource';
            $event->setError(ERROR_NEED_AUTHENTICATED_USER);
            $event->setParam('exception', new AuthenticationRequired($errorMessage));
            $event->setParam('redirectUri', '/user/dashboard');
            return $application->getEventManager()->trigger(MvcEvent::EVENT_DISPATCH_ERROR, $event);
        }
        $agentOptions = array();
        $allAgents = $this->modelAccessor->fetchAll(array(
            'instituteId' => $instituteId
        ));
        if (! empty($allAgents)) {
            foreach ($allAgents as $agent) {
                $cmsPercentage = $agent['commissionPercentage'];
                $str = $agent['name'] . '(' . $agent['emailId'] . ', Default Commission: ' . $cmsPercentage . '%)';
                $agentOptions[$agent['id']] = $str;
            }
        } else {
            $agentOptions[0009] = 'No agents registered so far';
        }
        return $this->getResponse()->setContent(Json::encode($agentOptions));
    }

    private function test($agentEmail)
    {
        $agentsByEmail = $this->modelAccessor->fetchAll(array(
            'emailId' => $agentEmail
        ));
        $agentDetails = array();
        $totalCommission = 0;
        foreach ($agentsByEmail as $key => $agentInfo) {
            $students = $this->modelAccessor->fetchStudents(array(
                'agentId' => $agentInfo['id']
            ));
            if (! empty($students)) {
                foreach ($students as $student) {
                    $cmsToPePaid = $student['commissionToBePaidByInstitute'];
                    $totalCommission = $totalCommission + $cmsToPePaid;
                }
            }
        }
        $paymens = $this->modelAccessor->fetchAgentPaymentInfo(array(
            'emailId' => $agentEmail
        ));
        $paidAmount = 0;
        if (! empty($paymens)) {
            foreach ($paymens as $paymen) {
                $p = intval($paymen['paidAmmount'], 10);
                $paidAmount = $paidAmount + $p;
            }
        }
        $in = array(
            'totalAmount' => $totalCommission,
            'paidAmount' => $paidAmount
        );
        return $in;
    }

    public function listAction()
    {
        ini_set('xdebug.var_display_max_depth', - 1);
        ini_set('xdebug.var_display_max_children', - 1);
        ini_set('xdebug.var_display_max_data', - 1);
        $event = $this->getEvent();
        $application = $event->getApplication();
        $router = $this->serviceLocator->get('Router');
        $routeMatch = $router->match($this->request);
        $routeMatchParams = $routeMatch->getParams();
        $request = $this->getRequest();
        $allAgents = array();
        $instituteInfo = array();
        if (! empty($routeMatchParams['instituteId'])) {
            
            $instituteId = $routeMatchParams['instituteId'];
            $existingInstitute = $this->modelAccessor->getInstituteModel()->fetchAll(array(
                'id' => $instituteId
            ), null, true)[0];
            if (empty($existingInstitute)) {
                $errorMessage = 'Forbidden Resource';
                $event->setError(ERROR_NEED_AUTHENTICATED_USER);
                $event->setParam('exception', new AuthenticationRequired($errorMessage));
                $event->setParam('redirectUri', '/user/dashboard');
                return $application->getEventManager()->trigger(MvcEvent::EVENT_DISPATCH_ERROR, $event);
            }
            $instituteInfo[$existingInstitute->getId()] = array(
                'name' => $existingInstitute->getName(),
                'country' => $existingInstitute->getCountry()
            );
            $allAgents = $this->modelAccessor->fetchAll(array(
                'institute' => $existingInstitute
            ), null, null, null);
        } else {
            $allAgents = $this->modelAccessor->fetchAll(null, null, null, null);
        }
        $emails = array();
        $formattedInfo = array();
        if (! empty($allAgents)) {
            foreach ($allAgents as $kty => $info) {
                $agentInstituteId = $info['instituteId'];
                $agentEmail = $info['emailId'];
                if (empty($emails[$agentEmail])) {
                    $emails[$agentEmail] = $info['id'];
                }
                if (! array_key_exists($agentEmail, $formattedInfo) || ! is_array($formattedInfo[$agentEmail])) {
                    $formattedInfo[$agentEmail] = array();
                }
                $formattedInfo[$agentEmail]['name'] = $info['name'];
                $formattedInfo[$agentEmail]['mobile'] = $info['mobile'];
                $formattedInfo[$agentEmail]['institutes'][] = $info;
                $formattedInfo[$agentEmail]['paymentRecord'] = $this->test($agentEmail);
                $agentInstitute = $this->modelAccessor->getInstituteModel()->fetchAll(array(
                    'id' => $agentInstituteId
                ))[0];
                $instituteInfo[$agentInstitute['id']] = $agentInstitute;
            }
        }
        $data = array(
            'allAgents' => $formattedInfo,
            'instituteInfo' => $instituteInfo
        );
        $vm = new ViewModel(array(
            'data' => $data
        ));
        $vmMenu = new ViewModel();
        $vmMenu->setTemplate('application/menu/adminSideMenu');
        $vm->addChild($vmMenu, 'adminSideMenu');
        return $vm;
    }

    public function addAction()
    {
        $event = $this->getEvent();
        $request = $this->getRequest();
        $application = $event->getApplication();
        $router = $this->serviceLocator->get('Router');
        $routeMatch = $router->match($this->request);
        $routeMatchParams = $routeMatch->getParams();
        $instituteId = $routeMatchParams['instituteId'];
        $existingInstitute = $this->modelAccessor->getInstituteModel()->fetchAll(array(
            'id' => $instituteId
        ))[0];
        if (empty($existingInstitute)) {
            $errorMessage = 'Forbidden Resource';
            $event->setError(ERROR_NEED_AUTHENTICATED_USER);
            $event->setParam('exception', new AuthenticationRequired($errorMessage));
            $event->setParam('redirectUri', '/user/dashboard');
            return $application->getEventManager()->trigger(MvcEvent::EVENT_DISPATCH_ERROR, $event);
        }
        $form = new AgentAdd();
        $errors = false;
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $data = NumberPlay::cleaner($data);
            $form->setData($data);
            if ($form->isValid()) {
                $formData = NumberPlay::cleaner($form->getData());
                if (! $errors) {
                    $statuses = $this->modelAccessor->register($instituteId, $formData);
                    if (is_array($statuses)) {
                        $form->setMessages($statuses);
                    } else {
                        if (! empty($request->getQuery())) {
                            $query = $request->getQuery();
                            if (! empty($query->get(REDIRECT_PARAM_NAME))) {
                                $destination = $query->get(REDIRECT_PARAM_NAME);
                                $request = new Request();
                                $request->setUri($destination);
                                $router = $this->getEvent()->getRouter();
                                $match = $router->match($request);
                                if (empty($match)) {
                                    $destination = null;
                                }
                            }
                        }
                        if (empty($destination)) {
                            return $this->redirect()->toRoute('agent/list', array(
                                'instituteId',
                                $instituteId
                            ));
                        } else {
                            return $this->redirect()->toUrl($destination);
                        }
                    }
                } else {
                    if (ENABLE_DEBUG_MODE) {
                        var_dump($formData);
                        var_dump($form->getMessages());
                    }
                }
            } else {
                if (ENABLE_DEBUG_MODE) {
                    echo "form is invalid";
                    var_dump($form->getMessages());
                }
            }
        }
        $view = new ViewModel(array(
            'form' => $form,
            'institute' => $existingInstitute
        ));
        $vmMenu = new ViewModel();
        $vmMenu->setTemplate('application/menu/adminSideMenu');
        $view->addChild($vmMenu, 'adminSideMenu');
        return $view;
    }

    public function updateAction()
    {
        $event = $this->getEvent();
        $application = $event->getApplication();
        $router = $this->serviceLocator->get('Router');
        $routeMatch = $router->match($this->request);
        $routeMatchParams = $routeMatch->getParams();
        $agentId = $routeMatchParams['agentId'];
        $existingAgent = $this->modelAccessor->fetchAll(array(
            'id' => $agentId
        ))[0];
        if (empty($existingAgent)) {
            $errorMessage = 'Forbidden Resource';
            $event->setError(ERROR_NEED_AUTHENTICATED_USER);
            $event->setParam('exception', new AuthenticationRequired($errorMessage));
            $event->setParam('redirectUri', '/user/dashboard');
            return $application->getEventManager()->trigger(MvcEvent::EVENT_DISPATCH_ERROR, $event);
        }
        $agentInstis = $this->modelAccessor->fetchAll(array(
            'emailId' => $existingAgent['emailId']
        ));
        //
        $form = new AgentAdd();
        $form->setData($existingAgent);
        $errors = false;
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $data = NumberPlay::cleaner($data);
            $form->setData($data);
            if ($form->isValid()) {
                $formData = NumberPlay::cleaner($form->getData());
                if (! $errors) {
                    $statuses = array();
                    $statuses = $this->modelAccessor->update($agentId, $formData);
                    if (is_array($statuses)) {
                        $form->setMessages($statuses);
                    } else {
                        return $this->redirect()->toRoute('agent/list');
                    }
                } else {
                    if (ENABLE_DEBUG_MODE) {
                        var_dump($formData);
                        var_dump($form->getMessages());
                    }
                }
            } else {
                if (ENABLE_DEBUG_MODE) {
                    echo "form is invalid";
                    var_dump($form->getMessages());
                }
            }
        }
        $existingInstitute = $this->modelAccessor->getInstituteModel()->fetchAll(array(
            'id' => $existingAgent['instituteId']
        ))[0];
        $view = new ViewModel(array(
            'form' => $form,
            'institute' => $existingInstitute
        ));
        $view->setTemplate('application/agent/add');
        $vmMenu = new ViewModel();
        $vmMenu->setTemplate('application/menu/adminSideMenu');
        $view->addChild($vmMenu, 'adminSideMenu');
        return $view;
    }
}

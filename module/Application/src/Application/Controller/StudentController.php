<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Authentication\AuthenticationService;
use Zend\Session\Container;
use Application\Model\StudentORM;
use Zend\Mvc\MvcEvent;
use Application\Utilities\NumberPlay;
use Application\Exception\AuthenticationRequired;
use Application\Form\StudentAdd;
use Zend\I18n\View\Helper\CurrencyFormat;
use Application\Entity\Student;
use Application\Entity\Programme;
use Application\Entity\Institute;
use Application\Entity\Agent;

class StudentController extends AbstractActionController
{

    /**
     *
     * @var AuthenticationService
     */
    protected $empAuthServiceService;

    /**
     *
     * @var StudentORM
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
        $this->modelAccessor = $this->getServiceLocator()->get('StudentModel');
        $empAuthServiceService = $this->getServiceLocator()->get('EmpAuthService');
        $this->empAuthServiceService = $empAuthServiceService;
        return parent::onDispatch($e);
    }

    public function indexAction()
    {
        $event = $this->getEvent();
        $application = $event->getApplication();
        $router = $this->serviceLocator->get('Router');
        $routeMatch = $router->match($this->request);
        $routeMatchParams = $routeMatch->getParams();
        if (empty($routeMatchParams['studentId'])) {
            return $this->redirect()->toUrl('agent/list');
        } else {
            
            $studentId = $routeMatchParams['studentId'];
            /* @var $student Student */
            $student = $this->modelAccessor->fetchAll(array(
                'id' => $studentId
            ), null, true)[0];
            if (empty($student)) {
                $errorMessage = 'Forbidden Resource';
                $event->setError(ERROR_NEED_AUTHENTICATED_USER);
                $event->setParam('exception', new AuthenticationRequired($errorMessage));
                $event->setParam('redirectUri', '/');
                return $application->getEventManager()->trigger(MvcEvent::EVENT_DISPATCH_ERROR, $event);
            }
            /* @var $agent Agent */
            $agent = $student->getAgent();
            /* @var $programme Programme */
            $programme = $student->getProgramme();
            /* @var $institute Institute */
            $institute = $programme->getInstitute();
            $breakDowns = $this->modelAccessor->fetchStudentFeeBreakDown(array(
                'studentId' => $studentId
            ));
            $structs = $this->modelAccessor->getInstituteModel()->fetchStructures(array(
                'instituteId' => $institute->getId()
            ));
            $structOptions = array();
            foreach ($structs as $structInfo) {
                $eleId = $structInfo['id'];
                $structOptions[$eleId] = array(
                    'name' => $structInfo['name'],
                    'amount' => $structInfo['amount']
                );
            }
            $vm = new ViewModel(array(
                'student' => $student,
                'agent' => $agent,
                'programme' => $programme,
                'institute' => $institute,
                'breakDowns' => $breakDowns,
                'structOptions' => $structOptions
            ));
            $vmMenu = new ViewModel();
            $vmMenu->setTemplate('application/menu/adminSideMenu');
            $vm->addChild($vmMenu, 'adminSideMenu');
            return $vm;
        }
    }

    public function addAction()
    {
        $event = $this->getEvent();
        $application = $event->getApplication();
        $router = $this->serviceLocator->get('Router');
        $routeMatch = $router->match($this->request);
        $routeMatchParams = $routeMatch->getParams();
        $instituteId = $routeMatchParams['instituteId'];
        $institute = $this->modelAccessor->getInstituteModel()->fetchAll(array(
            'id' => $instituteId
        ))[0];
        if (empty($institute)) {
            $errorMessage = 'Forbidden Resource';
            $event->setError(ERROR_NEED_AUTHENTICATED_USER);
            $event->setParam('exception', new AuthenticationRequired($errorMessage));
            $event->setParam('redirectUri', '/user/dashboard');
            return $application->getEventManager()->trigger(MvcEvent::EVENT_DISPATCH_ERROR, $event);
        }
        $agentId = $routeMatchParams['agentId'];
        $agent = $this->modelAccessor->getAgentModel()->fetchAll(array(
            'id' => $agentId
        ))[0];
        if (empty($agent)) {
            $errorMessage = 'Forbidden Resource';
            $event->setError(ERROR_NEED_AUTHENTICATED_USER);
            $event->setParam('exception', new AuthenticationRequired($errorMessage));
            $event->setParam('redirectUri', '/user/dashboard');
            return $application->getEventManager()->trigger(MvcEvent::EVENT_DISPATCH_ERROR, $event);
        }
        $commissions = array();
        $programmes = $this->modelAccessor->getProgrammeModel()->fetchAll(array(
            'instituteId' => $instituteId
        ));
        $agents = $this->modelAccessor->getAgentModel()->fetchAll(array(
            'instituteId' => $instituteId
        ));
        $structs = $this->modelAccessor->getInstituteModel()->fetchStructures(array(
            'instituteId' => $instituteId,
            'enabled' => 1
        ));
        
        $hasProgrammes = false;
        $programmeFees = array();
        
        if (! empty($programmes)) {
            $hasProgrammes = true;
        }
        $agentOptions = array();
        $hasAgents = false;
        if (! empty($agents)) {
            $hasAgents = true;
        }
        $hasFeeStructs = false;
        if (! empty($structs)) {
            $hasFeeStructs = true;
        }
        if (! $hasAgents || ! $hasProgrammes || ! $hasFeeStructs) {
            $view = new ViewModel(array(
                'hasAgents' => $hasAgents,
                'hasProgrammes' => $hasProgrammes,
                'hasFeeStructs' => $hasFeeStructs,
                'institute' => $institute,
                'agent' => $agent
            ));
            $vmMenu = new ViewModel();
            $vmMenu->setTemplate('application/menu/adminSideMenu');
            $view->addChild($vmMenu, 'adminSideMenu');
            return $view;
        }
        
        $cmsPercentage = $agent['commissionPercentage'];
        foreach ($programmes as $programmeX) {
            $fees = $programmeX['feeAmount'];
            $feeCurrency = $programmeX['feeCurrency'];
            $strFees = $fees . ' ' . $feeCurrency;
            $commsn = (($cmsPercentage / 100) * ($fees));
            $cmsStr = $commsn . ' ' . $feeCurrency;
            $helperStr = '(Fees:' . $strFees . ', Commission:' . $cmsStr . ')';
            $programmeOptions[$programmeX['id']] = $programmeX['name'] . $helperStr;
            $programmeFees[$programmeX['id']] = array(
                'fees' => $fees,
                'currency' => $feeCurrency,
                'commission' => $commsn
            );
        }
        $structOptions = array();
        $prefill = array();
        foreach ($structs as $structInfo) {
            $structOptions['cmp---' . $structInfo['id']] = array(
                'name' => $structInfo['name'],
                'amount' => $structInfo['amount']
            );
            $prefill['cmp---' . $structInfo['id']] = $structInfo['amount'];
        }
        $form = new StudentAdd($programmeOptions, $structOptions);
        $form->setData($prefill);
        $errors = false;
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $data = NumberPlay::cleaner($data);
            $form->setData($data);
            if ($form->isValid()) {
                $formData = NumberPlay::cleaner($form->getData());
                //
                $birthDay = $formData['birthDay'];
                $birthMonth = $formData['birthMonth'];
                $birthYear = $formData['birthYear'];
                //
                if (! checkdate($birthMonth, $birthDay, $birthYear)) {
                    $message = 'The date is invalid';
                    $form->get('birthDay')->setMessages(array(
                        $message
                    ));
                    $errors = true;
                } else {
                    $formData['dateOfBirth'] = mktime(0, 0, 0, $birthMonth, $birthDay, $birthYear);
                }
                $currencyformat = $this->getServiceLocator()
                    ->get('ViewHelperManager')
                    ->get('currencyformat');
                // $currencyformat->setLocale('en_US');
                //
                
                $selectedPrgId = $formData['programmeId'];
                $prgCurrency = $programmeFees[$selectedPrgId]['currency'];
                $slctdPrgFees = $programmeFees[$selectedPrgId]['fees'];
                $defCommission = $programmeFees[$selectedPrgId]['commission'];
                $commission = $defCommission;
                if (! empty($formData['addCommission'])) {
                    $addCommission = $formData['addCommission'];
                    if ($addCommission > $slctdPrgFees) {
                        $currencyformat->setCurrencyCode($prgCurrency);
                        $formattedPrgFees = $currencyformat($slctdPrgFees);
                        $formattedAdCmsn = $currencyformat($addCommission);
                        $message = sprintf('Added commission: %s, can not exceed course fees: %s', $formattedAdCmsn, $formattedPrgFees);
                        $form->get('addCommission')->setMessages(array(
                            $message
                        ));
                        $errors = true;
                    } else {
                        $commission = $commission + $addCommission;
                    }
                }
                // if (! empty($formData['feeDiscountPercentage'])) {
                // $feeDiscountPercentage = $formData['feeDiscountPercentage'];
                // $discount = ($feeDiscountPercentage / 100) * ($slctdPrgFees);
                // $feesToPay = $slctdPrgFees - $discount;
                // $formData['feeAmount'] = $feesToPay;
                // $formData['feeCurrency'] = $prgCurrency;
                // }
                if (! empty($formData['tuitionFees'])) {
                    $formData['feeAmount'] = $formData['tuitionFees'];
                    $formData['feeCurrency'] = $prgCurrency;
                }
                if (! empty($formData['deductCommission'])) {
                    $deductCommission = $formData['deductCommission'];
                    if ($deductCommission > $slctdPrgFees) {
                        $formattedDecCmsn = $currencyformat($deductCommission);
                        $message = sprintf('Deducted commission %s can not exceed course fees %s', $formattedDecCmsn, $formattedPrgFees);
                        $form->get('deductCommission')->setMessages(array(
                            $message
                        ));
                        $errors = true;
                    } else {
                        $commission = $commission - $deductCommission;
                    }
                }
                
                if (! $errors) {
                    $formData['agentId'] = $agentId;
                    $formData['commissionToBePaidByInstitute'] = $commission;
                    
                    $statuses = $this->modelAccessor->register($formData, $structOptions);
                    if (is_array($statuses)) {
                        $form->setMessages($statuses);
                    } else {
                        return $this->redirect()->toRoute('student/list', array(
                            'instituteId' => $instituteId
                        ));
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
            'hasAgents' => $hasAgents,
            'hasProgrammes' => $hasProgrammes,
            'institute' => $institute,
            'agent' => $agent,
            'hasFeeStructs' => $hasFeeStructs,
            'structOptions' => $structOptions
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
        $studentId = $routeMatchParams['studentId'];
        /* @var $studentObj Student */
        $studentObj = $this->modelAccessor->fetchAll(array(
            'id' => $studentId
        ), null, true)[0];
        
        /* @var $programmeObj Programme */
        $programmeObj = $studentObj->getProgramme();
        
        /* @var $instituteObj Institute */
        $instituteObj = $programmeObj->getInstitute();
        $instituteId = $instituteObj->getId();
        /* @var $agentObj Agent */
        $agentObj = $studentObj->getAgent();
        $agentId = $agentObj->getId();
        $programmes = $this->modelAccessor->getProgrammeModel()->fetchAll(array(
            'instituteId' => $instituteObj->getId()
        ));
        $programmeFees = array();
        $programmeOptions = array();
        $cmsPercentage = $agentObj->getCommissionPercentage();
        foreach ($programmes as $programmeX) {
            $fees = $programmeX['feeAmount'];
            $feeCurrency = $programmeX['feeCurrency'];
            $strFees = $fees . ' ' . $feeCurrency;
            $commsn = (($cmsPercentage / 100) * ($fees));
            $cmsStr = $commsn . ' ' . $feeCurrency;
            $helperStr = '(Fees:' . $strFees . ', Commission:' . $cmsStr . ')';
            $programmeOptions[$programmeX['id']] = $programmeX['name'] . $helperStr;
            $programmeFees[$programmeX['id']] = array(
                'fees' => $fees,
                'currency' => $feeCurrency,
                'commission' => $commsn
            );
        }
        $hasFeeStructs = false;
        if (! empty($structs)) {
            $hasFeeStructs = true;
        }
        $structs = $this->modelAccessor->getInstituteModel()->fetchStructures(array(
            'instituteId' => $instituteId
        ));
        $structOptions = array();
        
        $studentArray = $this->modelAccessor->fetchAll(array(
            'id' => $studentId
        ))[0];
        $oldStudentData = $studentArray;
        $oldStudentData['tuitionFees'] = $studentArray['feeAmount'];
        $oldStudentData['programmeId'] = $studentObj->getProgramme()->getId();
        $dob = $studentObj->getDateOfBirth();
        $oldStudentData['birthDay'] = date('d', $dob);
        $oldStudentData['birthMonth'] = date('m', $dob);
        $oldStudentData['birthYear'] = date('Y', $dob);
        $oldStudentData['agentId'] = $agentObj->getId();
        $breakDowns = $this->modelAccessor->fetchStudentFeeBreakDown(array(
            'studentId' => $studentId
        ));
        foreach ($structs as $structInfo) {
            $eleId = 'cmp---' . $structInfo['id'];
            $structOptions[$eleId] = array(
                'name' => $structInfo['name'],
                'amount' => $structInfo['amount']
            );
            $oldStudentData[$eleId] = $structInfo['amount'];
        }
        if (! empty($breakDowns)) {
            foreach ($breakDowns as $breakDown) {
                $formElementId = 'cmp---' . $breakDown['componentId'];
                if (array_key_exists($formElementId, $structOptions)) {
                    $oldStudentData[$formElementId] = $breakDown['amount'];
                }
            }
        }
        
        $form = new StudentAdd($programmeOptions, $structOptions);
        $form->setData($oldStudentData);
        $errors = false;
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $data = NumberPlay::cleaner($data);
            $form->setData($data);
            if ($form->isValid()) {
                $formData = NumberPlay::cleaner($form->getData());
                //
                $birthDay = $formData['birthDay'];
                $birthMonth = $formData['birthMonth'];
                $birthYear = $formData['birthYear'];
                //
                if (! checkdate($birthMonth, $birthDay, $birthYear)) {
                    $message = 'The date is invalid';
                    $form->get('birthDay')->setMessages(array(
                        $message
                    ));
                    $errors = true;
                } else {
                    $formData['dateOfBirth'] = mktime(0, 0, 0, $birthMonth, $birthDay, $birthYear);
                }
                $currencyformat = $this->getServiceLocator()
                    ->get('ViewHelperManager')
                    ->get('currencyformat');
                // $currencyformat->setLocale('en_US');
                //
                
                $selectedPrgId = $formData['programmeId'];
                $prgCurrency = $programmeFees[$selectedPrgId]['currency'];
                $slctdPrgFees = $programmeFees[$selectedPrgId]['fees'];
                $defCommission = $programmeFees[$selectedPrgId]['commission'];
                $commission = $defCommission;
                if (! empty($formData['addCommission'])) {
                    $addCommission = $formData['addCommission'];
                    if ($addCommission > $slctdPrgFees) {
                        $currencyformat->setCurrencyCode($prgCurrency);
                        $formattedPrgFees = $currencyformat($slctdPrgFees);
                        $formattedAdCmsn = $currencyformat($addCommission);
                        $message = sprintf('Added commission: %s, can not exceed course fees: %s', $formattedAdCmsn, $formattedPrgFees);
                        $form->get('addCommission')->setMessages(array(
                            $message
                        ));
                        $errors = true;
                    } else {
                        $commission = $commission + $addCommission;
                    }
                }
                if (! empty($formData['deductCommission'])) {
                    $deductCommission = $formData['deductCommission'];
                    if ($deductCommission > $slctdPrgFees) {
                        $formattedDecCmsn = $currencyformat($deductCommission);
                        $message = sprintf('Deducted commission %s can not exceed course fees %s', $formattedDecCmsn, $formattedPrgFees);
                        $form->get('deductCommission')->setMessages(array(
                            $message
                        ));
                        $errors = true;
                    } else {
                        $commission = $commission - $deductCommission;
                    }
                }
                $formData['agentId'] = $agentId;
                if (! $errors) {
                    $formData['commissionToBePaidByInstitute'] = $commission;
                    $statuses = $this->modelAccessor->updateUser($studentId, $formData, $structOptions);
                    if (is_array($statuses)) {
                        $form->setMessages($statuses);
                    } else {
                        return $this->redirect()->toRoute('student/list', array(
                            'instituteId' => $instituteId
                        ));
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
        $hasAgents = true;
        $hasProgrammes = true;
        $institute = $this->modelAccessor->getInstituteModel()->fetchAll(array(
            'id' => $instituteObj->getId()
        ))[0];
        $agent = $this->modelAccessor->getAgentModel()->fetchAll(array(
            'id' => $agentObj->getId()
        ))[0];
        $hasFeeStructs = true;
        $data = array(
            'form' => $form,
            'hasAgents' => $hasAgents,
            'hasProgrammes' => $hasProgrammes,
            'institute' => $institute,
            'agent' => $agent,
            'hasFeeStructs' => $hasFeeStructs,
            'structOptions' => $structOptions
        );
        $view = new ViewModel($data);
        $view->setTemplate('application/student/add');
        $vmMenu = new ViewModel();
        $vmMenu->setTemplate('application/menu/adminSideMenu');
        $view->addChild($vmMenu, 'adminSideMenu');
        return $view;
    }

    public function listAction()
    {
        $event = $this->getEvent();
        $application = $event->getApplication();
        $router = $this->serviceLocator->get('Router');
        $routeMatch = $router->match($this->request);
        $routeMatchParams = $routeMatch->getParams();
        $searchParams = array();
        $instituteId = null;
        if (! empty($routeMatchParams['instituteId'])) {
            $instituteId = $routeMatchParams['instituteId'];
            $institute = $this->modelAccessor->getInstituteModel()->fetchAll(array(
                'id' => $instituteId
            ))[0];
            if (empty($institute)) {
                $errorMessage = 'Forbidden Resource';
                $event->setError(ERROR_NEED_AUTHENTICATED_USER);
                $event->setParam('exception', new AuthenticationRequired($errorMessage));
                $event->setParam('redirectUri', '/user/dashboard');
                return $application->getEventManager()->trigger(MvcEvent::EVENT_DISPATCH_ERROR, $event);
            }
            $searchParams['instituteId'] = $instituteId;
        }
        $agentId = null;
        if (! empty($routeMatchParams['agentId'])) {
            $agentId = $routeMatchParams['agentId'];
            $agent = $this->modelAccessor->getAgentModel()->fetchAll(array(
                'id' => $agentId
            ))[0];
            if (empty($agent)) {
                $errorMessage = 'Forbidden Resource';
                $event->setError(ERROR_NEED_AUTHENTICATED_USER);
                $event->setParam('exception', new AuthenticationRequired($errorMessage));
                $event->setParam('redirectUri', '/user/dashboard');
                return $application->getEventManager()->trigger(MvcEvent::EVENT_DISPATCH_ERROR, $event);
            }
            $searchParams['agentId'] = $agentId;
            $agentEmail = $agent['emailId'];
        }
        
        $allStudents = $this->modelAccessor->fetchAll($searchParams, null, true);
        $vm = new ViewModel(array(
            'allStudents' => $allStudents,
            'instituteId' => $instituteId,
            'agentId' => $agentId
        ));
        $vmMenu = new ViewModel();
        $vmMenu->setTemplate('application/menu/adminSideMenu');
        $vm->addChild($vmMenu, 'adminSideMenu');
        return $vm;
    }
}

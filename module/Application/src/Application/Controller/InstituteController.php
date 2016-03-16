<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Model\InstituteORM;
use Zend\Session\Container;
use Zend\Mvc\MvcEvent;
use Zend\Authentication\AuthenticationService;
use Application\Form\InstituteAdd;
use Application\Utilities\NumberPlay;
use Application\Exception\AuthenticationRequired;
use Application\Form\CreateFeeStrcuture;
use Application\NonOrm\InstituteFeeStructure;
use Application\NonOrm\FeeComponent;
use Application\Form\UpdateFeeStructure;

class InstituteController extends AbstractActionController
{

    /**
     *
     * @var AuthenticationService
     */
    protected $empAuthServiceService;

    /**
     *
     * @var InstituteORM
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
        $this->modelAccessor = $this->getServiceLocator()->get('InstituteModel');
        $empAuthServiceService = $this->getServiceLocator()->get('EmpAuthService');
        $this->empAuthServiceService = $empAuthServiceService;
        return parent::onDispatch($e);
    }

    public function updatefeestructureAction()
    {
        $institutesInfo = array();
        $instituteComponents = array();
        $event = $this->getEvent();
        $application = $event->getApplication();
        $router = $this->serviceLocator->get('Router');
        $routeMatch = $router->match($this->request);
        $routeMatchParams = $routeMatch->getParams();
        $instituteId = $routeMatchParams['instituteId'];
        $existingInstitute = $this->modelAccessor->fetchAll(array(
            'id' => $instituteId
        ))[0];
        if (empty($existingInstitute)) {
            $errorMessage = 'Forbidden Resource';
            $event->setError(ERROR_NEED_AUTHENTICATED_USER);
            $event->setParam('exception', new AuthenticationRequired($errorMessage));
            $event->setParam('redirectUri', '/user/dashboard');
            return $application->getEventManager()->trigger(MvcEvent::EVENT_DISPATCH_ERROR, $event);
        }
        $hasStructs = true;
        $institutesInfo = $existingInstitute;
        $allStructures = $this->modelAccessor->fetchStructures(array(
            'instituteId' => $instituteId
        ));
        if (empty($allStructures)) {
            $hasStructs = false;
        } else {
            $allFields = array();
            $structOptions = array();
            $prefill = array();
            foreach ($allStructures as $structInfo) {
                $structOptions['cmp---' . $structInfo['id']] = array(
                    'name' => $structInfo['name'],
                    'amount' => $structInfo['amount']
                );
                $k = 'cmp---' . $structInfo['id'];
                $prefill[$k] = $structInfo['name'];
                $prefill[$k . 'action'] = $structInfo['enabled'];
                $prefill[$k . 'amount'] = $structInfo['amount'];
                $allFields[] = $k;
            }
            $form = new UpdateFeeStructure($structOptions);
            $form->setData($prefill);
            $errors = false;
            if ($this->getRequest()->isPost()) {
                $data = $this->getRequest()->getPost();
                $data = NumberPlay::cleaner($data);
                $form->setData($data);
                if ($form->isValid()) {
                    $formData = NumberPlay::cleaner($form->getData());
                    if (! $errors) {
                        foreach ($allFields as $fld) {
                            $amt = $fld . 'amount';
                            $act = $fld . 'action';
                            $id = explode('---', $fld)[1];
                            $this->modelAccessor->updateFeeStructure($id, array(
                                'name' => $formData[$fld],
                                'amount' => $formData[$amt],
                                'enabled' => $formData[$act],
                                'instituteId' => $instituteId
                            ));
                        }
                        return $this->redirect()->toUrl('/institute/listcomponents/' . $instituteId);
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
                        die();
                    }
                }
            }
            
            $view = new ViewModel(array(
                'hasStructs' => $hasStructs,
                'allFields' => $allFields,
                'form' => $form,
                'instInfo' => $existingInstitute
            ));
            $vmMenu = new ViewModel();
            $vmMenu->setTemplate('application/menu/adminSideMenu');
            $view->addChild($vmMenu, 'adminSideMenu');
            return $view;
        }
    }

    public function listcomponentsAction()
    {
        $institutesInfo = array();
        $instituteComponents = array();
        $event = $this->getEvent();
        $application = $event->getApplication();
        $router = $this->serviceLocator->get('Router');
        $routeMatch = $router->match($this->request);
        $routeMatchParams = $routeMatch->getParams();
        if (! empty($routeMatchParams['instituteId'])) {
            $instituteId = $routeMatchParams['instituteId'];
            $existingInstitute = $this->modelAccessor->fetchAll(array(
                'id' => $instituteId
            ))[0];
            if (empty($existingInstitute)) {
                $errorMessage = 'Forbidden Resource';
                $event->setError(ERROR_NEED_AUTHENTICATED_USER);
                $event->setParam('exception', new AuthenticationRequired($errorMessage));
                $event->setParam('redirectUri', '/user/dashboard');
                return $application->getEventManager()->trigger(MvcEvent::EVENT_DISPATCH_ERROR, $event);
            }
            $allStructures = $this->modelAccessor->fetchStructures(array(
                'instituteId' => $instituteId
            ));
            $institutesInfo[$instituteId] = $this->modelAccessor->fetchAll(array(
                'id' => $instituteId
            ))[0];
        } else {
            $allStructures = $this->modelAccessor->fetchStructures(null);
        }
        if (! empty($allStructures)) {
            foreach ($allStructures as $struct) {
                $instituteId = $struct['instituteId'];
                if (! array_key_exists($instituteId, $instituteComponents)) {
                    $institutesInfo[$instituteId] = $this->modelAccessor->fetchAll(array(
                        'id' => $instituteId
                    ))[0];
                    $instituteComponents[$instituteId] = array();
                }
                $instituteComponents[$instituteId][] = $struct;
            }
        }
        $view = new ViewModel(array(
            'instituteComponents' => $instituteComponents,
            'institutesInfo' => $institutesInfo
        ));
        $vmMenu = new ViewModel();
        $vmMenu->setTemplate('application/menu/adminSideMenu');
        $view->addChild($vmMenu, 'adminSideMenu');
        return $view;
    }

    /**
     *
     * @return array
     */
    public function indexAction()
    {
        $allInstitutes = $this->modelAccessor->fetchAll(null);
        if (empty($allInstitutes)) {
            $hasInstitutes = false;
            $data = array(
                'hasInstitutes' => $hasInstitutes
            );
        } else {
            $hasInstitutes = true;
            $instituteOptions = array();
            foreach ($allInstitutes as $institute) {
                $instituteOptions[$institute['id']] = ucwords($institute['name']);
            }
            $form = new CreateFeeStrcuture($instituteOptions);
            $feeStructure = new InstituteFeeStructure();
            $form->bind($feeStructure);
            $request = $this->getRequest();
            $errors = array();
            if ($request->isPost()) {
                $form->setData($request->getPost());
                if ($form->isValid()) {
                    /* @var $feeStructure InstituteFeeStructure */
                    $instituteId = $request->getPost()['instituteId'];
                    $fcs = $feeStructure->getFeeComponents();
                    foreach ($fcs as $fc) {
                        /* @var $fc FeeComponent */
                        $errors1 = $this->modelAccessor->saveFeeStructure(array(
                            'instituteId' => $instituteId,
                            'name' => $fc->getName(),
                            'amount' => $fc->getAmount(),
                            'enabled' => 1
                        ));
                        if (! empty($errors1) && is_array($errors1)) {
                            $errors = array_merge_recursive($errors, $errors1);
                        } else {
                            return $this->redirect()->toUrl('/institute/listcomponents');
                        }
                    }
                    if (is_array($errors) && ! empty($errors)) {
                        $form->setMessages($errors);
                    }
                } else {
                    echo "form is invalid";
                    var_dump($form->getMessages());
                }
            }
            $data = array(
                'hasInstitutes' => $hasInstitutes,
                'form' => $form
            );
            $view = new ViewModel($data);
            $vmMenu = new ViewModel();
            $vmMenu->setTemplate('application/menu/adminSideMenu');
            $view->addChild($vmMenu, 'adminSideMenu');
            return $view;
        }
        $view = new ViewModel($data);
        $vmMenu = new ViewModel();
        $vmMenu->setTemplate('application/menu/adminSideMenu');
        $view->addChild($vmMenu, 'adminSideMenu');
        return $view;
    }

    public function listfeecomponentsAction()
    {
        $application = $event->getApplication();
        $router = $this->serviceLocator->get('Router');
        $routeMatch = $router->match($this->request);
        $routeMatchParams = $routeMatch->getParams();
        $instituteId = $routeMatchParams['instituteId'];
        $existingInstitute = $this->modelAccessor->fetchAll(array(
            'id' => $instituteId
        ))[0];
        if (empty($existingInstitute)) {
            $errorMessage = 'Forbidden Resource';
            $event->setError(ERROR_NEED_AUTHENTICATED_USER);
            $event->setParam('exception', new AuthenticationRequired($errorMessage));
            $event->setParam('redirectUri', '/user/dashboard');
            return $application->getEventManager()->trigger(MvcEvent::EVENT_DISPATCH_ERROR, $event);
        }
    }

    public function listallAction()
    {
        $allInstitutes = $this->modelAccessor->fetchAll(null);
        
        $view = new ViewModel(array(
            'allInstitutes' => $allInstitutes
        ));
        $vmMenu = new ViewModel();
        $vmMenu->setTemplate('application/menu/adminSideMenu');
        $view->addChild($vmMenu, 'adminSideMenu');
        return $view;
    }

    public function addAction()
    {
        $form = new InstituteAdd();
        $errors = false;
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $data = NumberPlay::cleaner($data);
            $form->setData($data);
            if ($form->isValid()) {
                $formData = NumberPlay::cleaner($form->getData());
                if (! $errors) {
                    $statuses = $this->modelAccessor->register($formData);
                    if (is_array($statuses)) {
                        $form->setMessages($statuses);
                    } else {
                        return $this->redirect()->toRoute('institute/list');
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
            'form' => $form
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
        $instituteId = $routeMatchParams['instituteId'];
        $existingInstitute = $this->modelAccessor->fetchAll(array(
            'id' => $instituteId
        ))[0];
        if (empty($existingInstitute)) {
            $errorMessage = 'Forbidden Resource';
            $event->setError(ERROR_NEED_AUTHENTICATED_USER);
            $event->setParam('exception', new AuthenticationRequired($errorMessage));
            $event->setParam('redirectUri', '/user/dashboard');
            return $application->getEventManager()->trigger(MvcEvent::EVENT_DISPATCH_ERROR, $event);
        }
        $form = new InstituteAdd();
        //
        $form->setData($existingInstitute);
        $errors = false;
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost();
            $data = NumberPlay::cleaner($data);
            $form->setData($data);
            if ($form->isValid()) {
                $formData = NumberPlay::cleaner($form->getData());
                if (! $errors) {
                    $statuses = $this->modelAccessor->update($instituteId, $formData);
                    if (is_array($statuses)) {
                        $form->setMessages($statuses);
                    } else {
                        return $this->redirect()->toRoute('institute/list');
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
            'form' => $form
        ));
        $view->setTemplate('application/institute/add');
        $vmMenu = new ViewModel();
        $vmMenu->setTemplate('application/menu/adminSideMenu');
        $view->addChild($vmMenu, 'adminSideMenu');
        return $view;
    }
}

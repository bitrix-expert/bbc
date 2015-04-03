<?php
/**
 * Basis components
 *
 * @package components
 * @subpackage basis
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 * @copyright Copyright © 2014-2015 Nik Samokhvalov
 * @license MIT
 */

namespace Bex\Bbc\Components;

use Bitrix\Main;
use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;


if(!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true)die();

Loc::loadMessages(__DIR__.'/class.php');


/**
 * Common main trait for all basis components
 */
trait Common
{
    /**
     * @var string File name of log with last exception
     */
    protected $exceptionLog = 'exception.log';

    /**
     * @var bool Sending notifications to admin email
     */
    protected $exceptionNotifier = true;

    /**
     * @var array The codes of modules that will be connected when performing component
     */
    protected $needModules = array();

    /**
     * @var array Additional cache ID
     */
    private $cacheAdditionalId;

    /**
     * @var string Cache dir
     */
    protected $cacheDir = false;

    /**
     * @var bool Caching template of the component (default not cache)
     */
    protected $cacheTemplate = true;

    /**
     * @var string Salt for component ID for AJAX request
     */
    protected $ajaxComponentIdSalt;

    /**
     * @var string Template page name
     */
    protected $templatePage;

    /**
     * @var array List keys from $this->arParams for checking
     * @example $checkParams = array('IBLOCK_TYPE' => array('type' => 'string'), 'ELEMENT_ID' => array('type' => 'int', 'error' => '404'));
     */
    protected $checkParams = array();

    /**
     * Include modules
     *
     * @uses $this->needModules
     * @throws \Bitrix\Main\LoaderException
     */
    protected function includeModules()
    {
        if (empty($this->needModules))
        {
            return false;
        }

        foreach ($this->needModules as $module)
        {
            if (!Main\Loader::includeModule($module))
            {
                throw new Main\LoaderException('Failed include module "'.$module.'"');
            }
        }
    }

    /**
     * @throws \Bitrix\Main\ArgumentNullException
     */
    private function checkAutomaticParams()
    {
        foreach ($this->checkParams as $key => $params)
        {
            $exception = false;

            switch ($params['type'])
            {
                case 'int':

                    if (!is_numeric($this->arParams[$key]) && $params['error'] !== false)
                    {
                        $exception = new Main\ArgumentTypeException($key, 'integer');
                    }
                    else
                    {
                        $this->arParams[$key] = intval($this->arParams[$key]);
                    }

                break;

                case 'string':

                    $this->arParams[$key] = htmlspecialchars(trim($this->arParams[$key]));

                    if (strlen($this->arParams[$key]) <= 0 && $params['error'] !== false)
                    {
                        $exception = new Main\ArgumentNullException($key);
                    }

                break;

                case 'array':

                    if (!is_array($this->arParams[$key]))
                    {
                        if ($params['error'] === false)
                        {
                            $this->arParams[$key] = array($this->arParams[$key]);
                        }
                        else
                        {
                            $exception = new Main\ArgumentTypeException($key, 'array');
                        }
                    }

                break;

                default:
                    $exception = new Main\NotSupportedException('Not supported type of parameter for automatical checking');
                break;
            }

            if ($exception)
            {
                if ($this->checkParams[$key]['error'] === '404')
                {
                    $this->return404(true, $exception);
                }
                else
                {
                    throw $exception;
                }
            }
        }
    }

    /**
     * Checking required component params
     */
    protected function checkParams()
    {

    }

    /**
     * Restart buffer if AJAX request
     */
    private function startAjax()
    {
        if ($this->arParams['USE_AJAX'] !== 'Y')
        {
            return false;
        }

        if (strlen($this->arParams['AJAX_PARAM_NAME']) <= 0)
        {
            $this->arParams['AJAX_PARAM_NAME'] = 'compid';
        }

        if (strlen($this->arParams['AJAX_COMPONENT_ID']) <= 0)
        {
            $this->arParams['AJAX_COMPONENT_ID'] = \CAjax::GetComponentID($this->getName(), $this->getTemplateName(), $this->ajaxComponentIdSalt);
        }

        if ($this->isAjax())
        {
            global $APPLICATION;

            if ($this->arParams['AJAX_HEAD_RELOAD'] === 'Y')
            {
                $APPLICATION->ShowAjaxHead();
            }
            else
            {
                $APPLICATION->RestartBuffer();
            }

            if ($this->arParams['AJAX_TYPE'] === 'JSON')
            {
                header('Content-Type: application/json');
            }

            if (strlen($this->arParams['AJAX_TEMPLATE_PAGE']) > 0)
            {
                $this->templatePage = basename($this->arParams['AJAX_TEMPLATE_PAGE']);
            }
        }
    }

    /**
     * Execute before getting results. Not cached
     */
    protected function executeProlog()
    {

    }

    /**
     * Cache init
     *
     * @return bool
     */
    protected function startCache()
    {
        global $USER;

        if ($this->arParams['CACHE_TYPE'] && $this->arParams['CACHE_TYPE'] !== 'N' && $this->arParams['CACHE_TIME'] > 0)
        {
            if ($this->templatePage)
            {
                $this->cacheAdditionalId[] = $this->templatePage;
            }

            if ($this->arParams['CACHE_GROUPS'] === 'Y')
            {
                $this->cacheAdditionalId[] = $USER->GetGroups();
            }

            if ($this->startResultCache($this->arParams['CACHE_TIME'], $this->cacheAdditionalId, $this->cacheDir))
            {
                return true;
            }
            else
            {
                return false;
            }
        }

        return true;
    }

    /**
     * Write cache to disk
     */
    protected function writeCache()
    {
        $this->endResultCache();
    }

    /**
     * Resets the cache
     */
    protected function abortCache()
    {
        $this->abortResultCache();
    }

    /**
     * A method for extending the results of the child classes.
     * The result this method will be cached
     */
    protected function executeMain()
    {

    }

    protected function executeMainCommon()
    {
        if (strlen($this->arParams['AJAX_PARAM_NAME']) > 0 && strlen($this->arParams['AJAX_COMPONENT_ID']) > 0)
        {
            $this->arResult['AJAX_REQUEST_PARAMS'] = $this->arParams['AJAX_PARAM_NAME'].'='.$this->arParams['AJAX_COMPONENT_ID'];

            $this->setResultCacheKeys(array('AJAX_REQUEST_PARAMS'));
        }
    }

    /**
     * Execute after getting results. Not cached
     */
    protected function executeEpilog()
    {

    }

    /**
     * Stop execute script if AJAX request
     */
    private function stopAjax()
    {
        if ($this->isAjax() && $this->arParams['USE_AJAX'] === 'Y')
        {
            exit;
        }
    }

    /**
     * Show results. Default: include template of the component
     *
     * @uses $this->templatePage
     */
    protected function returnDatas()
    {
        $this->includeComponentTemplate($this->templatePage);
    }

    private function executeFinal()
    {
        if ($this->exceptionNotifier)
        {
            $logFile = Application::getDocumentRoot().$this->__path.'/'.$this->exceptionLog;

            if (is_file($logFile))
            {
                unlink($logFile);
            }
        }
    }

    /**
     * Set status 404 and throw exception
     *
     * @param bool $notifier Sent notify to admin email
     * @param \Exception|null|false $exception Exception which will be throwing or "false" what not throwing exceptions. Default — throw new \Exception()
     * @throws \Exception
     */
    protected function return404($notifier = false, \Exception $exception = null)
    {
        @define('ERROR_404', 'Y');
        \CHTTP::SetStatus('404 Not Found');

        if ($exception !== false)
        {
            if ($notifier === false)
            {
                $this->exceptionNotifier = false;
            }

            if ($exception instanceof \Exception)
            {
                throw $exception;
            }
            else
            {
                throw new \Exception('Page not found');
            }
        }
    }

    /**
     * Called when an error occurs
     *
     * Resets the cache, show error message (two mode: for users and for admins),
     * sending notification to admin email
     *
     * @param \Exception $exception
     * @param bool $notifier Sent notify to admin email. Default — value of property $this->exceptionNotifier
     * @uses exceptionNotifier
     */
    protected function catchException(\Exception $exception, $notifier = null)
    {
        global $USER;

        $this->abortCache();

        if ($USER->IsAdmin())
        {
            $this->showExceptionAdmin($exception);
        }
        else
        {
            $this->showExceptionUser($exception);
        }

        if (($notifier === true) || ($this->exceptionNotifier && $notifier !== false) && BX_EXC_NOTIFY !== false)
        {
            $this->sendNotifyException($exception);
        }
    }

    /**
     * Send error message to the admin email
     *
     * @param \Exception $exception
     */
    protected function sendNotifyException($exception)
    {
        $adminEmail = Main\Config\Option::get('main', 'email_from');
        $logFile = Application::getDocumentRoot().$this->__path.'/'.$this->exceptionLog;

        if (!is_file($logFile) && $adminEmail)
        {
            $date = date('Y-m-d H:m:s');

            bxmail(
                $adminEmail,
                Loc::getMessage(
                    'BBC_COMPONENT_EXCEPTION_EMAIL_SUBJECT', array('#SITE_URL#' => SITE_SERVER_NAME)
                ),
                Loc::getMessage(
                    'BBC_COMPONENT_EXCEPTION_EMAIL_TEXT',
                    array(
                        '#URL#' => 'http://'.SITE_SERVER_NAME.Main\Context::getCurrent()->getRequest()->getRequestedPage(),
                        '#DATE#' => $date,
                        '#EXCEPTION_MESSAGE#' => $exception->getMessage(),
                        '#EXCEPTION#' => $exception
                    )
                ),
                'Content-Type: text/html; charset=utf-8'
            );

            $log = fopen($logFile, 'w');
            fwrite($log, '['.$date.'] Catch exception: '.PHP_EOL.$exception);
            fclose($log);
        }
    }

    /**
     * Display of the error for user
     *
     * @param \Exception $exception
     */
    protected function showExceptionUser(\Exception $exception)
    {
        // todo set HTTP status and constant

        ShowError(Loc::getMessage('BBC_COMPONENT_CATCH_EXCEPTION'));
    }

    /**
     * Display of the error for admin
     *
     * @param \Exception $exception
     */
    protected function showExceptionAdmin(\Exception $exception)
    {
        ShowError($exception->getMessage());

        echo nl2br($exception);
    }

    /**
     * Is AJAX request
     *
     * @return bool
     */
    public function isAjax()
    {
        if (
            strlen($this->arParams['AJAX_COMPONENT_ID']) > 0
            && strlen($this->arParams['AJAX_PARAM_NAME']) > 0
            && $_REQUEST[$this->arParams['AJAX_PARAM_NAME']] === $this->arParams['AJAX_COMPONENT_ID']
            && isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']))
        {
            return true;
        }

        return false;
    }

    /**
     * Register tag in cache
     *
     * @param string $tag Tag
     */
    public static function registerCacheTag($tag)
    {
        if ($tag)
        {
            Application::getInstance()->getTaggedCache()->registerTag($tag);
        }
    }

    /**
     * Add additional ID to cache
     *
     * @param mixed $id
     */
    protected function addCacheAdditionalId($id)
    {
        $this->cacheAdditionalId[] = $id;
    }
}
<?php
/**
 * Basis components
 *
 * @package components
 * @subpackage basis
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 * @copyright Copyright (c) 2014, Nik Samokhvalov
 */
namespace Components\Basis;

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
    public static $logException = 'exception.log';

    /**
     * @var object Main\Data\Cache
     */
    protected $cache;

    /**
     * @var array Additional cache ID
     */
    protected $cacheIdAdditional;

    /**
     * @var string Cache dir
     */
    protected $cacheDir = false;

    /**
     * @var bool Caching template of the component (default not cache)
     */
    protected $cacheTemplate = true;

    /**
     * @var string Component ID for AJAX request (default value result of CAjax::GetComponentID())
     */
    protected $ajaxComponentId;

    /**
     * @var string Salt for component ID for AJAX request
     */
    protected $ajaxComponentIdSalt;

    /**
     * @var string Name of parameter for AJAX request (example: index.php?compid=…)
     */
    protected $ajaxRequestParam = 'compid';

    /**
     * @var bool Reload page headers after AJAX request
     */
    protected $ajaxReloadHead = false;

    /**
     * @var array Paginator parameters
     */
    protected $navParams;

    /**
     * @var string Template page name
     */
    protected $page;

    /**
     * @var array The codes of modules that will be connected when performing component
     */
    protected static $needModules = array();

    /**
     * Include modules
     *
     * @param array $needModules [optional] Array with codes of the modules (default uses static::$needModules)
     * @throws \Bitrix\Main\LoaderException
     */
    public static function includeModules($needModules = array())
    {
        if (!$needModules)
        {
            $needModules = static::$needModules;
        }

        foreach ($needModules as $module)
        {
            if (!Main\Loader::includeModule($module))
            {
                throw new Main\LoaderException(
                    Loc::getMessage('BASIS_COMPONENT_EXCEPTION_LOADER', array('#MODULE_CODE#' => $module))
                );
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
    protected function startAjax()
    {
        if (!$this->ajaxComponentId)
        {
            $this->ajaxComponentId = \CAjax::GetComponentID($this->getName(), $this->getTemplateName(), $this->ajaxComponentIdSalt);
        }

        if ($this->isAjax())
        {
            global $APPLICATION;

            if ($this->ajaxReloadHead)
            {
                $APPLICATION->ShowAjaxHead();
            }
            else
            {
                $APPLICATION->RestartBuffer();
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
        if ($this->arParams['CACHE_TYPE'] && $this->arParams['CACHE_TYPE'] !== 'N' && $this->arParams['CACHE_TIME'] > 0)
        {
            $this->cacheIdAdditional = array(
                $this->cacheIdAdditional,
                $this->page,
                \CDBResult::GetNavParams($this->navParams)
            );

            if ($this->startResultCache($this->arParams['CACHE_TIME'], $this->cacheIdAdditional, $this->cacheDir))
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
    protected function getResult()
    {

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
    protected function stopAjax()
    {
        if ($this->isAjax())
        {
            exit;
        }
    }

    /**
     * Setting component AJAX parameters
     */
    private function setAjaxParams()
    {
        if ($this->ajaxRequestParam && $this->ajaxComponentId)
        {
            $this->arResult['AJAX_COMPONENT_ID'] = $this->ajaxComponentId;
            $this->arResult['AJAX_PARAM_NAME'] = $this->ajaxRequestParam;
            $this->arResult['AJAX_REQUEST_PARAMS'] = $this->ajaxRequestParam.'='.$this->ajaxComponentId;
        }
    }

    /**
     * Set status 404 and reset cache
     */
    protected function return404()
    {
        $this->abortCache();

        @define('ERROR_404', 'Y');
        \CHTTP::SetStatus('404 Not Found');
    }

    /**
     * Called when an error occurs
     *
     * @param object $e Exception
     */
    protected function catchException($e)
    {
        global $USER;

        $adminEmail = Main\Config\Option::get('main', 'email_from');
        $logFile = Application::getDocumentRoot().$this->__path.'/'.static::$logException;

        $this->abortCache();

        if ($USER->IsAdmin())
        {
            $this->showExceptionAdmin($e);
        }
        else
        {
            $this->showExceptionUser($e);
        }

        if (!is_file($logFile) && $adminEmail)
        {
            $date = date('Y-m-d H:m:s');

            bxmail(
                $adminEmail,
                Loc::getMessage(
                    'BASIS_COMPONENT_EXCEPTION_EMAIL_SUBJECT', array('#SITE_URL#' => SITE_SERVER_NAME)
                ),
                Loc::getMessage(
                    'BASIS_COMPONENT_EXCEPTION_EMAIL_TEXT',
                    array(
                        '#URL#' => 'http://'.SITE_SERVER_NAME.Main\Context::getCurrent()->getRequest()->getRequestedPage(),
                        '#DATE#' => $date,
                        '#EXCEPTION_MESSAGE#' => $e->getMessage(),
                        '#EXCEPTION#' => $e
                    )
                ),
                'Content-Type: text/html; charset=utf-8'
            );

            $log = fopen($logFile, 'w');
            fwrite($log, '['.$date.'] Catch exception: '.PHP_EOL.$e);
            fclose($log);
        }
    }

    /**
     * Display of the error for user
     *
     * @param object $e Exception
     */
    protected function showExceptionUser($e)
    {
        ShowError(Loc::getMessage('BASIS_COMPONENT_CATCH_EXCEPTION'));
    }

    /**
     * Display of the error for admin
     *
     * @param object $e Exception
     */
    protected function showExceptionAdmin($e)
    {
        ShowError($e->getMessage());

        echo nl2br($e);
    }

    /**
     * Show results. Default: include template of the component
     *
     * @uses $this->page
     */
    protected function returnDatas()
    {
        $this->includeComponentTemplate($this->page);
    }

    private function executeFinal()
    {
        $logFile = Application::getDocumentRoot().$this->__path.'/'.static::$logException;

        if (is_file($logFile))
        {
            unlink($logFile);
        }
    }

    /**
     * Is AJAX request
     *
     * @return bool
     */
    public function isAjax()
    {
        if (
            $this->ajaxComponentId
            && $this->ajaxRequestParam
            && $_REQUEST[$this->ajaxRequestParam] === $this->ajaxComponentId
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
}
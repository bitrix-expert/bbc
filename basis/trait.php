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

if(!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true)die();


trait BasisTrait
{
    /**
     * @var object Main\Data\Cache
     */
    protected $cache;

    /**
     * @var string Cache ID
     */
    protected $cacheId;

    /**
     * @var string Cache dir
     */
    protected $cacheDir;

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
     * @var bool|array Pagination
     */
    protected $navParams;

    /**
     * @var array The codes of modules that will be connected when performing component
     */
    protected $needModules;

    /**
     * Include modules (use $this->needModules)
     *
     * @throws \Bitrix\Main\LoaderException
     */
    protected function includeModules()
    {
        foreach ($this->needModules as $module)
        {
            if (!Main\Loader::includeModule($module))
            {
                throw new Main\LoaderException('Not installed module "'.$module.'"');
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

            $APPLICATION->RestartBuffer();
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
            $this->cache = Main\Data\Cache::createInstance();
            $this->cacheId = $this->getCacheId();
            $this->cacheDir = Application::getInstance()->getManagedCache()->getCompCachePath($this->getRelativePath());

            if ($this->startResultCache($this->arParams['CACHE_TIME'], $this->cacheId, $this->cacheDir))
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
        if ($this->cache)
        {
            Application::getInstance()->getTaggedCache()->endTagCache();

            $this->endResultCache();
        }
    }

    /**
     * Resets the cache
     */
    protected function abortCache()
    {
        if ($this->cache)
        {
            $this->abortResultCache();
        }
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
     * Stop execute of script if AJAX request
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
     * Called when an error occurs
     *
     * @param object $e Exception
     */
    protected function catchError($e)
    {
        ShowError($e->getMessage());
    }

    /**
     * Show results. Default: include template of the component
     *
     * @param string $page Template page
     * @param string $customPath Custom template path
     */
    protected function returnDatas($page = '', $customPath = '')
    {
        $this->includeComponentTemplate($page, $customPath);
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
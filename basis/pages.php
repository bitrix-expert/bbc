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

if(!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true)die();


trait Pages
{
    protected function executePrologPages()
    {
        $this->setNavParams();
    }

    protected function setNavParams()
    {
        if ($this->arParams['NAV_SHOW'] === 'Y' && $this->arParams['ELEMENTS_COUNT'] > 0)
        {
            $this->navParams = array(
                'nPageSize' => $this->arParams['ELEMENTS_COUNT'],
                'bShowAll' => false
            );
        }
        elseif ($this->arParams['ELEMENTS_COUNT'] > 0)
        {
            $this->navParams = array(
                'nTopCount' => $this->arParams['ELEMENTS_COUNT']
            );
        }
        else
        {
            $this->navParams = false;
        }

        if ($this->arParams['NAV_SAVE_SESSION'] !== 'Y')
        {
            \CPageOption::SetOptionString('main', 'nav_page_in_session', 'N');
        }
    }

    /**
     * Generate navigation string
     *
     * @param object $result \CIBlockResult
     */
    protected function setNav($result)
    {
        if ($this->arParams['NAV_SHOW'] === 'Y')
        {
            $navComponentObject = false;

            $this->arResult['NAV_STRING'] = $result->GetPageNavStringEx(
                $navComponentObject,
                $this->arParams['NAV_TITLE'],
                $this->arParams['NAV_TEMPLATE'],
                $this->arParams['NAV_SHOW_ALWAYS']
            );
            $this->arResult['NAV_CACHED_DATA'] = $navComponentObject->GetTemplateCachedData();
            $this->arResult['NAV_RESULT'] = $result;
        }
    }

    /**
     * Setting meta tags for current page
     * <ul> Uses:
     * <li> title
     * <li> description
     * <li> keywords
     * </ul>
     */
    protected function setSeoTags()
    {
        global $APPLICATION;

        if ($this->arResult['SEO_TAGS']['TITLE'])
        {
            $APPLICATION->SetPageProperty('title', $this->arResult['SEO_TAGS']['TITLE']);
        }

        if ($this->arResult['SEO_TAGS']['DESCRIPTION'])
        {
            $APPLICATION->SetPageProperty('description', $this->arResult['SEO_TAGS']['DESCRIPTION']);
        }

        if ($this->arResult['SEO_TAGS']['KEYWORDS'])
        {
            $APPLICATION->SetPageProperty('keywords', $this->arResult['SEO_TAGS']['KEYWORDS']);
        }
    }

    /**
     * Setting open graph tags for current page
     * <ul> Uses:
     * <li> og:title
     * <li> og:type
     * <li> og:url
     * <li> og:image
     * </ul>
     */
    protected function setOgTags()
    {
        global $APPLICATION;

        if ($this->arResult['OG_TAGS']['TITLE'])
        {
            $APPLICATION->AddHeadString('<meta property="og:title" content="'.$this->arResult['OG_TAGS']['TITLE'].'" />', true);
        }

        if ($this->arResult['OG_TAGS']['TYPE'])
        {
            $APPLICATION->AddHeadString('<meta property="og:type" content="'.$this->arResult['OG_TAGS']['TYPE'].'" />', true);
        }

        if ($this->arResult['OG_TAGS']['URL'])
        {
            $APPLICATION->AddHeadString('<meta property="og:url" content="'.$this->arResult['OG_TAGS']['URL'].'" />', true);
        }

        if ($this->arResult['OG_TAGS']['IMAGE'])
        {
            $APPLICATION->AddHeadString('<meta property="og:image" content="'.$this->arResult['OG_TAGS']['IMAGE'].'" />', true);
        }
    }

    protected function executeEpilogPages()
    {
        $this->setSeoTags();
        $this->setOgTags();
    }
}
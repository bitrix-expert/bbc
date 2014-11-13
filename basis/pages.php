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

use \Bitrix\Iblock\InheritedProperty;


if(!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true)die();


trait Pages
{
    /**
     * @var array Paginator parameters
     */
    protected $navParams;

    protected function executePrologPages()
    {
        $this->setNavParams();
        $this->setGlobalFilters();
    }

    protected function setNavParams()
    {
        if ($this->arParams['PAGER_SAVE_SESSION'] !== 'Y')
        {
            \CPageOption::SetOptionString('main', 'nav_page_in_session', 'N');
        }

        if ($this->arParams['DISPLAY_BOTTOM_PAGER'] === 'Y' || $this->arParams['DISPLAY_TOP_PAGER'] === 'Y')
        {
            $this->navParams = array(
                'nPageSize' => $this->arParams['ELEMENTS_COUNT'],
                'bDescPageNumbering' => $this->arParams['PAGER_DESC_NUMBERING'],
                'bShowAll' => $this->arParams['PAGER_SHOW_ALL']
            );

            $this->addCacheAdditionalId(\CDBResult::GetNavParams($this->navParams));
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
    }

    /**
     * Generate navigation string
     *
     * @param object $result \CIBlockResult
     */
    protected function setNav($result)
    {
        if ($this->arParams['DISPLAY_BOTTOM_PAGER'] === 'Y' || $this->arParams['DISPLAY_TOP_PAGER'] === 'Y')
        {
            $navComponentObject = false;

            $this->arResult['NAV_STRING'] = $result->GetPageNavStringEx(
                $navComponentObject,
                $this->arParams['PAGER_TITLE'],
                $this->arParams['PAGER_TEMPLATE'],
                $this->arParams['PAGER_SHOW_ALWAYS']
            );
            $this->arResult['NAV_CACHED_DATA'] = $navComponentObject->GetTemplateCachedData();
            $this->arResult['NAV_RESULT'] = $result;
        }
    }

    protected function executeGetResultPages()
    {
        if ($this->arParams['SET_SEO_TAGS'] !== 'Y')
        {
            return;
        }

        if ($this->arParams['SECTION_ID'])
        {
            $rsSeoValues = new InheritedProperty\SectionValues($this->arParams['IBLOCK_ID'], $this->arParams['SECTION_ID']);
            $arSeoValues = $rsSeoValues->getValues();

            if (!$this->arResult['SEO_TAGS']['TITLE'])
            {
                $this->arResult['SEO_TAGS']['TITLE'] = $arSeoValues['SECTION_META_TITLE'];
            }

            if (!$this->arResult['SEO_TAGS']['DESCRIPTION'])
            {
                $this->arResult['SEO_TAGS']['DESCRIPTION'] = $arSeoValues['SECTION_META_DESCRIPTION'];
            }

            if (!$this->arResult['SEO_TAGS']['KEYWORDS'])
            {
                $this->arResult['SEO_TAGS']['KEYWORDS'] = $arSeoValues['SECTION_META_KEYWORDS'];
            }
        }
        elseif ($this->arParams['ELEMENT_ID'])
        {
            $rsSeoValues = new InheritedProperty\ElementValues($this->arParams['IBLOCK_ID'], $this->arParams['ELEMENT_ID']);
            $arSeoValues = $rsSeoValues->getValues();

            if (!$this->arResult['SEO_TAGS']['TITLE'])
            {
                $this->arResult['SEO_TAGS']['TITLE'] = $arSeoValues['ELEMENT_META_TITLE'];
            }

            if (!$this->arResult['SEO_TAGS']['DESCRIPTION'])
            {
                $this->arResult['SEO_TAGS']['DESCRIPTION'] = $arSeoValues['ELEMENT_META_DESCRIPTION'];
            }

            if (!$this->arResult['SEO_TAGS']['KEYWORDS'])
            {
                $this->arResult['SEO_TAGS']['KEYWORDS'] = $arSeoValues['ELEMENT_META_KEYWORDS'];
            }
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

        if ($this->arResult['OG_TAGS']['DESCRIPTION'])
        {
            $APPLICATION->AddHeadString('<meta property="og:description" content="'.$this->arResult['OG_TAGS']['DESCRIPTION'].'" />', true);
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

    /**
     * Prepare parameters of sort of the component
     */
    protected function setSortFields()
    {
        $this->arParams['SORT_BY_1'] = trim($this->arParams['SORT_BY_1']);

        if (strlen($this->arParams['SORT_BY_1']) <= 0)
        {
            $this->arParams['SORT_BY_1'] = 'ACTIVE_FROM';
        }

        if (!preg_match('/^(asc|desc|nulls)(,asc|,desc|,nulls){0,1}$/i', $this->arParams['SORT_ORDER_1']))
        {
            $this->arParams['SORT_ORDER_1'] = 'DESC';
        }

        if (strlen($this->arParams['SORT_BY_2']) <= 0)
        {
            $this->arParams['SORT_BY_2'] = 'SORT';
        }

        if (!preg_match('/^(asc|desc|nulls)(,asc|,desc|,nulls){0,1}$/i', $this->arParams['SORT_ORDER_2']))
        {
            $this->arParams['SORT_ORDER_2'] = 'ASC';
        }
    }

    /**
     * Getting global filter and write his to component parameters
     */
    protected function setGlobalFilters()
    {
        if (strlen($this->arParams['EX_FILTER_NAME']) > 0
            && preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $this->arParams['EX_FILTER_NAME'])
            && is_array($GLOBALS[$this->arParams['EX_FILTER_NAME']])
        )
        {
            $this->arParams['EX_FILTER'] = $GLOBALS[$this->arParams['EX_FILTER_NAME']];
        }
        else
        {
            $this->arParams['EX_FILTER'] = array();
        }
    }

    /**
     * Returns array with selected fields and properties for uses in \CIBlock...::GetList()
     *
     * @param string $propsPrefix Prefix for properties. Default PROPERTY_
     * @return array
     */
    protected function getSelectedFields($propsPrefix = 'PROPERTY_')
    {
        $fields = array();

        foreach ($this->arParams['SELECT_FIELDS'] as $field)
        {
            if (trim($field))
            {
                $fields[] = $field;
            }
        }

        unset($field);

        foreach ($this->arParams['SELECT_PROPS'] as $propCode)
        {
            if (trim($propCode))
            {
                $fields[] = $propsPrefix.$propCode;
            }
        }

        return $fields;
    }

    protected function executeEpilogPages()
    {
        $this->setSeoTags();
        $this->setOgTags();
    }
}
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


trait Elements
{
    /**
     * @var array|bool Paginator parameters for \CIBlockElement::GetList()
     */
    private $navStartParams;

    private $globalFilterValues = array();

    protected function executePrologElements()
    {
        $this->setNavStartParams();
        $this->setGlobalFilters();
    }

    protected function setNavStartParams()
    {
        if ($this->arParams['PAGER_SAVE_SESSION'] !== 'Y')
        {
            \CPageOption::SetOptionString('main', 'nav_page_in_session', 'N');
        }

        if ($this->arParams['DISPLAY_BOTTOM_PAGER'] === 'Y' || $this->arParams['DISPLAY_TOP_PAGER'] === 'Y')
        {
            $this->navStartParams = array(
                'nPageSize' => $this->arParams['ELEMENTS_COUNT'],
                'bDescPageNumbering' => $this->arParams['PAGER_DESC_NUMBERING'],
                'bShowAll' => $this->arParams['PAGER_SHOW_ALL']
            );

            $this->addCacheAdditionalId(\CDBResult::GetNavParams($this->navStartParams));
        }
        elseif ($this->arParams['ELEMENTS_COUNT'] > 0)
        {
            $this->navStartParams = array(
                'nTopCount' => $this->arParams['ELEMENTS_COUNT']
            );
        }
        else
        {
            $this->navStartParams = false;
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

    protected function executeGetResultElements()
    {
        if ($this->arParams['SET_SEO_TAGS'] !== 'Y' || !$this->arParams['IBLOCK_ID'])
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
     * Returns prepare parameters of sort of the component
     *
     * @param array $additionalFields Additional fields for sorting
     * @return array
     */
    protected function getParamsSort($additionalFields = array())
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

        $fields = array(
            $this->arParams['SORT_BY_1'] => $this->arParams['SORT_ORDER_1'],
            $this->arParams['SORT_BY_2'] => $this->arParams['SORT_ORDER_2']
        );

        if (is_array($additionalFields) && !empty($additionalFields))
        {
            $fields = array_merge($fields, $additionalFields);
        }

        return $fields;
    }

    /**
     * Getting global filter and write his to component parameters
     */
    private function setGlobalFilters()
    {
        if (strlen($this->arParams['EX_FILTER_NAME']) > 0
            && preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $this->arParams['EX_FILTER_NAME'])
            && is_array($GLOBALS[$this->arParams['EX_FILTER_NAME']])
        )
        {
            $this->globalFilterValues = $GLOBALS[$this->arParams['EX_FILTER_NAME']];

            $this->addCacheAdditionalId($GLOBALS[$this->arParams['EX_FILTER_NAME']]);
        }
    }

    /**
     * Returns array filters fields for uses in \CIBlock...::GetList().
     *
     * Returns array with values global filter and (if is set in $this->arParams)
     * <ul>
     * <li> IBLOCK_TYPE
     * <li> IBLOCK_ID
     * <li> SECTION_ID
     * </ul>
     *
     * @param array $additionalFields
     * @return array
     */
    protected function getParamsFilter($additionalFields = array())
    {
        if ($this->arParams['IBLOCK_TYPE'] && !$additionalFields['IBLOCK_TYPE'])
        {
            $additionalFields['IBLOCK_TYPE'] = $this->arParams['IBLOCK_TYPE'];
        }

        if ($this->arParams['IBLOCK_ID'] > 0 && !$additionalFields['IBLOCK_ID'])
        {
            $additionalFields['IBLOCK_ID'] = $this->arParams['IBLOCK_ID'];
        }

        if ($this->arParams['SECTION_ID'] > 0 && !$additionalFields['SECTION_ID'])
        {
            $additionalFields['SECTION_ID'] = $this->arParams['SECTION_ID'];
        }

        if ($this->arParams['ELEMENT_ID'] > 0 && !$additionalFields['ID'])
        {
            $additionalFields['ID'] = $this->arParams['ELEMENT_ID'];
        }

        if ($this->arParams['CHECK_PERMISSIONS'] && !$additionalFields['CHECK_PERMISSIONS'])
        {
            $additionalFields['CHECK_PERMISSIONS'] = $this->arParams['CHECK_PERMISSIONS'];
        }

        if ($additionalFields['ACTIVE'] != false)
        {
            $additionalFields['ACTIVE'] = 'Y';
        }

        if (is_array($additionalFields) && !empty($additionalFields))
        {
            $this->globalFilterValues = array_merge($this->globalFilterValues, $additionalFields);
        }

        return $this->globalFilterValues;
    }

    protected function getParamsNavStart($additionalFields = array())
    {
        if (is_array($additionalFields) && !empty($additionalFields))
        {
            $this->navStartParams = array_merge($this->navStartParams, $additionalFields);
        }

        return $this->navStartParams;
    }

    /**
     * Returns array with selected fields and properties for uses in \CIBlock...::GetList()
     *
     * @param array $additionalFields Additional fields
     * @return array
     */
    protected function getParamsSelected($additionalFields = array())
    {
        $fields = array(
            'ID',
            'IBLOCK_ID',
            'NAME'
        );

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
                $fields[] = $propCode;
            }
        }

        if (is_array($additionalFields) && !empty($additionalFields))
        {
            $fields = array_merge($fields, $additionalFields);
        }

        return $fields;
    }

    protected function executeEpilogElements()
    {
        $this->setSeoTags();
        $this->setOgTags();
    }
}
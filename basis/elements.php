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

use Bitrix\Iblock\InheritedProperty;
use Bitrix\Main\Page\Asset;


if(!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true)die();


trait Elements
{
    /**
     * @var array|bool Paginator parameters for \CIBlockElement::GetList()
     */
    private $navStartParams;

    /**
     * @var array|bool Group parameters for \CIBlockElement::GetList()
     */
    private $groupingParams;

    /**
     * @var array Values of global filter
     */
    private $globalFilterValues = array();

    /**
     * @var bool Show include areas
     */
    protected $showEditButtons = true;

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
    protected function generateNav($result)
    {
        /**
         * @global object $navComponentObject
         */

        if ($this->arParams['DISPLAY_BOTTOM_PAGER'] === 'Y' || $this->arParams['DISPLAY_TOP_PAGER'] === 'Y')
        {
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
     *
     * <ul> Uses:
     * <li> title
     * <li> description
     * <li> keywords
     * </ul>
     *
     * @uses arResult['SEO_TAGS']
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
     *
     * <ul> Uses:
     * <li> og:title
     * <li> og:type
     * <li> og:url
     * <li> og:image
     * </ul>
     *
     * @uses arResult['OG_TAGS']
     */
    protected function setOgTags()
    {
        if ($this->arResult['OG_TAGS']['TITLE'])
        {
            Asset::getInstance()->addString('<meta property="og:title" content="'.$this->arResult['OG_TAGS']['TITLE'].'" />', true);
        }

        if ($this->arResult['OG_TAGS']['DESCRIPTION'])
        {
            Asset::getInstance()->addString('<meta property="og:description" content="'.$this->arResult['OG_TAGS']['DESCRIPTION'].'" />', true);
        }

        if ($this->arResult['OG_TAGS']['TYPE'])
        {
            Asset::getInstance()->addString('<meta property="og:type" content="'.$this->arResult['OG_TAGS']['TYPE'].'" />', true);
        }

        if ($this->arResult['OG_TAGS']['URL'])
        {
            Asset::getInstance()->addString('<meta property="og:url" content="'.$this->arResult['OG_TAGS']['URL'].'" />', true);
        }

        if ($this->arResult['OG_TAGS']['IMAGE'])
        {
            Asset::getInstance()->addString('<meta property="og:image" content="'.$this->arResult['OG_TAGS']['IMAGE'].'" />', true);
        }
    }

    /**
     * Add to page buttons for edit elements and sections of info-block
     */
    protected function setEditButtons()
    {
        global $APPLICATION;

        if (!$APPLICATION->GetShowIncludeAreas() || $this->showEditButtons === false)
        {
            return false;
        }

        $buttons = \CIBlock::GetPanelButtons(
            $this->arParams['IBLOCK_ID'],
            $this->arResult['ID'],
            $this->arParams['SECTION_ID'], // @todo Can be SECTION_CODE
            array()
        );

        $this->addIncludeAreaIcons(\CIBlock::GetComponentMenu($APPLICATION->GetPublicShowMode(), $buttons));

        if (is_array($buttons['intranet']))
        {
            Asset::getInstance()->addJs(BX_ROOT.'/js/main/utils.js');

            foreach ($buttons['intranet'] as $button)
            {
                $this->addEditButton($button);
            }
        }
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
     * Add new fields to global filter
     *
     * @param array $fields
     */
    protected function addGlobalFilters(array $fields)
    {
        if (is_array($fields) && !empty($fields))
        {
            $this->globalFilterValues = array_merge_recursive($this->globalFilterValues, $fields);
            $this->addCacheAdditionalId($fields);
        }
    }

    /**
     * Add parameters to grouping
     *
     * @param array $additionalFields
     * @uses groupingParams
     */
    protected function addParamsGrouping($additionalFields = array())
    {
        if (is_array($additionalFields) && !empty($additionalFields))
        {
            $this->groupingParams = array_merge($this->groupingParams, $additionalFields);
        }
    }

    /**
     * Add parameters to pagination settings
     *
     * @param array $additionalFields
     * @uses navStartParams
     */
    protected function addParamsNavStart($additionalFields = array())
    {
        if (is_array($additionalFields) && !empty($additionalFields))
        {
            $this->navStartParams = array_merge($this->navStartParams, $additionalFields);
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
    protected function getParamsFilters($additionalFields = array())
    {
        if ($this->arParams['IBLOCK_TYPE'] && !$additionalFields['IBLOCK_TYPE'])
        {
            $additionalFields['IBLOCK_TYPE'] = $this->arParams['IBLOCK_TYPE'];
        }

        if ($this->arParams['IBLOCK_ID'] > 0 && !$additionalFields['IBLOCK_ID'])
        {
            $additionalFields['IBLOCK_ID'] = $this->arParams['IBLOCK_ID'];
        }

        if ($this->arParams['SECTION_CODE'] && !$additionalFields['SECTION_CODE'])
        {
            $additionalFields['SECTION_CODE'] = $this->arParams['SECTION_CODE'];
        }
        elseif ($this->arParams['SECTION_ID'] > 0 && !$additionalFields['SECTION_ID'])
        {
            $additionalFields['SECTION_ID'] = $this->arParams['SECTION_ID'];
        }

        if ($this->arParams['ELEMENT_CODE'] && !$additionalFields['CODE'])
        {
            $additionalFields['CODE'] = $this->arParams['ELEMENT_CODE'];
        }
        elseif ($this->arParams['ELEMENT_ID'] > 0 && !$additionalFields['ID'])
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
            $this->globalFilterValues = array_merge_recursive($this->globalFilterValues, $additionalFields);
        }

        return $this->globalFilterValues;
    }

    /**
     * Returns array with group parameters for uses in \CIBlock...::GetList()
     *
     * @param array $additionalFields
     * @uses groupingParams
     * @return array|bool
     */
    protected function getParamsGrouping($additionalFields = array())
    {
        if (!empty($additionalFields))
        {
            $this->addParamsGrouping($additionalFields);
        }

        return $this->groupingParams;
    }

    /**
     * Returns array with pagination parameters for uses in \CIBlock...::GetList()
     *
     * @param array $additionalFields
     * @uses navStartParams
     * @return array|bool
     */
    protected function getParamsNavStart($additionalFields = array())
    {
        if (!empty($additionalFields))
        {
            $this->addParamsNavStart($additionalFields);
        }

        return $this->navStartParams;
    }

    /**
     * Returns array with selected fields and properties for uses in \CIBlock...::GetList()
     *
     * @param array $additionalFields Additional fields
     * @param string $propsPrefix Prefix for properties keys
     * @return array
     */
    protected function getParamsSelected($additionalFields = array(), $propsPrefix = 'PROPERTY_')
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
                $fields[] = $propsPrefix.$propCode;
            }
        }

        if (is_array($additionalFields) && !empty($additionalFields))
        {
            $fields = array_merge($fields, $additionalFields);
        }

        return array_unique($fields);
    }

    protected function getProcessingMethod()
    {
        if ($this->arParams['RESULT_PROCESSING_MODE'] === 'EXTENDED')
        {
            return 'GetNextElement';
        }
        else
        {
            return 'GetNext';
        }
    }

    /**
     * Processing request of the elements
     *
     * @param \CIBlockResult $element
     * @return array
     */
    private function processingElementsResult($element)
    {
        if ($this->arParams['RESULT_PROCESSING_MODE'] === 'EXTENDED')
        {
            $arElement = $element->GetFields();
            $arElement['PROPS'] = $element->GetProperties();
        }
        else
        {
            $arElement = $element;

            foreach ($this->arParams['SELECT_PROPS'] as $propCode)
            {
                if (trim($propCode))
                {
                    $arProp = explode('.', $propCode);
                    $propCode = array_shift($arProp);
                    $propValue = $element['PROPERTY_'.$propCode.'_VALUE'];

                    if ($propValue)
                    {
                        $arElement['PROPS'][$propCode]['VALUE'] = $propValue;
                    }
                    
                    foreach ($arProp as $field)
                    {
                        $arElement['PROPS'][$propCode]['LINKED'][$field] = $element['PROPERTY_'.$propCode.'_'.$field];
                    }
                }
            }
        }

        if ($this->prepareElementsResult($arElement))
        {
            return $arElement;
        }
        else
        {
            return false;
        }
    }

    /**
     * Empty method for prepare result request of the elements
     *
     * @param \CIBlockResult $element
     * @return bool
     */
    protected function prepareElementsResult($element)
    {
        return $element;
    }

    protected function executeEpilogElements()
    {
        $this->setSeoTags();
        $this->setOgTags();
        $this->setEditButtons();
    }
}
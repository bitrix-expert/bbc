<?php
/**
 * @link http://bbc.bitrix.expert
 * @copyright Copyright © 2014-2015 Nik Samokhvalov
 * @license MIT
 */

namespace Bex\Bbc\Components;

use Bex\Bbc;
use Bex\Plugins\HermitagePlugin;
use Bex\Plugins\ElementsParamsPlugin;
use Bex\Plugins\SeoPlugin;

if(!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true)die();

if (!\Bitrix\Main\Loader::includeModule('bex.bbc')) return false;

/**
 * Component for show elements list
 *
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 */
class ElementsList extends Bbc\Basis
{
    use Bbc\ElementsTrait;

    protected $needModules = ['iblock'];

    protected $checkParams = [
        'IBLOCK_TYPE' => ['type' => 'string'],
        'IBLOCK_ID' => ['type' => 'int']
    ];

    public function plugins()
    {
        return array_merge(
            parent::plugins(),
            [
                'elementsParams' => ElementsParamsPlugin::getClass(),
                'elementsSeo' => SeoPlugin::getClass(),
                'elementsHermitage' => HermitagePlugin::getClass(),
                /*'includer' => [
                    'class' => Includer::getClass(),
                    'checkParams' => [
                        'IBLOCK_TYPE' => ['type' => 'string'],
                        'IBLOCK_ID' => ['type' => 'int']
                    ],
                    'needModules' => ['iblock']
                ]*/
            ]
        );
    }

    public function executeMain()
    {
        $elementsParams = ElementsParamsPlugin::getInstance();

        $rsElements = \CIBlockElement::GetList(
            $elementsParams->getSort(),
            $elementsParams->getFilters(),
            $elementsParams->getGrouping(),
            $elementsParams->getNavStart(),
            $elementsParams->getSelected([
                'DETAIL_PAGE_URL',
                'LIST_PAGE_URL'
            ])
        );

        if (!isset($this->arResult['ELEMENTS']))
        {
            $this->arResult['ELEMENTS'] = [];
        }

        $processingMethod = $elementsParams->getProcessingMethod();

        while ($element = $rsElements->$processingMethod())
        {
            if ($arElement = $this->processingElementsResult($element))
            {
                $this->arResult['ELEMENTS'][] = $arElement;
            }
        }

        if ($this->arParams['SET_404'] === 'Y' && empty($this->arResult['ELEMENTS']))
        {
            $this->return404();
        }

        $this->generateNav($rsElements);
        $this->setResultCacheKeys(['NAV_CACHED_DATA']);
    }
}
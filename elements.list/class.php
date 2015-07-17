<?php
/**
 * @link http://bbc.bitrix.expert
 * @copyright Copyright © 2014-2015 Nik Samokhvalov
 * @license MIT
 */

namespace Bex\Bbc\Components;

use Bex\Bbc;
use Bex\Bbc\Plugins\HermitagePlugin;
use Bex\Bbc\Plugins\ElementsParamsPlugin;
use Bex\Bbc\Plugins\SeoPlugin;

if(!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true)die();

if (!\Bitrix\Main\Loader::includeModule('bex.bbc')) return false;

/**
 * Component for show elements list
 *
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 */
class ElementsList extends Bbc\BasisComponent
{
    use Bbc\ElementsTrait;

    /**
     * @var ElementsParamsPlugin
     */
    protected $elementsParams;
    /**
     * @var SeoPlugin
     */
    protected $seo;

    public function configurate()
    {
        parent::configurate();

        $this->elementsParams = new ElementsParamsPlugin();
        $this->seo = new SeoPlugin();

        $this->pluginManager
            ->register($this->elementsParams)
            ->register($this->seo);

        $this->includer->addModule('iblock');

        $this->paramsValidator->add([
            'IBLOCK_TYPE' => ['type' => 'string'],
            'IBLOCK_ID' => ['type' => 'int']
        ]);
    }

    public function executeMain()
    {
        $rsElements = \CIBlockElement::GetList(
            $this->elementsParams->getSort(),
            $this->elementsParams->getFilters(),
            $this->elementsParams->getGrouping(),
            $this->elementsParams->getNavStart(),
            $this->elementsParams->getSelected([
                'DETAIL_PAGE_URL',
                'LIST_PAGE_URL'
            ])
        );

        if (!isset($this->arResult['ELEMENTS']))
        {
            $this->arResult['ELEMENTS'] = [];
        }

        $processingMethod = $this->elementsParams->getProcessingMethod();

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
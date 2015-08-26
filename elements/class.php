<?php
/**
 * @link http://bbc.bitrix.expert
 * @copyright Copyright © 2014-2015 Nik Samokhvalov
 * @license MIT
 */

namespace Bex\Bbc\Components;

use Bitrix\Main\Loader;
use Bex\Bbc\BasisComponent;
use Bex\Bbc\Plugins\ElementsParamsPlugin;
use Bex\Bbc\Plugins\ElementsSeoPlugin;

if (!defined('B_PROLOG_INCLUDED') || !Loader::includeModule('bex.bbc')) return;

/**
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 */
class ElementsComponent extends BasisComponent
{
    /**
     * @var ElementsSeoPlugin
     */
    public $seo;
    /**
     * @var ElementsParamsPlugin
     */
    public $elementsParams;

    public function configurate()
    {
        parent::configurate();

        $this->seo = new ElementsSeoPlugin();
        $this->elementsParams = new ElementsParamsPlugin();

        $this->pluginManager
            ->register($this->seo)
            ->register($this->elementsParams);

        $this->includer->addModule('iblock');
    }

    public function routes()
    {
        /**
         * @todo А как варьировать SECTION_ID и SECTION_CODE, ELEMENT_ID и ELEMENT_CODE?
         */

        return [
            'index' => '',
            'section' => '#SECTION_ID#/',
            'detail' => '#SECTION_ID#/#ELEMENT_ID#/'
        ];
    }

    protected function indexAction()
    {
        // May be?
        /*$elementModel = $this->elementsReader->getModel();

        $sort = ['ID' => 'ASC'];
        $addSelectFields = ['DETAIL_PAGE_URL', 'LIST_PAGE_URL'];

        $elements = $elementModel
            ->setSort($sort)
            ->addSelectFields($addSelectFields)
            ->fetchAll();

        $this->arResult['ELEMENTS'] = $elements;*/

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
            if ($arElement = $this->elementsParams->processingFetch($element))
            {
                $this->arResult['ELEMENTS'][] = $arElement;
            }
        }

        if ($this->arParams['SET_404'] === 'Y' && empty($this->arResult['ELEMENTS']))
        {
            $this->return404();
        }

        $this->elementsParams->generateNav($rsElements);
        $this->setResultCacheKeys(['NAV_CACHED_DATA']);

        $this->includeComponentTemplate('index');
    }

    protected function sectionAction()
    {
        $this->includeComponentTemplate('section');
    }

    protected function detailAction()
    {
        $this->includeComponentTemplate('detail');
    }




    /////////////////////////////////////////
    // Examples
    /////////////////////////////////////////

    public function routesUsersComponent()
    {
        return [
            'users' => [
                'template' => '',
                'method' => [
                    'GET' => 'getUser',
                    'POST' => 'addUser',
                    'OPTION' => 'option',
                ]
            ],
            'user' => [
                'template' => '#ELEMENT_ID#',
                'method' => [
                    'GET' => 'getUser',
                    'POST|PUT' => 'updateUser',
                    'DELETE' => 'deleteUser',
                ]
            ],
        ];
    }

    public function routesNewsComponent()
    {
        return [
            'list' => '',
            'section' => '#SECTION_CODE#/',
            'detail' => '#SECTION_CODE#/#ELEMENT_ID#/',
        ];
    }

    public function routesPagesComponent()
    {
        return [
            'pages' => [
                'GET' => 'getPages',
                'POST' => 'addPage'
            ],
            'page' => [
                'template' => '#PAGE_ID#/',
                'GET' => 'getPage',
                'POST|PUT' => 'updatePage',
                'DELETE' => 'deletePage',
            ]
        ];
    }
}
<?php
/**
 * @link http://bbc.bitrix.expert
 * @copyright Copyright © 2014-2015 Nik Samokhvalov
 * @license MIT
 */

namespace Bex\Bbc\Components;

use Bex\Bbc\Plugins\ElementsSelectorPlugin;
use Bex\Bbc\BasisComponent;
use Bitrix\Main\Loader;

if (!defined('B_PROLOG_INCLUDED') || !Loader::includeModule('bex.bbc')) {
    return;
}

/**
 * Component for displaying elements from info block
 *
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 */
class ElementsComponent extends BasisComponent
{
    public function plugins()
    {
        $plugins = parent::plugins();

        $plugins['elementsSeo'] = '\Bex\Bbc\Plugins\ElementsSeoPlugin';
        $plugins['elementsSelector'] = '\Bex\Bbc\Plugins\ElementsSelectorPlugin';

        return $plugins;
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
        /**
         * @var ElementsSelectorPlugin $selector
         */
        $selector = $this->getPlugin('elementsSelector');

        $rsElements = \CIBlockElement::GetList(
            $selector->getSort(),
            $selector->getFilters(),
            $selector->getGrouping(),
            $selector->getNavStart(),
            $selector->getSelected([
                'DETAIL_PAGE_URL',
                'LIST_PAGE_URL'
            ])
        );

        if (!isset($this->arResult['ELEMENTS'])) {
            $this->arResult['ELEMENTS'] = [];
        }

        $processingMethod = $selector->getProcessingMethod();

        while ($element = $rsElements->$processingMethod()) {
            if ($arElement = $selector->processingFetch($element)) {
                $this->arResult['ELEMENTS'][] = $arElement;
            }
        }

        if ($this->arParams['SET_404'] === 'Y' && empty($this->arResult['ELEMENTS'])) {
            $this->return404();
        }

        $selector->generateNav($rsElements);
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
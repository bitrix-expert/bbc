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
// todo die() for Loader?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true || !Loader::includeModule('bex.bbc')) die();

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

    }



    // TEMP

    public function routesUsers()
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

    public function routesNews()
    {
        return [
            'list' => '',
            'section' => '#SECTION_CODE#/',
            'detail' => '#SECTION_CODE#/#ELEMENT_ID#/',
        ];
    }

    public function routesPages()
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
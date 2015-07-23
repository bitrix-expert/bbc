<?php
/**
 * @link http://bbc.bitrix.expert
 * @copyright Copyright © 2014-2015 Nik Samokhvalov
 * @license MIT
 */

namespace Bex\Bbc\Components;

use Bex\Bbc\BasisComponent;

if(!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true)die();

if (!\Bitrix\Main\Loader::includeModule('bex.bbc')) return false;

/**
 * @author Nik Samokhvalov <nik@samokhvalov.info>
 */
class ElementsComponent extends BasisComponent
{
    public function routers()
    {
        /*return [
            'list' => '',
            'detail' => '#ELEMENT_ID#/'
        ];*/

        return [
            'users' => [
                'action' => 'actionUser'
            ],
            'user' => [
                'template' => '#ELEMENT_ID#',
                'action' => [
                    'GET' => 'getUser',
                    'POST|PUT' => 'updateUser',
                    'DELETE' => 'deleteUser',
                ]
            ],
            'userGroup' => [
                'template' => '#ELEMENT_ID#/group',
                'action' => [
                    'GET' => 'getUserGroups',
                    'POST|PUT' => 'updateUserGroups',
                    'DELETE' => 'deleteUserGroups',
                ]
            ],
            'groups' => [
                'template' => 'group',
                'action' => 'getGroups'
            ],
            'group' => [
                'template' => 'group/#GROUP_ID#',
                'action' => 'getGroup'
            ],
        ];
    }
}
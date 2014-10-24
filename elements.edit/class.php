<?php
/**
 * @package components
 * @subpackage basis
 * Пример создания компонента для редактирования элемента, на примере редактирования элемента инфоблока.
 * От данного компонента можно безболезненно наследоваться не только в случае компонентов.
 * Если же вы очень хотите кастомизировать ход обработки формы - необходимо создавать свой компонент.
 * @author Igor Tsupko <maicatus@gmail.com> http://bitrix.expert
 * @copyright Copyright (c) 2014, Igor Tsupko
 */
namespace Components\Basis;

if(!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true)die();

\CBitrixComponent::includeComponentClass(basename(dirname(__DIR__)).':basis');
include_once __DIR__.'/form.php';

/**
 * Work in progress…
 */
class ElementsEdit extends Basis
{
    use FormComponent;
    protected static $needModules = array('iblock');

    var $mode = '';

    /**
     * Вешаемся на событие executeProlog базиса
     * Вне нашего внимания остается всё, что прописано в executeComponent базиса - и работа с аяксом, и подключение шаблона.
     * @throws \Exception
     */
    final function executeProlog()
    {
        $this->selectMode();
        $method = $this->_get_method('check');
        $this->$method();

        $this->arResult['FORM'] = $this->restoreFormModifierFromCachedUserInput($_REQUEST);
        $this->generateForm($this->arElement);

        if ($this->isDataSubmitted())
        {
            // Запускаем проверку валидности данных
            $this->arResult['FORM'] = $this->rsForm->validate($_REQUEST);
            // Если данные валидны - пробуем сохранить, если нет - зовём коллбэк и успокаиваемся
            if ($this->rsForm->isValid()){
                $method = $this->_get_method('save');
                if ($this->$method())
                {
                    $method = $this->_get_method('on_success');
                    $this->$method();
                } else {
                    $method = $this->_get_method('on_fault');
                    $this->$method();
                }
            }else{
                $method = $this->_get_method('on_invalid');
                $this->$method();
            }
        }
    }

    final protected function _get_method($str)
    {
        $method = $str.($this->mode&&method_exists($this,"{$str}_{$this->mode}")?"_{$this->mode}":'');
        return $method;
    }

    /**
     * Определение режима работы компонента.
     * Режимов может быть несколько, а может и не быть вовсе. Если переменная $this->mode установлена,
     * то методы check, save, on_success, on_fault, on_invalid можно кастомизировать для каждого из режимов.
     * В приведённом примере с режимами edit и add можно создать отдельно save_edit и save_add, или не создавать,
     * и тогда запросы будут идти на общий save()
     */
    function selectMode()
    {
        if ($this->arParams['ID'])
        {
            $this->arElement = \CIBlockElement::GetByID($this->id)->Fetch();
            if ($this->arElement['ID'])
            {
                $this->id = $this->arParams['ID'];
                $this->mode = 'edit';
                return;
            }
        }
        $this->mode = 'add';
        return;
    }

    /**
     * Метод, генерирующий форму
     * @param $arElement
     * @param string $id_postfix
     * @return \BasicComponentForm
     */
    function generateForm($arElement, $id_postfix='')
    {
        /** @notice: тут может быть чтение необходимых данных. Делайте перегрузку метода и читайте всё, что нужно! */

        // формируем массив полей
        $fields = $this->getFormFieldsArray();
        $fields['NAME']['VALUE'] = $arElement['NAME'];

        // если есть доп. коррективы - то мы их пробрасываем
        if ($this->fields_mods)
            $fields = $this->_array_merge_recursive_distinct($fields, $this->fields_mods);

        return new \BasicComponentForm($fields, $id_postfix);
    }

    /**
     * Проверки в случае режима редактирования элемента
     * @throws \Exception
     */
    function check_edit()
    {
        if (! $this->id)
            throw new \Exception("Не указан элемент для загрузки"); // @todo: ещё warning level надо указывать!

        global $USER;
        if (! $USER->IsAdmin())
            throw new \Exception("Только администратор может редактировать этот элемент");
    }

    /**
     * Проверки в случае режима добавления элемента
     * @throws \Exception
     */
    function check_add()
    {
        global $USER;
        if (! $USER->IsAdmin())
            throw new \Exception("Только администратор может добавлять данные");
    }

    /**
     * Сохранение в том случае, если режим - добавление нового элемента
     * @return bool
     */
    function save_add()
    {
        $arSave = array(
            'NAME'=>$this->rsForm->get($this->arResult['DATA'],'NAME')
        );
        $el = new \CIBlockElement();
        $this->id = $el->Add($arSave);
        return (bool) $this->id;
    }

    /**
     * Сохранение в том случае, если режим - update элемента
     * @return bool
     */
    function save_update()
    {
        $arSave = array(
            'NAME'=>$this->rsForm->get($this->arResult['DATA'],'NAME')
        );
        $el = new \CIBlockElement();
        return $el->Update($this->id,$arSave);
    }

    /**
     * Описываем поля, которые выводятся в форме.
     * @return array of BasicComponentForm::$field_example
     */
    function getFormFieldsArray()
    {
        return array(
            'NAME'=>array(
                'TITLE' => 'Название'
            )
        );
    }

    /**
     * @notice: А можно было создать on_success_add и on_success_edit - и тогда он будет подтягивать их, в соответствии с режимами
     */
    function on_success()
    {
        $this->page = 'success';
    }

    /**
     * @notice: А можно было создать on_fault_add и on_fault_edit - и тогда он будет подтягивать их, в соответствии с режимами
     */
    function on_fault()
    {
        $this->page = 'fail';
    }

    /**
     * @notice: А можно было создать on_invalid_add и on_invalid_edit - и тогда он будет подтягивать их, в соответствии с режимами
     */
    function on_invalid()
    {
        /** @notice: а нам ничего не надо особенного в этой ситуации */
    }
}
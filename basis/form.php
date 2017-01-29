<?php
/**
 * Form components
 *
 * @package components
 * @subpackage basis
 * @author Igor Tsupko <maicatus@gmail.com> http://bitrix.expert
 * @copyright Copyright (c) 2014, Igor Tsupko
 */
namespace Components\Basis;

use Bitrix\Main;
use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;

if(!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true)die();

Loc::loadMessages(__DIR__.'/class.php');

trait FormComponent
{
    static $SESS_FLASH_KEY='FLASH_MESSAGE';
    static $SESS_FORMCACHE_KEY='FLASH_MESSAGE';

    /**
     * @var int айдишник текущего обрабатываемого элемента
     */
    var $id = 0;

    /**
     * @var array данные текущего обрабатываемого элемента
     */
    var $arElement = array();

    /**
     * @var \BasicComponentForm объект для работы с формой
     */
    var $rsForm;

    /**
     * @var array служебный массив с примесями к массиву $fields
     */
    var $fields_mods = array();

    /**
     * Запоминаем флэш-сообщение об ошибке или что-то вроде того, чтобы потом один раз показать пользователю
     * после перезагрузки страницы.
     * @param $str
     */
    function setFlashMessage($str)
    {
        $_SESSION[self::$SESS_FLASH_KEY][$this->id] = $str;
    }

    /**
     * Выводим флэш-сообщение об ошибке и удаляем его из памяти.
     */
    function echoFlashMessage()
    {
        // Если передано какое-то сообщение об ошибке из другого компонента - его нужно отобразить.
        if (($this->arParams['SILENT_MODE'] != 'Y') && ($str = $_SESSION[self::$SESS_FLASH_KEY][$this->id])) {
            echo $str;
            unset($_SESSION[self::$SESS_FLASH_KEY][$this->id]);
        }
    }


    /**
     * Сохраняем данные, введённые пользователем в форму.
     * Данные могут по каким-то причинам быть не сохранены в ЕТМ, и на этот случай нужно закэшировать их.
     * @param $data
     */
    function cacheUserInput($data)
    {
        $_SESSION[self::$SESS_FORMCACHE_KEY][$this->id] = $data;
    }

    /**
     * Восстанавливаем из кэша пользовательского ввода данные для формы.
     * На предыдущем шаге пользователь мог что-то ввести, и нам может быть нужно показать ему эти данные на новом шаге,
     * даже если данные не ушли в ЕТМ
     * @params $arData array данные
     * @return array
     */
    function restoreFormModifierFromCachedUserInput($arData)
    {
        // вытаскиваем из сессии прошлые заполненные юзером поля
        $cacheData = $_SESSION[self::$SESS_FORMCACHE_KEY][$this->id];

        /**
         * @notice: Массив fields_mods позже будет слит с массивом, описывающим требуемые поля и может,
         * таким образом, модифицировать их параметры.
         * Ниже приведён пример того, как сделать disable'нутым поле NAME
         * и установить ему другой тайтл
         * DISABLED и TITLE - это не какие-то магические константы, а ключи
         * массива, описывающего поле. Пример такого массива можно посмотерть
         * в BasicComponentForm::$field_example
         *
         * Перегружая данный метод, Вы можете прописать свои fields_mods
         */
        // $this->fields_mods['NAME']['DISABLED'] = true;
        // $this->fields_mods['NAME']['TITLE'] = 'Новый тайтл';

        $this->fields_mods = array();

        return $cacheData;
    }


    /***
     * Метод, генерирующий объект формы на основании переданных ему настроек.
     * С полученным объектом уже можно оперировать для того, чтобы выводить поля, валидировать данные и т.п.
     *
     * @param string|null $id_postfix если на странице находится несколько форм, то обязательно указывать id_postfix, чтобы JS-события привязывались к корректным id'шникам
     *
     * Перегрузкой метода можно добиваться генерации нужной формы.
     * @notice: если вы делаете перегрузку, то можете добавить новые параметры
     *
     * @return \BasicComponentForm возвращает сгенерированный объект формы
     */
    function generateForm($arElement, $id_postfix='')
    {
        /** @notice: тут может быть чтение необходимых данных. Делайте перегрузку метода и читайте всё, что нужно! */

        // формируем массив полей
        $fields = $this->getFormFieldsArray();

        // если есть доп. коррективы - то мы их пробрасываем
        if ($this->fields_mods)
            $fields = $this->_array_merge_recursive_distinct($fields, $this->fields_mods);

        return new \BasicComponentForm($fields, $id_postfix);

    }

    /**
     * Служебный метод, склеивающий два массива.
     * Аналогичен array_merge, но позволяет корректно склеивать массивы большой вложенности
     * @param $array1
     * @param $array2
     * @return array
     */
    protected function _array_merge_recursive_distinct($array1, $array2)
    {
        if (!is_array($array1) && is_array($array2)) {
            return $array2;
        }
        if (is_array($array1) && !is_array($array2)) {
            return $array1;
        }
        if (!is_array($array1) && !is_array($array2)) {
            return array();
        }
        $merged = $array1;
        foreach ($array2 as $key => &$value) {
            if (is_array($value) && isset ($merged [$key]) && is_array($merged [$key])) {
                $merged [$key] = $this->_array_merge_recursive_distinct($merged [$key], $value);
            } else {
                $merged [$key] = $value;
            }
        }

        return $merged;
    }

    /**
     * Возвращает массив, описывающий поля.
     * Этот массив должен иметь вид, понятный классу \BasicComponentForm
     * @return array of BasicComponentForm::$field_example
     */
    abstract function getFormFieldsArray();


    /**
     * Метод, проверяющий, является ли текущий элемент разрешённым к обработке
     * @throws \Exception
     */
    function check() {}


    /**
     * Сохранение данных
     * @return bool
     */
    function save() {}

    /**
     * Коллбэк, вызываемые при успешном сохранении
     */
    function on_success() {}

    /**
     * Коллбэк, вызываемый при провале сохранения
     */
    function on_fault() {}

    /**
     * Коллбэк, вызываемый, если данные оказались не валидны
     */
    function on_invalid() {}

    /**
     * Пришли ли данные с формы?
     * @return bool
     */
    function isDataSubmitted(){
        return $_POST;
    }
}
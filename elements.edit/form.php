<?php
/**
 * Класс, олицитворяющий веб-форму. Он целиком берёт на себя функции генерации html-кода формы, валидации данных и т.п.
 *
 * Class BasicComponentForm
 *
 * Основные публичные методы
 *  - валидация
 *      validate($arData)
 *      isValid()
 *  - вывод поля
 *      field($arData, $selector)
 *  - вывод данных
 *      field($arData, $selector)
 *  - получение данных
 *      get($arData, $selector)
 *
 *  Класс позволяет полностью абстрагироваться от работы с данными в форме и сосредоточиться на
 *  бизнес-логике.
 *
 * Основные публичные типы данных:
 *  - $arData = $_REQUEST
 *  - $selector - селекторы вида "user.name". Подробнее о селекторах - смотри метод ->array_get()
 */
class BasicComponentForm
{
    /** @var array пример поля */
    protected $field_example = array(
        'REQUIRED'=>true,
        'TITLE'=>'Тайтл',
        'TYPE'=>'select', // Тип вывода, если он задан - вызывается соответствующий метод. Для приведённого примера это будет метод field_select()
        'VALIDATE'=>'REGEX',
        'REGEX'=>'/([0-9]{10})/',
        'DISABLED' => false,
        'CLASS' => 'input-type__wrap',
        'DATA'=>array( // Данные, которые нужны кастомному методу. В приведённом примере select'у, очевидно, нужны значения для списка
            array('VALUE'=>'MOON','TITLE'=>'Луна'),
            array('VALUE'=>'MARS','TITLE'=>'Марс'),
            array('VALUE'=>'SUN','TITLE'=>'Солнце'),
        )
    );

    /** @var array многомерный массив с полями */
    public $fields;

    var $valid = true;
    var $arErrors = array();
    var $id_postfix = '';

    /**
     * @param $fields
     * @param string|null $id_postfix если на странице находится несколько форм, то обязательно указывать id_postfix, чтобы JS-события привязывались к корректным id'шникам
     */
    function __construct($fields, $id_postfix = null)
    {
        $this->fields = $fields;
        if ($id_postfix)
            $this->id_postfix = '_'.$id_postfix;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return $this->valid;
    }

    /**
     * @return array
     */
    function getErrors()
    {
        return $this->arErrors;
    }

    /**
     * @param $selector
     * @param bool $fieldsOnly
     * @return array|mixed
     */
    function getFields($selector, $fieldsOnly = true)
    {
        // Выбираем тот кусок дерева, что нужен
        if ($selector)
            $tmp = $this->array_get($this->fields,$selector);
        else
            $tmp = $this->fields;

        // Возвращаем либо только поля, либо всё, вместе с подструктурами
        if ($fieldsOnly)
        {
            $ar = array();
            foreach ($tmp as $k=>$f)
                if ($f['TITLE'])
                    $ar[$k] = $f;
            return $ar;
        } else {
            return $tmp;
        }
    }

    /******************************************************************************************************************
     ******************************************************************************************************************
     *                                          ВАЛИДАЦИЯ
     *
     * Код ниже посвящён проверке полей на корректность заполнения
     */

    /**
     * Основной метод для валидации.
     * @param $arData
     * @return mixed
     */
    public function validate(&$arData)
    {
        $this->validateLevel($arData['data'],$this->fields,$this->arErrors,'');
        return $arData['data'];
    }

    protected function validateLevel(&$arData, $fields, &$arErrors,$selector)
    {
        foreach ($fields as $k=>$subfields)
        {
            if (! $subfields['TITLE']) {
                $this->validateLevel($arData[$k], $subfields, $arErrors[$k], $selector?$selector.$this->delimiter.$k:$k );
            } else {
                // Общая проверка на заполненность
                if ($subfields['REQUIRED'] && ! $arData[$k])
                {
                    $this->valid = false;
                    $arErrors[$k] = 'Поле не заполнено';
                }

                // Если есть кастомная функция валидации и данные в поле - запускаем кастомную валидацию //
                if ($arData[$k] && $subfields['VALIDATE'] && method_exists($this,'validate_'.$subfields['VALIDATE']))
                {
                    if ($selector)
                        $fieldOptions = $this->array_get($this->fields,$selector.$this->delimiter.$k);
                    else
                        $fieldOptions = $this->fields[$k];
                    $this->{'validate_'.$subfields['VALIDATE']}($selector, $arData[$k], $fieldOptions, $arErrors[$k], $arData);
                }
            }
        }
    }

    /**
     * Пример того, как пишется валидатор
     * @param $selector
     * @param $value
     * @param $fieldOptions
     * @param $error
     * @param $arData
     */
    protected function validate_EXAMPLE($selector, $value, &$fieldOptions, &$error, &$arData)
    {
        if ($value!='Котик')
        {
            $this->valid = false;
            $error = 'Фигню написал';
        }
    }

    protected function validate_REGEX($selector, $value, &$fieldOptions, &$error, &$arData)
    {
        if (! preg_match($fieldOptions['REGEX'],$value))
        {
            $this->valid = false;
            $error = 'Имя должно быть написано латинскими буквами';
        }
    }

    public function validate_SELECT($selector, $value, &$fieldOptions, &$error, &$arData)
    {
        $in_arr = false;
        foreach ($fieldOptions['DATA'] as $a)
        {
            if ($a['VALUE']==$value)
                $in_arr = true;
        }
        if (! $in_arr)
        {
            $this->valid = false;
            $error = 'Должно быть выбрано значение из списка';
        }
    }

    public function validate_EMAIL($selector, $value, &$fieldOptions, &$error, &$arData)
    {
        if (! preg_match('/^(.+\@.+\..{1,10})$/i',$value))
        {
            $this->valid = false;
            $error = 'Не похоже на е-мэйл';
        }
    }


    protected function validate_DATE($selector, $value, &$fieldOptions, &$error, &$arData)
    {
        if (!preg_match('/^(?:(?:31(\/|-|\.)(?:0?[13578]|1[02]))\1|(?:(?:29|30)(\/|-|\.)(?:0?[1,3-9]|1[0-2])\2))(?:(?:1[6-9]|[2-9]\d)?\d{2})$|^(?:29(\/|-|\.)0?2\3(?:(?:(?:1[6-9]|[2-9]\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00))))$|^(?:0?[1-9]|1\d|2[0-8])(\/|-|\.)(?:(?:0?[1-9])|(?:1[0-2]))\4(?:(?:1[6-9]|[2-9]\d)?\d{2})$/',$value))
        {
            $this->valid = false;
            $error = 'Некорретно заполнено поле "Дата"';
        }
    }

    protected function validate_EXPIREDDATE($selector, $value, &$fieldOptions, &$error, &$arData)
    {
        if (!preg_match('/^(?:(?:31(\/|-|\.)(?:0?[13578]|1[02]))\1|(?:(?:29|30)(\/|-|\.)(?:0?[1,3-9]|1[0-2])\2))(?:(?:1[6-9]|[2-9]\d)?\d{2})$|^(?:29(\/|-|\.)0?2\3(?:(?:(?:1[6-9]|[2-9]\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00))))$|^(?:0?[1-9]|1\d|2[0-8])(\/|-|\.)(?:(?:0?[1-9])|(?:1[0-2]))\4(?:(?:1[6-9]|[2-9]\d)?\d{2})$/',$value))
        {
            $this->valid = false;
            $error = 'Некорретно заполнено поле "Дата"';
        }
        if (date('Y-m-d',strtotime($value))>date('Y-m-d'))
        {
            $this->valid = false;
            $error = 'Дата должна быть в прошлом';
        }
    }

    protected function validate_FUTUREDATE($selector, $value, &$fieldOptions, &$error, &$arData)
    {
        if (!preg_match('/^(?:(?:31(\/|-|\.)(?:0?[13578]|1[02]))\1|(?:(?:29|30)(\/|-|\.)(?:0?[1,3-9]|1[0-2])\2))(?:(?:1[6-9]|[2-9]\d)?\d{2})$|^(?:29(\/|-|\.)0?2\3(?:(?:(?:1[6-9]|[2-9]\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00))))$|^(?:0?[1-9]|1\d|2[0-8])(\/|-|\.)(?:(?:0?[1-9])|(?:1[0-2]))\4(?:(?:1[6-9]|[2-9]\d)?\d{2})$/',$value))
        {
            $this->valid = false;
            $error = 'Некорретно заполнено поле "Дата"';
        }
        if (date('Y-m-d',strtotime($value))<date('Y-m-d'))
        {
            $this->valid = false;
            $error = 'Дата должна быть в будущем';
        }
    }

    protected function validate_HIDDEN($selector, $value, &$fieldOptions, &$error, &$arData)
    {
        if ($value!=$fieldOptions['FORCE_VALUE'])
        {
            $this->valid = false;
            $error = 'Критичная ошибка! Пожалуйста, сообщите администрации сайта.';
            CEventLog::Add(array(
                "MODULE_ID" => "BasicComponentForm",
                "ITEM_ID" => '',
                "DESCRIPTION" => "При заполнении формы скрытое свойство ".var_export($fieldOptions,true)." каким-то образом оказалось заполненным не тем значением",
            ));
        }
    }


    /******************************************************************************************************************
     ******************************************************************************************************************
     *                                          ВЫВОД ПОЛЕЙ
     *
     * Код ниже посвящён выводу полей
     */

    var $tabIndex=0;

    /**
     * Основной метод, через который выводятся поля.
     * Предназначен для вызова его из шаблонов.
     * @param $arData array Все данные для полей
     * @param $selector
     * @return null
     * @internal param $field_code
     * @internal param $arguments
     */
    public function field(&$arData, $selector)
    {
        /**
         * @var $fieldOptions array опции рассматриваемого свойства,
         * формат полностью аналогичен $this->field_example
         */
        $fieldOptions = $this->array_get($this->fields,$selector);
        /**
         * @var $value string текущее значение, которое пришло в $_REQUEST с формы
         */
        $value = $this->array_get($arData,$selector);
        /**
         * @var $errors string|null ошибка, полученная в результате работы валидаторов
         */
        $error = $this->array_get($this->arErrors,$selector);
        /**
         * @var $html_name string строка, описывающая название, под которым данное свойство должно
         * заполняться в HTML-форме.
         * @notice: Не меняйте расчёт этого значения - под него много чего завязано в веб-форме, в том числе
         * значения, которые отправляются валидатору и т.п.t
         */
        $html_name = 'data'.implode('',array_map(function($x){return "[$x]";},explode($this->delimiter,$selector)));
        $html_id = preg_replace('/[^a-z0-9]/i','_',$html_name);
        $this->tabIndex++;

        // Вызываем кастомную функцию вывода, если надо //
        if (!$fieldOptions['TYPE'] || !method_exists($this,'field_'.$fieldOptions['TYPE']))
            $fieldOptions['TYPE'] = 'default';
        return $this->{'field_'.$fieldOptions['TYPE']}($selector, $value, $fieldOptions, $error, $arData, $html_name, $html_id);
    }


    protected function field_default($selector, $value, &$fieldOptions, &$error, &$arData, $html_name, $html_id)
    {
        ?>
        <div class="input-type__wrap  <? if ($error): ?>input-type__wrap_state_error<? endif ?> <?=$fieldOptions['DISABLED']?'input-type__wrap_hide':''?> <?=$fieldOptions['DIV_CLASS']?>">
            <label for="<?=$html_id?><?=$this->id_postfix?>" class="input-type__label"><?=$fieldOptions['TITLE']?></label>
            <input type="text" id="<?=$html_id?><?=$this->id_postfix?>" name="<?=$html_name?>"
                <?=$fieldOptions['REGEX']?"data-validate=\"{$fieldOptions['REGEX']}\"":''?>
                   data-message="<?=$fieldOptions['JS_ERROR']?$fieldOptions['JS_ERROR']:'Поле заполнено неверно'?>"
                   class="input-type__input <?=$fieldOptions['REGEX']?"js-input-validate":''?> <?=$fieldOptions['CLASS']?>"
                   value="<?= $fieldOptions['DATA']['VALUE']?$fieldOptions['DATA']['VALUE']:htmlspecialcharsEx($value)?>"
                   tabindex="<?=$this->tabIndex?>">
            <span class="input-type__error"><?=$error?></span>
        </div>
        <? if ($fieldOptions['TOOLTIP']): ?>
        <div class="b-payment-info__icon">
            <div data-always="data-always" class="tooltip-container js-tooltip b-payment-info__icon-tooltip tooltip-container_fixed_200">
                <div class="tooltip"></div>
                <div class="tooltip__hint"><?=$fieldOptions['TOOLTIP']?></div>
            </div>
        </div>
        <? endif ?>
        <?
    }

    protected function field_SELECT($selector, $value, &$fieldOptions, &$error, &$arData, $html_name, $html_id)
    {
        $arSelected = array();
        foreach ($fieldOptions['DATA'] as $k=>$ar)
            if ($ar['VALUE']==$value)
                $arSelected = $ar;
        ?>
        <div class="input-type__wrap <? if ($error): ?>input-type__wrap_state_error<? endif ?> <?=$fieldOptions['DISABLED']?'input-type__wrap_hide':''?> <?=$fieldOptions['CLASS']?>">
            <label for="<?=$html_id?><?=$this->id_postfix?>" class="input-type__label"><?=$fieldOptions['TITLE']?></label>
            <div role="combobox" class="jelect jelect_border_blue">
                <input id="<?=$html_id?><?=$this->id_postfix?>" name="<?=$html_name?>" value="<?=htmlspecialcharsEx($value)?>" data-text="<?=htmlspecialcharsEx($value)?>"
                       type="text" <?=$fieldOptions['REGEX']?"data-validate=\"{$fieldOptions['REGEX']}\"":''?>
                       class="jelect-input <?=$fieldOptions['REGEX']?"js-input-validate":''?>" tabindex="<?=$this->tabIndex?>" >
                <div tabindex="0" role="button" class="jelect-current"><?=$arSelected['TITLE']?></div>
                <ul class="jelect-options">
                    <li data-val="" tabindex="-1" role="option" class="jelect-option jelect-option_state_active"></li>
                    <? foreach ($fieldOptions['DATA'] as $k=>$ar): ?>
                        <li data-val="<?=$ar['VALUE']?>" tabindex="-1" role="option" class="jelect-option <?=($ar['VALUE']==$value)?'jelect-option_state_active':''?>"><?=$ar["TITLE"]?></li>
                    <? endforeach ?>
                </ul>
            </div><span class="input-type__error"><?=$error?></span><span class="input-type__icon input-type__icon_info"></span>
        </div>
        <? if ($fieldOptions['TOOLTIP']): ?>
        <div class="b-payment-info__icon">
            <div data-always="data-always" class="tooltip-container js-tooltip b-payment-info__icon-tooltip tooltip-container_fixed_200">
                <div class="tooltip"></div>
                <div class="tooltip__hint"><?=$fieldOptions['TOOLTIP']?></div>
            </div>
        </div>
    <? endif ?>
    <?
    }


    /******************************************************************************************************************
     ******************************************************************************************************************
     *                                          ВЫДАЧА ДАННЫХ
     *
     *
     */
    public function get(&$arData,$selector)
    {
        return $this->array_get($arData,$selector);
    }


    /******************************************************************************************************************
     ******************************************************************************************************************
     *                                          СЛУЖЕБНОЕ
     *
     * Методы данного класса умеют искать по многомерному массиву с помощью селекторов.
     * Селекторы могут иметь, например, такой вид:
     *     user.name
     * Это означает, что будет найден элемент
     *     $array['user']['name']
     * Машина, занимающяся поиском с помощью селекторов - ниже.
     */


    var $delimiter = '.';
    /**
     * Get an item from an array using "underscore" notation.
     * Stolen from Lavarel framework and corrected after that for our Bitrix realities.
     *
     * <code>
     *      // Get the $array['user']['name'] value from the array
     *      $name = array_get($array, 'user'.$this->delimiter.'name');
     *
     *      // Return a default from if the specified item doesn't exist
     *      $name = array_get($array, 'user'.$this->delimiter.'name', 'Taylor');
     * </code>
     *
     * @param  array   $array
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    protected function array_get($array, $key, $default = null)
    {
        if (is_null($key)) return $array;

        // To retrieve the array item using dot syntax, we'll iterate through
        // each segment in the key and look for that value. If it exists, we
        // will return it, otherwise we will set the depth of the array and
        // look for the next segment.
        foreach (explode($this->delimiter, $key) as $segment)
        {
            if ( ! is_array($array) or ! array_key_exists($segment, $array))
            {
                return (is_callable($default) and ! is_string($default)) ? call_user_func($default) : $default;
            }

            $array = $array[$segment];
        }

        return $array;
    }



}
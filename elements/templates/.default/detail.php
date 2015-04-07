<?if(!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true)die();?>

<?$APPLICATION->IncludeComponent('bbc:elements.detail', '.default', array(
        'IBLOCK_TYPE' => $arParams['IBLOCK_TYPE'],
        'IBLOCK_ID' => $arParams['IBLOCK_ID'],
        'ELEMENT_ID' => $arResult['VARIABLES']['ELEMENT_ID'],
        'ELEMENT_CODE' => $arResult['VARIABLES']['ELEMENT_CODE'],
        'SELECT_FIELDS' => $arParams['DETAIL_SELECT_FIELDS'],
        'SELECT_PROPS' => $arParams['DETAIL_SELECT_PROPS'],
        'RESULT_PROCESSING_MODE' => $arParams['DETAIL_RESULT_PROCESSING_MODE'],
        'ADD_SECTIONS_CHAIN' => $arParams['ADD_SECTIONS_CHAIN'],
        'ADD_ELEMENT_CHAIN' => $arParams['ADD_ELEMENT_CHAIN'],
        'CACHE_GROUPS' => $arParams['CACHE_GROUPS'],
        'CACHE_TYPE' => $arParams['CACHE_TYPE'],
        'CACHE_TIME' => $arParams['CACHE_TIME'],
        'SET_404' => $arParams['SET_404'],
        'DATE_FORMAT' => $arParams['DETAIL_DATE_FORMAT'],
        'SET_SEO_TAGS' => $arParams['SET_SEO_TAGS']
    ),
    $component
);?>
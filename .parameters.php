<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

// Типы почтовых событий
$site = ($_REQUEST["site"] <> ''? $_REQUEST["site"] : ($_REQUEST["src_site"] <> ''? $_REQUEST["src_site"] : false));
$arFilter = Array("TYPE_ID" => "FEEDBACK_FORM", "ACTIVE" => "Y");
if($site !== false)
	$arFilter["LID"] = $site;

$arEvent = Array();
$dbType = CEventMessage::GetList($by="ID", $order="DESC", $arFilter);
while($arType = $dbType->GetNext())
	$arEvent[$arType["ID"]] = "[".$arType["ID"]."] ".$arType["SUBJECT"];

// список типов полей
$fieldsTypes = array(
    "text" => "Строка",
    "number" => "Число",
    "tel" => "Номер телефона",
    "email" => "Email",
    "textarea" => "Текст",
    "date" => "Дата",
    "list" => "Список",
    "file" => "Файл",
);

$arComponentParameters = array(
    "GROUPS" => array(
        "FIELDS" => array(
        	"NAME" => "Данные формы"
		),
		"LISTS" => array(
			"NAME" => "Списки для полей-списков"
		),
		"ELSE" => array(
			"NAME" => "Дополнителные параметры"
		),
		"CAPCHA" => array(
			"NAME" => "Защита формы"
		),
    ),
	"PARAMETERS" => array(
    
        "FORM_TITLE" => array(
    		"PARENT" => "FIELDS",
    		"NAME" => "Заголовок формы",
    		"TYPE" => "STRING",
    	), 
        
        "FIELDS_LIST" => array(
    		"PARENT" => "FIELDS",
    		"NAME" => "Список полей",
    		"TYPE" => "STRING",
    		"VALUES" => array(),
    		"MULTIPLE" => "Y",
    		"DEFAULT" => array("Имя","Номер телефона","Email","Сообщение"),
    		"ADDITIONAL_VALUES" => "Y",
            "REFRESH" => "Y",
    	), 
        
		"OK_TEXT" => Array(
			"NAME" => "Сообщение об успешной отправке", 
			"TYPE" => "STRING",
			"DEFAULT" => GetMessage("MFP_OK_TEXT"), 
			"PARENT" => "ELSE",
		),
		"EMAIL_TO" => Array(
			"NAME" => "Email администратора (если пустое умолчанию берется из настроек сайта)",
			"TYPE" => "STRING",
			"DEFAULT" => htmlspecialcharsbx(COption::GetOptionString("main", "email_from")), 
			"PARENT" => "ELSE",
		),
		"EVENT_MESSAGE_ID" => Array(
			"NAME" => "Почтовый шаблон", 
			"TYPE"=>"LIST", 
			"VALUES" => $arEvent,
			"DEFAULT"=>"", 
			"MULTIPLE"=>"Y", 
			"COLS"=>25, 
			"PARENT" => "ELSE",
		),            
        "AJAX_MODE" => array(),           
        "USE_CAPTCHA" => Array(
			"NAME" => "Показывать капчу Bitrix (для незарегистрированных пользователей)", 
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N", 
			"PARENT" => "CAPCHA",
		),
		"USE_RECAPTCHA" => Array(
			"NAME" => "Показывать капчу от Google (для незарегистрированных пользователей)", 
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N", 
			"PARENT" => "CAPCHA",
            "REFRESH" => "Y",
		),
	)
);

if($arCurrentValues['USE_RECAPTCHA'] == 'Y'){
    $arComponentParameters["PARAMETERS"]['FIRE_SITE_KEYELD'] = array(
    	"NAME" => 'Ключ',
		"TYPE" => "STRING",
		"PARENT" => "CAPCHA",
    );
    $arComponentParameters["PARAMETERS"]['RE_SEC_KEY'] = array(
    	"NAME" => 'Секретный ключ',
		"TYPE" => "STRING",
		"PARENT" => "CAPCHA",
    );    
}

foreach($arCurrentValues['FIELDS_LIST'] as $key => $value){
    if($value){
        $fieldsList[$key] = $value;
        $arComponentParameters["PARAMETERS"]['FIELD_'.$key.'_TYPE'] = array(
        	"NAME" => 'Тип поля "'.$value.'"',
    		"TYPE" => "LIST",
    		"DEFAULT" => "INPUT", 
    		"PARENT" => "FIELDS",
            "DEFAULT" => "input",
            "VALUES" => $fieldsTypes,
            "REFRESH" => "Y",
        );
    }
}

foreach($arCurrentValues['FIELDS_LIST'] as $key => $value){
    if($arCurrentValues['FIELD_'.$key.'_TYPE'] == 'list'){
        $arComponentParameters["PARAMETERS"]['FIELD_'.$key.'_LIST'] = array(
        	"NAME" => 'Список для поля "'.$value.'"',
    		"PARENT" => "LISTS",
    		"TYPE" => "STRING",
    		"VALUES" => array(),
    		"MULTIPLE" => "Y",
    		"DEFAULT" => array(),
    		"ADDITIONAL_VALUES" => "Y"
        );
    }
}
$arComponentParameters["PARAMETERS"]['REQUIRED_FIELDS'] = array(
	"NAME" => 'Поля обязательные для заполнения',
	"TYPE" => "LIST",
    "MULTIPLE" => "Y",
	"DEFAULT" => array(0,1,2,3), 
	"PARENT" => "FIELDS",
    "VALUES" => $fieldsList,
);

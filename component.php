<?if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();

$arResult["PARAMS_HASH"] = md5(serialize($arParams).$this->GetTemplateName());

if($USER->IsAuthorized()){
    $arParams["USE_CAPTCHA"] = 'N';
    $arParams["USE_RECAPTCHA"] = 'N';    
}

$arParams["EVENT_NAME"] = trim($arParams["EVENT_NAME"]);
if($arParams["EVENT_NAME"] == '') $arParams["EVENT_NAME"] = "FEEDBACK_FORM";

$arFields["EMAIL_TO"] = trim($arParams["EMAIL_TO"]);
if($arFields["EMAIL_TO"] == ''){
    $rsSites = CSite::GetByID(SITE_ID);
    $arSite = $rsSites->Fetch();
    if($arSite['EMAIL']){
        $arFields["EMAIL_TO"] = $arSite['EMAIL'];
    }else{
        $arFields["EMAIL_TO"] = COption::GetOptionString("main", "email_from");
    }
}

$arParams["OK_TEXT"] = trim($arParams["OK_TEXT"]);
if($arParams["OK_TEXT"] == '') $arParams["OK_TEXT"] = "Cпасибо, Ваше сообщение принято.";

if( ($arParams['USE_RECAPTCHA'] == 'Y') && $arParams['FIRE_SITE_KEYELD'] && $arParams['RE_SEC_KEY'] ){
    $APPLICATION->AddHeadScript('https://www.google.com/recaptcha/api.js?hl=ru');
    @require_once 'ReCapcha.php';
    define("RE_SITE_KEY",$arParams['FIRE_SITE_KEYELD']);
    define("RE_SEC_KEY",$arParams['RE_SEC_KEY']);    
}else $arParams["USE_RECAPTCHA"] = 'N';

foreach($arParams['FIELDS_LIST'] as $id => $field) if( !$field ) unset($arParams['FIELDS_LIST'][$id]);

if( ($_REQUEST["submit"] <> '') && ( !isset($_REQUEST["PARAMS_HASH"]) || ($arResult["PARAMS_HASH"] === $_REQUEST["PARAMS_HASH"]) ) ){
    
	$arResult["ERRORS"] = array();
    
	if(check_bitrix_sessid()){

        foreach($arParams['FIELDS_LIST'] as $id => $field){
            
            // проврка обязательных полей
            if(in_array($id,$arParams['REQUIRED_FIELDS'])){
                if($arParams['FIELD_'.$id.'_TYPE'] == 'file'){
                    if(!$_FILES['FIELDS']['tmp_name'][$id])
                        $arResult["ERRORS"]['FIELD'.$id] = "Файл '".$field."' обязательно для заполнения";
                }elseif( strlen($_REQUEST['FIELDS'][$id]) <= 1 )			    
                    $arResult["ERRORS"]['FIELD'.$id] = "Поле '".$field."' обязательно для заполнения";
            }
                        
            // проверка типов полей
            if( $_REQUEST['FIELDS'][$id] && ( $arParams['FIELD_'.$id.'_TYPE'] == 'email' ) && !check_email($_REQUEST['FIELDS'][$id]) ){
                $arResult["ERRORS"]['FIELD'.$id] = "Введите корректный email";
            }    
        }
        
		if($arParams["USE_CAPTCHA"] == "Y"){
			include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/captcha.php");
			$captcha_code = $_REQUEST["captcha_sid"];
			$captcha_word = $_REQUEST["captcha_word"];
			$cpt = new CCaptcha();
                        
			$captchaPass = COption::GetOptionString("main", "captcha_password", "");
			if (strlen($captcha_word) > 0 && strlen($captcha_code) > 0)
			{
				if (!$cpt->CheckCodeCrypt($captcha_word, $captcha_code, $captchaPass))
					$arResult["ERRORS"]['CAPCHA'] = "Код c картинки введен не правильно";
			}
			else
				$arResult["ERRORS"]['CAPCHA'] = "Введите код с картинки";

		}	
        
        if( $arParams['USE_RECAPTCHA'] == 'Y' ){
            $recaptcha = new \ReCaptcha\ReCaptcha(RE_SEC_KEY);
            $resp = $recaptcha->verify($_REQUEST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);

            if (!$resp->isSuccess()){
                $arResult["ERRORS"]["CAPCHA"] = "Подтвердите, что Вы не робот";
            } 
		}
        		
		if(empty($arResult["ERRORS"])){
            foreach($arParams['FIELDS_LIST'] as $id => $field){
                if(($arParams['FIELD_'.$id.'_TYPE'] == 'file')){ 
                    if($_FILES['FIELDS']['tmp_name'][$id]){
                        $thereIsFiles++;
                        $arFields['TEXT'] .= $arParams['FIELDS_LIST'][$id].': Прикреплен снизу <br>';
                    }
                }else
                    $arFields['TEXT'] .= $arParams['FIELDS_LIST'][$id].': '.$_REQUEST['FIELDS'][$id].'<br>';
            }
            
			if(!empty($arParams["EVENT_MESSAGE_ID"])){
				foreach($arParams["EVENT_MESSAGE_ID"] as $v)
					if(IntVal($v) > 0){
                        if($thereIsFiles)
                            CEvent::Send($arParams["EVENT_NAME"], SITE_ID, $arFields, "N", IntVal($v), $_FILES['FIELDS']['tmp_name']);
                        else
                            CEvent::Send($arParams["EVENT_NAME"], SITE_ID, $arFields, "N", IntVal($v));
                    }
			}else{
                if($thereIsFiles)
				    CEvent::Send($arParams["EVENT_NAME"], SITE_ID, $arFields, "N", '', $_FILES['FIELDS']['tmp_name']);
                else
                    CEvent::Send($arParams["EVENT_NAME"], SITE_ID, $arFields);
            }
			
			LocalRedirect($APPLICATION->GetCurPageParam("success=".$arResult["PARAMS_HASH"], Array("success")));
		}
		
	}else{
		$arResult["ERRORS"]['MESSAGE'] = "Время сессии истекло.";
    }
}elseif($_REQUEST["success"] == $arResult["PARAMS_HASH"]){
    
	$arResult["OK_MESSAGE"] = $arParams["OK_TEXT"];
    
}

if($arParams["USE_CAPTCHA"] == "Y") $arResult["capCode"] =  htmlspecialcharsbx($APPLICATION->CaptchaGetCode());

$this->IncludeComponentTemplate();

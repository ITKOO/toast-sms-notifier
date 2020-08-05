# toast-sms-notifier

🔗 Toast SMS api Document : https://docs.toast.com/ko/Notification/SMS/ko/api-guide/
<br><br>

<h4>1. Create instance of ToastSMS class </h4>

```
$toastSMS = new ToastSMS("api-key", "sender phone number");
```

<h4>2. Create array of recipient's information </h4>

```
/*case 1 : array(phone number array)*/
$recipientList = array("01012345678", "01022222222");

/*case 2 : a phone number */
$recipientList = "01012345678";

/*case 3 : Object Array(ToastSMSRecipient class) */
$recipient = new ToastSMSRecipient("01012345678");
// $recipient->setCountryCode(82); // available to set options
array_push($recipientList, $recipient);
```
<h4>3. Send message </h4>

- SMS
```
/* Toast SMS Options, available to set ToastSMS options */
$smsOption = new ToastSMSOption();

/* Send SMS */
$toastSMS->sendSMS("text", $recipientList);
$toastSMS->sendSMS("text", $recipientList, $smsOption); // with options

/* Send Auth SMS, required auth message */
$toastSMS->sendAuthSMS("text", $recipientList, ToastSMS::AUTH);
$toastSMS->sendAuthSMS("text", $recipientList, ToastSMS::AUTH, $smsOption); // with options

/* Send Ad SMS, required rejection number */
$rejectionNumber = "08012345678"; // rejection number (console > 080 rejection setting)
$toastSMS->sendAdSMS("text", $recipientList, $rejectionNumber);
$toastSMS->sendAdSMS("text", $recipientList, $rejectionNumber, $smsOption); // with options

/* Send Tag SMS, tag expression array */
$tagExpressionList = array("tag1", "tag2");
$smsOption->setTagExpression($tagExpressionList);
$toastSMS->sendTagSMS("text", $recipientList, $smsOption);
```

- MMS
```
$mmsOption = new ToastSMSOption();

/* Send MMS */
$toastSMS->sendMMS("title", "text", $recipientList);
$toastSMS->sendMMS("title", "text", $recipientList, $mmsOption); // with options

/* Send Ad MMS, required rejection number */
$rejectionNumber = "08012345678"; // rejection number (console > 080 rejection setting)
$toastSMS->sendAdMMS("title", "text", $recipientList, $rejectionNumber);
$toastSMS->sendAdMMS("title", "text", $recipientList, $rejectionNumber, $mmsOption); // with options

/* Send Tag LMS, tag expression array */
$tagExpressionList = array("tag1", "tag2");
$mmsOption->setTagExpression($tagExpressionList);
$toastSMS->sendTagLMS("title", "text", $recipientList, $mmsOption);
```

- KakaoTalk BizMessage
```
$plusFriendId = "@channelName";
$templateCode = "templateCode";
$templateParameter = array(
    "key"=>"value"
);

$bizTalkRecipientList = array();
$bizTalkRecipientModel = new ToastKakaoTalkBizMessageRecipient("01012345678", $templateParameter);
array_push($bizTalkRecipientList, $bizTalkRecipientModel);

/* Send KakaoTalk BizMessage */
$toastSMS->sendKakaoTalkBizMessage($plusFriendId, $templateCode, $bizTalkRecipientList);
```

<br><hr><br>
- Auth message list 

  Required auth message when you send auth SMS
```
// auth
$toastSMS->setAuthMsg(ToastSMS::AUTH_REQUIRED_MSG_EN);

// 인증 (default)
$toastSMS->setAuthMsg(ToastSMS::AUTH_REQUIRED_MSG_KR);

// にんしょう
$toastSMS->setAuthMsg(ToastSMS::AUTH_REQUIRED_MSG_JP);

// 認証
$toastSMS->setAuthMsg(ToastSMS::AUTH_REQUIRED_MSG_CN);

// verif
$toastSMS->setAuthMsg(ToastSMS::AUTH_REQUIRED_MSG_VERIF);

// password
$toastSMS->setAuthMsg(ToastSMS::AUTH_REQUIRED_MSG_PASSWORD);

// 비밀번호
$toastSMS->setAuthMsg(ToastSMS::AUTH_REQUIRED_MSG_PASSWORD_KR);
```

- Language
  (default : KR)
  
  Available to set language, AD message set by language 
```
$toastSMS->setLanguage(ToastSMS::KR);
$toastSMS->setLanguage(ToastSMS::EN);
$toastSMS->setLanguage(ToastSMS::JP);
```

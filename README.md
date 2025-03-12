# Toast SMS Notifier

## Introduction
Toast SMS Notifier is a PHP-based library that enables you to send SMS, MMS, and KakaoTalk BizMessages using the Toast SMS API. It provides various messaging options such as authentication SMS, advertisement messages, and tag-based messages.

🔗 **Toast SMS API Documentation**: [Toast SMS API Guide](https://docs.toast.com/ko/Notification/SMS/ko/api-guide/)

## Installation
To use Toast SMS Notifier, include the required library in your PHP project.

```php
require_once 'ToastSMS.php';
```

## Getting Started

### 1. Create an Instance of `ToastSMS` Class
Create an instance of the `ToastSMS` class using your API key and sender phone number.

```php
$toastSMS = new ToastSMS("your-api-key", "sender-phone-number");
```

### 2. Define Recipient List
You can define recipients in multiple ways:

#### (1) Array of Phone Numbers
```php
$recipientList = array("01012345678", "01022222222");
```

#### (2) Single Phone Number
```php
$recipientList = "01012345678";
```

#### (3) Using `ToastSMSRecipient` Class
```php
$recipient = new ToastSMSRecipient("01012345678");
$recipient->setCountryCode(82); // Optional: Set country code
array_push($recipientList, $recipient);
```

---

## Sending Messages

### Sending SMS
#### Basic SMS
```php
$toastSMS->sendSMS("Your message text", $recipientList);
```

#### SMS with Options
```php
$smsOption = new ToastSMSOption();
$toastSMS->sendSMS("Your message text", $recipientList, $smsOption);
```

#### Authentication SMS
```php
$toastSMS->sendAuthSMS("Your auth message", $recipientList, ToastSMS::AUTH);
$toastSMS->sendAuthSMS("Your auth message", $recipientList, ToastSMS::AUTH, $smsOption);
```

#### Advertisement SMS (Rejection Number Required)
```php
$rejectionNumber = "08012345678"; // Set rejection number in console
$toastSMS->sendAdSMS("Ad message text", $recipientList, $rejectionNumber);
$toastSMS->sendAdSMS("Ad message text", $recipientList, $rejectionNumber, $smsOption);
```

#### Tag-based SMS
```php
$tagExpressionList = array("tag1", "tag2");
$smsOption->setTagExpression($tagExpressionList);
$toastSMS->sendTagSMS("Tagged message text", $recipientList, $smsOption);
```

### Sending MMS
```php
$mmsOption = new ToastSMSOption();
$toastSMS->sendMMS("Title", "MMS message text", $recipientList);
$toastSMS->sendMMS("Title", "MMS message text", $recipientList, $mmsOption);
```

#### Advertisement MMS
```php
$toastSMS->sendAdMMS("Title", "Ad MMS message", $recipientList, $rejectionNumber);
$toastSMS->sendAdMMS("Title", "Ad MMS message", $recipientList, $rejectionNumber, $mmsOption);
```

#### Tag-based LMS
```php
$tagExpressionList = array("tag1", "tag2");
$mmsOption->setTagExpression($tagExpressionList);
$toastSMS->sendTagLMS("Title", "Tagged LMS message", $recipientList, $mmsOption);
```

---

## Sending KakaoTalk BizMessages

### Sending a KakaoTalk Message
```php
$plusFriendId = "@channelName";
$templateCode = "templateCode";
$templateParameter = array(
    "key" => "value"
);

$bizTalkRecipientList = array();
$bizTalkRecipientModel = new ToastKakaoTalkBizMessageRecipient("01012345678", $templateParameter);
array_push($bizTalkRecipientList, $bizTalkRecipientModel);

$toastSMS->sendKakaoTalkBizMessage($plusFriendId, $templateCode, $bizTalkRecipientList);
```

---

## Authentication Message List
When sending an authentication SMS, you can specify the authentication message in various languages:

```php
$toastSMS->setAuthMsg(ToastSMS::AUTH_REQUIRED_MSG_EN); // English
$toastSMS->setAuthMsg(ToastSMS::AUTH_REQUIRED_MSG_KR); // Korean (default)
$toastSMS->setAuthMsg(ToastSMS::AUTH_REQUIRED_MSG_JP); // Japanese
$toastSMS->setAuthMsg(ToastSMS::AUTH_REQUIRED_MSG_CN); // Chinese
$toastSMS->setAuthMsg(ToastSMS::AUTH_REQUIRED_MSG_VERIF); // Verification
$toastSMS->setAuthMsg(ToastSMS::AUTH_REQUIRED_MSG_PASSWORD); // Password
$toastSMS->setAuthMsg(ToastSMS::AUTH_REQUIRED_MSG_PASSWORD_KR); // Password in Korean
```

---

## Setting Language
By default, the language is set to Korean. You can change the language as follows:

```php
$toastSMS->setLanguage(ToastSMS::KR); // Korean (default)
$toastSMS->setLanguage(ToastSMS::EN); // English
$toastSMS->setLanguage(ToastSMS::JP); // Japanese
```

---

## Conclusion
Toast SMS Notifier provides a simple yet powerful interface for sending SMS, MMS, and KakaoTalk messages. With various messaging options, it is suitable for different types of notifications, including authentication, advertisements, and tag-based messages.

For more details, refer to the official [Toast SMS API Guide](https://docs.toast.com/ko/Notification/SMS/ko/api-guide/).

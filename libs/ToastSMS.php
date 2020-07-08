<?php


class ToastSMS
{
    private $api_key; /* Api app key */
    private $user_agent; /* User-agent */
    private $host = "https://api-sms.cloud.toast.com"; /* Toast api domain */
    private $path; /* Url path */
    private $method; /* GET 0 / POST 1 */
    private $version; /* Api version */
    private $rejectionNumber; /* Rejection number(console > 080 rejection setting) */
    private $content; /* Data */
    private $result; /* Api Result */
    private $error; /* Error Message */
    private $authMsg = "인증"; /* Default = AUTH_REQUIRED_MSG_KR */
    private $language = "KR"; /* Default = KR */

    public const AUTH = "AUTH";
    public const AD = "AD";
    public const TAG = "TAG";

    public const KR = "KOREAN";
    public const EN = "ENGLISH";
    public const JP = "JAPANESE";

    /* Authentication Required Message */
    public const AUTH_REQUIRED_MSG_EN = "auth";
    public const AUTH_REQUIRED_MSG_KR = "인증";
    public const AUTH_REQUIRED_MSG_JP = "にんしょう";
    public const AUTH_REQUIRED_MSG_CN = "認証";
    public const AUTH_REQUIRED_MSG_VERIF = "verif";
    public const AUTH_REQUIRED_MSG_PASSWORD = "password";
    public const AUTH_REQUIRED_MSG_PASSWORD_KR = "비밀번호";

    public const MMS = "mms";
    public const SMS = "sms";

    /* Advertisement Required Message */
    private const AD_REQUIRED_MSG_FIRST_KR = "(광고)";
    private const AD_REQUIRED_MSG_SECOND_KR = "[무료 수신 거부]";
    private const AD_REQUIRED_MSG_FIRST_EN = "(Ad)";
    private const AD_REQUIRED_MSG_SECOND_EN = "[Reject receiving ads charge-free]";
    private const AD_REQUIRED_MSG_FIRST_JP = "(広告)";
    private const AD_REQUIRED_MSG_SECOND_JP = "[無料受信拒否]";
    private const RECIPIENT_TYPE_ERROR_MSG = "TypeError, Recipient list must be String(phone number) or String Array(phone number array) or Object Array(ToastSMSRecipient class)";

    public function __construct($apiKey, $sender, $version= "2.3")
    {
        $this->api_key = $apiKey;
        $this->content["sendNo"] = $sender;
        $this->version = $version;

        if(isset($_SERVER['HTTP_USER_AGENT']))
            $this->user_agent = $_SERVER['HTTP_USER_AGENT'];
    }

    /**
     * process curl
     */
    public function curlProcess()
    {
        $curl = curl_init();

        $host = sprintf("%s/sms/v%s/appKeys/%s%s",
            $this->host, $this->version, $this->api_key, $this->path);

        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($this->content, JSON_UNESCAPED_UNICODE));
        curl_setopt($curl, CURLOPT_URL, $host);
        curl_setopt($curl, CURLOPT_POST, $this->method);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type:application/json;charset=UTF-8"));
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $this->result = curl_exec($curl);

        $resultArray = json_decode($this->result, true);
        $resultHeader = $resultArray["header"];

        if($resultHeader["isSuccessful"] == false){
            $this->error["isSuccessful"] = $resultHeader["isSuccessful"];
            $this->error["resultCode"] = $resultHeader["resultCode"];
            $this->error["resultMessage"] = $resultHeader["resultMessage"];
        }

        if(curl_errno($curl)){
            $this->error = curl_errno($curl);
        }

        curl_close($curl);
    }

    /**
     * send Message
     * @param string $msgType
     * @param string $type
     * @param array|string $recipientList
     * @param ToastSMSOption $option
     * @param string $text
     * @param null|string $title
     * @return $this
     */
    private function send($msgType, $type, $recipientList, $option, $text, $title=null){
        $recipientList = $this->isRecipientTypeCorrect($recipientList);
        if(!empty($recipientList)){
            $this->content["recipientList"] = $this->setRecipientToArray($recipientList);
            $this->content["body"] = $text;
            if(!empty($title)){
                $this->content["title"] = $title;
            }

            $option = $this->setBodyOfOptionsByType($text, $type, $option);
            $this->setOptionsToContent($option);
            $this->setMethod($this->getPathByType($type, $msgType), 1);
            $this->curlProcess();
        } else {
            $this->error["resultMessage"] = self::RECIPIENT_TYPE_ERROR_MSG;
        }

        return $this;
    }

    /**
     * Check type of RecipientList
     * @param $recipientList
     * @return array|null
     */
    private function isRecipientTypeCorrect($recipientList){
        $result = true;
        $formattedRecipientList = array();
        if(gettype($recipientList) == "array"){
            foreach ($recipientList as $recipient){
                if(gettype($recipient) == "string"){
                    if(preg_match("/[0-9]/", $recipient)){
                        $toastSMSRecipient = new ToastSMSRecipient($recipient);
                        array_push($formattedRecipientList, $toastSMSRecipient);
                    } else {
                        $result = false;
                        break;
                    }
                } else if (is_object($recipient) == "object"){
                    break;
                } else {
                    $result = false;
                    break;
                }
            }
        } else if(gettype($recipientList) == "string"){
            if(preg_match("/[0-9]/", $recipientList)){
                $toastSMSRecipient = new ToastSMSRecipient($recipientList);
                array_push($formattedRecipientList, $toastSMSRecipient);
            } else {
                $result = false;
            }
        } else if(gettype($recipientList) == "object") {
            array_push($formattedRecipientList, $recipientList);
        } else {
            $result = false;
        }

        if(!$result){
            return NULL;
        } else {
            if(empty($formattedRecipientList)){
                return $recipientList;
            } else {
                return $formattedRecipientList;
            }
        }
    }

    /**
     * send basic SMS
     * @param string $text
     * @param array|string $recipientList
     * @param ToastSMSOption $option
     * @return $this
     */
    public function sendSMS($text, $recipientList, ToastSMSOption $option=null){
        $this->send(self::SMS, self::SMS, $recipientList, $option, $text);
        return $this;
    }

    /**
     * send SMS for Authentication(emergency)
     * @param string $text
     * @param array|string $recipientList
     * @param string $authMsg
     * @param ToastSMSOption $option
     * @return $this
     */
    public function sendAuthSMS($text, $recipientList, $authMsg, ToastSMSOption $option=null){
        $this->authMsg = $authMsg;

        $this->send(self::SMS, self::AUTH, $recipientList, $option, $text);
        return $this;
    }

    /**
     * send SMS for Advertisement
     * @param string $text
     * @param array|string $recipientList
     * @param string $rejectionNumber
     * @param ToastSMSOption $option
     * @return $this
     */
    public function sendAdSMS($text, $recipientList, $rejectionNumber, ToastSMSOption $option=null){
        $this->rejectionNumber = $rejectionNumber;

        $this->send(self::SMS, self::AD, $recipientList, $option, $text);
        return $this;
    }

    /**
     * send Tagged SMS
     * @param string $text
     * @param array|string $recipientList
     * @param ToastSMSOption $option
     * @return $this
     */
    public function sendTagSMS($text, $recipientList, ToastSMSOption $option){
        $this->send(self::SMS, self::TAG, $recipientList, $option, $text);
        return $this;
    }

    /**
     * send basic MMS
     * @param string $title
     * @param string $text
     * @param array|string $recipientList
     * @param ToastSMSOption $option
     * @return $this
     */
    public function sendMMS($title, $text, $recipientList, ToastSMSOption $option=null){
        $this->send(self::MMS, self::MMS, $recipientList, $option, $text, $title);
        return $this;
    }

    /**
     * send MMS for Advertisement
     * @param string $title
     * @param string $text
     * @param array|string $recipientList
     * @param string $rejectionNumber
     * @param ToastSMSOption $option
     * @return $this
     */
    public function sendAdMMS($title, $text, $recipientList, $rejectionNumber, ToastSMSOption $option=null){
        $this->rejectionNumber = $rejectionNumber;

        $this->send(self::MMS, self::AD, $recipientList, $option, $text, $title);
        return $this;
    }

    /**
     * send Tagged LMS
     * @param string $title
     * @param string $text
     * @param array|string $recipientList
     * @param ToastSMSOption $option
     * @return $this
     */
    public function sendTagLMS($title, $text, $recipientList, ToastSMSOption $option){
        $this->send(self::MMS, self::TAG, $recipientList, $option, $text, $title);
        return $this;
    }

    /**
     * set language(KR, EN ..)
     * @param string $language
     */
    public function setLanguage(string $language): void
    {
        $this->language = $language;
    }

    /**
     * set rejection number(console > 080 rejection setting)
     * @param mixed $rejectionNumber
     */
    public function setRejectionNumber($rejectionNumber): void
    {
        $this->rejectionNumber = $rejectionNumber;
    }

    /**
     * set required auth message
     * @param string $authMsg
     */
    public function setAuthMsg(string $authMsg): void
    {
        $this->authMsg = $authMsg;
    }

    /**
     * @return mixed
     */
    public function getApiKey()
    {
        return $this->api_key;
    }

    /**
     * @return mixed
     */
    public function getUserAgent()
    {
        return $this->user_agent;
    }

    /**
     * @return string
     */
    public function getHost(): ?string
    {
        return $this->host;
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getVersion(): ?string
    {
        return $this->version;
    }

    /**
     * @return mixed
     */
    public function getRejectionNumber()
    {
        return $this->rejectionNumber;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @return string
     */
    public function getAuthMsg(): ?string
    {
        return $this->authMsg;
    }

    /**
     * @return string
     */
    public function getLanguage(): ?string
    {
        return $this->language;
    }

    /**
     * set required content by type(auth, ad ...)
     * @param string $text
     * @param $type
     * @param ToastSMSOption $option
     * @return ToastSMSOption
     */
    private function setBodyOfOptionsByType($text, $type, ToastSMSOption $option=null){
        if(!empty($option)){
            if(empty($option->getTemplateId())){
                $text = $this->addRequiredTextByType($text, $type);
            }
        } else {
            $text = $this->addRequiredTextByType($text, $type);
        }

        $this->content["body"] = $text;
        return $option;
    }

    /**
     * Add required text to send message(type Auth, AD)
     * @param $text
     * @param $type
     * @return string
     */
    private function addRequiredTextByType($text, $type){
        if(strcmp($type, self::AD) == 0){
            if(strcmp($this->language, self::JP) == 0){
                $text = self::AD_REQUIRED_MSG_FIRST_JP.$text."\n"
                    .self::AD_REQUIRED_MSG_SECOND_JP.$this->rejectionNumber;
            } else if(strcmp($this->language, self::EN) == 0){
                $text = self::AD_REQUIRED_MSG_FIRST_EN.$text."\n"
                    .self::AD_REQUIRED_MSG_SECOND_EN.$this->rejectionNumber;
            } else{
                $text = self::AD_REQUIRED_MSG_FIRST_KR.$text."\n"
                    .self::AD_REQUIRED_MSG_SECOND_KR.$this->rejectionNumber;
            }
        } else if(strcmp($type,self::AUTH) == 0){
            $text = "(".$this->authMsg.") ".$text;
        }

        return $text;
    }

    /**
     * get url path by type(auth, ad ..)
     * @param string $type
     * @param string $msgType
     * @return string
     */
    private function getPathByType($type, $msgType){
        if(strcmp($type, self::AUTH) == 0){
            $path = "/sender/auth/";
        } else if(strcmp($type, self::AD) == 0){
            $path = "/sender/ad-";
        } else if(strcmp($type, self::TAG) == 0){
            $path = "/tag-sender/";
        } else {
            $path = "/sender/";
        }
        $path .= $msgType;
        return $path;
    }

    /**
     * set url path, method, version
     * @param string $path
     * @param int $method
     * @param string $version
     */
    private function setMethod($path, $method, $version="2.3")
    {
        $this->path = $path;
        $this->method = $method;
        $this->version = $version;
    }

    /**
     * set option data to content(array)
     * @param ToastSMSOption $option
     */
    private function setOptionsToContent(ToastSMSOption $option=null){
        if(!empty($option)){
            $this->content = array_merge($this->content, $option->objectToArray());
        }
    }

    /**
     * set required recipient variable to array
     * @param ToastSMSRecipient[] $recipientList
     * @return ToastSMSRecipient[]
     */
    private function setRecipientToArray(array $recipientList){
        $formattedRecipientList = array();
        foreach ($recipientList as $recipient){
            array_push($formattedRecipientList, $recipient->objectToArray());
        }

        return $formattedRecipientList;
    }
}

class ToastSMSOption
{
    private $templateId; /* Template ID */
    private $requestDate; /* Request date and time  */
    private $senderGroupingKey; /* Sender's group key */
    private $attachFileIdList; /* Attached file included, for MMS*/
    private $userId; /* User ID */
    private $statsId; /* Statistics ID */
    private $tagExpression; /* Tag expression, for TAG message, type(Array) */
    private $adYn; /* Ad or not (default: N), for MMS, for TAG message*/
    private $autoSendYn; /* Auto delivery or not (immediate delivery) (default: Y), for MMS, for TAG message */

    /**
     * @return mixed
     */
    public function getTemplateId()
    {
        return $this->templateId;
    }

    /**
     * @param mixed $templateId
     */
    public function setTemplateId($templateId): void
    {
        $this->templateId = $templateId;
    }

    /**
     * @return mixed
     */
    public function getRequestDate()
    {
        return $this->requestDate;
    }

    /**
     * @param mixed $requestDate
     */
    public function setRequestDate($requestDate): void
    {
        $this->requestDate = $requestDate;
    }

    /**
     * @return mixed
     */
    public function getSenderGroupingKey()
    {
        return $this->senderGroupingKey;
    }

    /**
     * @param mixed $senderGroupingKey
     */
    public function setSenderGroupingKey($senderGroupingKey): void
    {
        $this->senderGroupingKey = $senderGroupingKey;
    }

    /**
     * @return array
     */
    public function getAttachFileIdList(): array
    {
        return $this->attachFileIdList;
    }

    /**
     * @param array $attachFileIdList
     */
    public function setAttachFileIdList(array $attachFileIdList): void
    {
        $this->attachFileIdList = $attachFileIdList;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param mixed $userId
     */
    public function setUserId($userId): void
    {
        $this->userId = $userId;
    }

    /**
     * @return mixed
     */
    public function getStatsId()
    {
        return $this->statsId;
    }

    /**
     * @param mixed $statsId
     */
    public function setStatsId($statsId): void
    {
        $this->statsId = $statsId;
    }

    /**
     * @return array
     */
    public function getTagExpression(): array
    {
        return $this->tagExpression;
    }

    /**
     * @param array $tagExpression
     */
    public function setTagExpression(array $tagExpression): void
    {
        $this->tagExpression = $tagExpression;
    }

    /**
     * @return mixed
     */
    public function getAdYn()
    {
        return $this->adYn;
    }

    /**
     * @param mixed $adYn
     */
    public function setAdYn($adYn): void
    {
        $this->adYn = $adYn;
    }

    /**
     * @return mixed
     */
    public function getAutoSendYn()
    {
        return $this->autoSendYn;
    }

    /**
     * @param mixed $autoSendYn
     */
    public function setAutoSendYn($autoSendYn): void
    {
        $this->autoSendYn = $autoSendYn;
    }

    public function objectToArray ()
    {
        $smsOptionArray = array();

        if(!empty($this->templateId)){
            $smsOptionArray["templateId"] = $this->templateId;
        }

        if(!empty($this->requestDate)){
            $smsOptionArray["requestDate"] = $this->requestDate;
        }

        if(!empty($this->senderGroupingKey)){
            $smsOptionArray["senderGroupingKey"] = $this->senderGroupingKey;
        }

        if(!empty($this->attachFileIdList)){
            $smsOptionArray["attachFileIdList"] = $this->attachFileIdList;
        }

        if(!empty($this->userId)){
            $smsOptionArray["userId"] = $this->userId;
        }

        if(!empty($this->statsId)){
            $smsOptionArray["statsId"] = $this->statsId;
        }

        if(!empty($this->tagExpression)){
            $smsOptionArray["tagExpression"] = $this->tagExpression;
        }

        if(!empty($this->adYn)){
            $smsOptionArray["adYn"] = $this->adYn;
        }

        if(!empty($this->autoSendYn)){
            $smsOptionArray["autoSendYn"] = $this->autoSendYn;
        }

        return $smsOptionArray;
    }
}

class ToastSMSRecipient
{
    private $recipientNo; /* Recipient number, required */
    private $countryCode; /* Country code [Default: 82 (Korea)] */
    private $internationalRecipientNo; /* Recipient number including country code */
    private $templateParameter; /* Template parameter (with the input of template ID), key-value */
    private $recipientGroupingKey; /* Recipient group key */

    public function __construct($recipientNo)
    {
        $this->recipientNo = $recipientNo;
    }

    /**
     * @return mixed
     */
    public function getRecipientNo()
    {
        return $this->recipientNo;
    }

    /**
     * @param mixed $recipientNo
     */
    public function setRecipientNo($recipientNo): void
    {
        $this->recipientNo = $recipientNo;
    }

    /**
     * @return mixed
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * @param mixed $countryCode
     */
    public function setCountryCode($countryCode): void
    {
        $this->countryCode = $countryCode;
    }

    /**
     * @return mixed
     */
    public function getInternationalRecipientNo()
    {
        return $this->internationalRecipientNo;
    }

    /**
     * @param mixed $internationalRecipientNo
     */
    public function setInternationalRecipientNo($internationalRecipientNo): void
    {
        $this->internationalRecipientNo = $internationalRecipientNo;
    }

    /**
     * @return mixed
     */
    public function getTemplateParameter()
    {
        return $this->templateParameter;
    }

    /**
     * @param mixed $templateParameter
     */
    public function setTemplateParameter($templateParameter): void
    {
        $this->templateParameter = $templateParameter;
    }

    /**
     * @return mixed
     */
    public function getRecipientGroupingKey()
    {
        return $this->recipientGroupingKey;
    }

    /**
     * @param mixed $recipientGroupingKey
     */
    public function setRecipientGroupingKey($recipientGroupingKey): void
    {
        $this->recipientGroupingKey = $recipientGroupingKey;
    }

    public function objectToArray ()
    {
        $recipientArray = array();

        if (!empty($this->recipientNo)) {
            $recipientArray["recipientNo"] = $this->recipientNo;
        }

        if (!empty($this->countryCode)) {
            $recipientArray["countryCode"] = $this->countryCode;
        }

        if (!empty($this->internationalRecipientNo)) {
            $recipientArray["internationalRecipientNo"] = $this->internationalRecipientNo;
        }

        if (!empty($this->templateParameter)) {
            $recipientArray["templateParameter"] = $this->templateParameter;
        }

        if (!empty($this->recipientGroupingKey)) {
            $recipientArray["recipientGroupingKey"] = $this->recipientGroupingKey;
        }

        return $recipientArray;
    }
}
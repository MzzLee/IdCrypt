<?php
/**
* 数字ID与字符串ID之间的转换
*/
class IdCrypt {
    /**
     * @const 随机加密串
     */
    const CRYPT_KEY     = 'YiSsTCUrGLfBw5qczgX1aIo3OR7EbZ69kWAeDvHlmVQN2t8FK4JPyd0uhnpjxM';
    /**
     * @const 加密串长度
     */
    const CRYPT_KEY_LEN = 62;

    /**
     * 数字ID转换成字符串ID (输入数字最大支持int(20)，生成加密字符最大支持32位长度)
     *
     * @param int $intId
     * @param int $intCryptLen
     * @return string
     */
    public static function encode($intId, $intCryptLen = 16){
        $strKey = '';
        /** 有效加密长度 */
        $intCryptKeyLen =  self::CRYPT_KEY_LEN - $intCryptLen;
        /** 用于加密的字符串集合 */
        $arrCryptMap    = str_split(substr(self::CRYPT_KEY, 0, $intCryptKeyLen + 1));
        /** 用于补位的字符串集合 */
        $strFillKey     = substr(self::CRYPT_KEY, $intCryptKeyLen + 1);

        /** 转换进制（取决于去掉补位字符串后的剩余长度）*/
        $intHex         = $intCryptKeyLen;
        $done           = true;
        $arrXHex        = [];

        while($done) {
            $intNum = bcmod($intId, $intHex);
            $intId = ($intId - $intNum) / ($intHex);
            array_unshift($arrXHex, $intNum);
            if($intId == 0) {
                break;
            }
            if($intId < $intHex ) {
                array_unshift($arrXHex, $intId);
                $done = false;
            }
        }
        /** 根据X进制映射随机map */
        foreach($arrXHex as $index) {
            $strKey .= $arrCryptMap[$index];
        }
        /** 长度不足进行补位 */
        $intFillLen = $intCryptLen - count($arrXHex);
        if($intFillLen > 0) {
            //echo $arrFillKey;
            $strKey .= substr($strFillKey, 0, $intFillLen);
        }
        return $strKey;

    }

    /**
     * 字符串ID反解成数字ID
     *
     * @param string $strId  加密ID串
     * @return int
     */
    public static function decode($strId){
        $intId       = 0;
        $intCryptLen = strlen($strId);
        /** 有效加密长度 */
        $intCryptKeyLen =  self::CRYPT_KEY_LEN - $intCryptLen;
        /** 用于补位的字符串集合 */
        $strFillKey     = substr(self::CRYPT_KEY, $intCryptKeyLen + 1);
        /** 计算补位字符串位置 */
        $intCryptPos    = 1;
        while($intCryptPos < $intCryptLen) {
            if(strstr($strFillKey, substr($strId, $intCryptPos))) {
                break;
            }

            $intCryptPos++;
        }

        /** 计算进制 */
        $intHex         = self::CRYPT_KEY_LEN - $intCryptLen;
        /** 获取真实加密串 */
        $strCryptKey    = substr($strId, 0, $intCryptPos);
        /** 获取加密串的位置并根据位置，转换成10进制 */
        $arrCryptKey    = str_split(strrev($strCryptKey));

        for($i=0; $i<$intCryptPos; $i++) {
            $intId += strpos(self::CRYPT_KEY, $arrCryptKey[$i]) * pow($intHex, $i);
        }
        return $intId;
    }
}

?>
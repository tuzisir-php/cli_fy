<?php
$DEBUG = TRUE;
function dedump($string)
{
    global $DEBUG;
    if ($DEBUG)
        echo $string, "\n";
}
function procEnd($string, $debugString = '')
{
    echo $string, "\n";
    dedump($debugString);
    exit;
}
$help = 'demo: dict.php "apple"';
$urlFormat = 'http://fanyi.youdao.com/openapi.do'
        . '?keyfrom=%s&key=%s&type=data&doctype=json'
        . '&version=1.1&q=%s';

$printFormat = <<<'EOT'
word: %s
phonetic:
%s
translation: %s

explains:
============
%s

internet trans:
============
%s
EOT;

$apiKey = 254937526;
$apiKeyfrom = 'jumpaper';

function isAllChinese($str){
        //新疆等少数民族可能有·
        if(strpos($str,'·')){
            //将·去掉，看看剩下的是不是都是中文
            $str=str_replace("·",'',$str);
            if(preg_match('/^[\x7f-\xff]+$/', $str)){
                return true;//全是中文
            }else{
                return false;//不全是中文
            }
        }else{
            if(preg_match('/^[\x7f-\xff]+$/', $str)){
                return true;//全是中文
            }else{
                return false;//不全是中文
            }
        }
}
$errorMaps = array(
    0 => '正常',
    20 => '要翻译的文本过长',
    30 => '无法进行有效的翻译',
    40 => '不支持的语言类型',
    50 => 'API KEY 无效',
    60 => '无词典结果， 仅在获取词典结果生效'
);

if ($argc == 1 || @$argv[2] == '-h' || @$argv[2] == '--help') {
    procEnd($help);
}
if ($argc > 1) {
    $q = implode(' ', array_slice($argv, 1));
    if (isAllChinese($q)) {
        $q = urlencode($q);
    }
    $url = sprintf($urlFormat, $apiKeyfrom, $apiKey, $q);
    $res = file_get_contents($url);
    $resJ = json_decode($res, true);
    $phonetic = '';
    $translation = '';
    $explains = '';
    $interTrans = '';
    if (@$resJ['errorCode']) procEnd($errorMaps[$resJ['errorCode']], $url);
    if (@$resJ['basic']['uk-phonetic']) $phonetic .= 'uk: ' . $resJ['basic']['uk-phonetic'] . "\n";
    if (@$resJ['basic']['us-phonetic']) $phonetic .= 'us: ' . $resJ['basic']['us-phonetic'];
    if (@$resJ['translation']) $translation = implode("\n", $resJ['translation']);
    if (@$resJ['basic']['explains']) $explains = implode("\n", $resJ['basic']['explains']);
    if (@$resJ['web']) {
        $items = array();
        foreach ( $resJ['web'] as $item):
            $items[] = $item['key'] . ': ' . implode(',', $item['value']);
        endforeach;
        $interTrans = implode("\n", $items);
    }
    $willPrint = sprintf($printFormat, $resJ['query'], $phonetic,
        $translation, $explains, $interTrans);

    echo $willPrint;
}

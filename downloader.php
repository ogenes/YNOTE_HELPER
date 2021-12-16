<?php
/**
 * Created by YNOTE_HELPER.
 * User: ogenes
 * Date: 2021/12/15
 */


/**
 * @return array
 */
function conf(): array
{
    $json = file_get_contents('./config.json');
    return json_decode($json, true);
}

/**
 * @param string $url
 * @return string
 * @throws ErrorException
 */
function request(string $url): string
{
    $curl = curl_init();
    
    $YNOTE_SESS = conf()['YNOTE_SESS'] ?? '';
    $YNOTE_LOGIN = conf()['YNOTE_LOGIN'] ?? '';
    
    if (empty($YNOTE_SESS) || empty($YNOTE_LOGIN)) {
        throw new ErrorException('请先在网页端登录并复制对应Cookie值保存到conf中，详情见 README ', 100);
    }
    $HTTPHEADER[] = "Cookie: YNOTE_SESS={$YNOTE_SESS}; YNOTE_LOGIN={$YNOTE_LOGIN}";
    
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => $HTTPHEADER,
    ]);
    
    $response = curl_exec($curl);
    
    curl_close($curl);
    return $response;
}

/**
 * @param string $basedir
 * @return array
 * @throws ErrorException
 */
function listEntireByParentPath(string $basedir): array
{
    $url = 'https://note.youdao.com/yws/api/personal/file';
    $params = [
        "method" => "listEntireByParentPath",
        "_system" => "macos",
        "cstk" => conf()['YNOTE_CSTK'] ?? '',
        "sev" => "j1",
        "path" => "/",
        "dirOnly" => true,
        "f" => true,
    
    ];
    $queryStr = http_build_query($params);
    $api = $url . '?' . $queryStr;
    $resp = request($api);
    $data = json_decode($resp, true);
    
    if (isset($data['error']) && $data['error'] > 0) {
        throw new ErrorException($data['message'], $data['error']);
    }
    
    $ret = [];
    foreach ($data as $datum) {
        $tmp = [];
        $tmp['id'] = $datum['fileEntry']['id'] ?? '';
        $tmp['name'] = $datum['fileEntry']['name'] ?? '';
        $tmp['dir'] = $datum['fileEntry']['dir'] ?? '';
        $tmp['basedir'] = $basedir;
        $ret[] = $tmp;
    }
    return $ret;
}

/**
 * @param string $id
 * @param string $basedir
 * @return array
 * @throws ErrorException
 */
function listPageByParentId(string $id, string $basedir): array
{
    $url = "https://note.youdao.com/yws/api/personal/file/{$id}";
    $params = [
        "all" => true,
        "f" => true,
        "len" => 300,
        "sort" => 1,
        "isReverse" => false,
        "method" => "listPageByParentId",
        "_system" => "macos",
        "cstk" => conf()['YNOTE_CSTK'] ?? '',
        "sev" => "j1",
    ];
    $queryStr = http_build_query($params);
    $api = $url . '?' . $queryStr;
    $resp = request($api);
    $data = json_decode($resp, true);
    if (!(isset($data['entries']) && $data['entries'])) {
        return [];
    }
    
    $ret = [];
    foreach ($data['entries'] as $datum) {
        $tmp = [];
        $tmp['id'] = $datum['fileEntry']['id'] ?? '';
        $tmp['name'] = $datum['fileEntry']['name'] ?? '';
        $tmp['dir'] = $datum['fileEntry']['dir'] ?? '';
        $tmp['basedir'] = $basedir;
        if ($tmp['dir']) {
            $tmpRet = listPageByParentId($tmp['id'], $basedir . $tmp['name'] . '/');
            array_push($ret, ...$tmpRet);
        } else {
            $ret[] = $tmp;
        }
    }
    return $ret;
}

/**
 * @param string $fileId
 * @return string
 * @throws ErrorException
 */
function download(string $fileId): string
{
    $url = "https://note.youdao.com/yws/api/personal/sync";
    $params = [
        "method" => "download",
        "_system" => "macos",
        "cstk" => conf()['YNOTE_CSTK'] ?? '',
        "sev" => "j1",
        "fileId" => $fileId,
        "version" => -1,
        "read" => true,
    ];
    $queryStr = http_build_query($params);
    $api = $url . '?' . $queryStr;
    $resp = request($api);
    return $resp ?: '';
}

/**
 * @param $basedir
 */
function main($basedir)
{
    try {
        //一级目录
        $parents = listEntireByParentPath($basedir);
    
        $docs = [];
        //所有文档
        foreach ($parents as $dir) {
            $basedir = $dir['basedir'] . $dir['name'] . '/';
            $tmpDocs = listPageByParentId($dir['id'], $basedir);
            array_push($docs, ...$tmpDocs);
            echo $basedir . ': ' . count($tmpDocs) . PHP_EOL;
        }
    
        //获取内容
        foreach ($docs as $doc) {
            $docDir = $doc['basedir'];
            if (!is_dir($docDir) && !mkdir($docDir, 0777, true) && !is_dir($docDir)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $docDir));
            }
            $filename = $docDir . $doc['name'];
            echo $filename . PHP_EOL;
            $content = download($doc['id']);
            
            //save
            file_put_contents($filename, $content);
        }
    } catch (ErrorException $e) {
        print_r($e->getMessage());
    }
    
    echo PHP_EOL . ' Over !' . PHP_EOL;
}

$basedir = __DIR__ . "/note/";
if (!is_dir($basedir) && !mkdir($basedir, 0755, true) && !is_dir($basedir)) {
    throw new \RuntimeException(sprintf('Directory "%s" was not created', $basedir));
}

main($basedir);

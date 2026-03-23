<?php
function cmpversion($v1,$v2) {
    $cmp=strcmp(substr($v1,0,7),substr($v2,0,7));
    if ($cmp!=0) return($cmp);
    $c1=substr($v1,7,1);
    $c2=substr($v2,7,1);
    if ($c1==$c2) {
        if ($c1=='.') return((int) substr($v1,8) <=> (int) substr($v2,8));
        if ($c1=='-') return((int) substr($v1,10) <=> (int) substr($v2,10));
        return(0);
    }
    if ($c1=='-') return(-1);
    if ($c2=='-') return(1);
    if ($c1=='') return(-1);
    return(1);
}

header('Content-Type: application/json; charset=utf-8');
$cachejson='/tmp/buildroot.json';
if (!file_exists($cachejson) || (time()-filemtime($cachejson))>600) {
    $url = 'https://buildroot.org/downloads/';
    $context = stream_context_create([
        'http' => [
            'method'  => 'GET',
            'timeout' => 15,
            'header'  => "User-Agent: PHP-fetch/1.0\r\nAccept: text/html\r\n",
        ],
        'ssl' => [
            'verify_peer'      => true,
            'verify_peer_name' => true,
        ],
    ]);
    $html = @file_get_contents($url, false, $context);
    if ($html === false) {
        http_response_code(502);
        exit;
    }
    $versions = [];
    if (preg_match_all('#href\s*=\s*["\'][^"\']*/?buildroot-([^"\']*?)\.tar\.gz["\']#i',$html,$m)) {
        usort($m[1],'cmpversion');
        foreach($m[1] as $title) if ($title > '2023.') {
            $color=$debian12=$debian13='tt';
            foreach(json_decode(file_get_contents('buildroot.json')) as $entry)
                if (cmpversion($entry->min,$title)<=0 && cmpversion($title,$entry->max)<=0) {
                    $color=$entry->color;
                    $debian12=$entry->debian12;
                    $debian13=$entry->debian13;
                }
            $version=(object) ['title' => $title, 'color' => $color, 'debian12' => $debian12, 'debian13' => $debian13];
            $versions[]=$version;
        }
    }
    file_put_contents($cachejson, json_encode($versions, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
}
echo file_get_contents($cachejson);
?>
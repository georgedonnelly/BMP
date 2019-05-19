<?php # BMP



function rpc_connect() {
    global $b;

    if (!$b) {
        require_once('lib/easybitcoin.php');
        $sb = parse_url(URL_BCH);
        $b = new Bitcoin($sb['user'], $sb['pass'], $sb['host'], $sb['port']);
        if (!$b)
            echo $b->error();
    }
    
    return $b;
}



function get_block($hash) {
    $b = rpc_connect();

    if (strlen($hash)!==64)
        $hash = $b->getblockhash($hash);

    return $b->getblock($hash);
}



function get_raw_transaction($txid) {
    $b = rpc_connect();
    return $b->getrawtransaction($txid, 1);
}



function block_height_last() {
    $b = rpc_connect();
    return $b->getinfo()['blocks'];
}

<?php # BMP — Javier González González



function get_new_blocks() {
    $output = false;
    
    foreach (BLOCKCHAINS AS $blockchain => $config)
        if (get_new_block($blockchain))
            $output = true;

    return $output;
}



function get_new_block($blockchain=BLOCKCHAIN_ACTIONS) {
    
    $height_rpc = rpc_get_best_height($blockchain);
    if (!$height_rpc)
        exit;

    $height_bmp = sql("SELECT height AS ECHO FROM blocks WHERE blockchain = '".$blockchain."' ORDER BY height DESC LIMIT 1");
    
    if (!is_numeric($height_rpc) OR $height_rpc==$height_bmp)
        return false;
    
    if ($height_bmp)
        $height = $height_bmp + 1;
    else
        $height = BLOCKCHAINS[$blockchain]['bmp_genesis'];

    block_insert($height, $blockchain);


    return true;
}


function block_delete_from($height, $blockchain=BLOCKCHAIN_ACTIONS) {
    if ($height < 0)
        $height = rpc_get_best_height($blockchain)-$height;

    sql("DELETE FROM blocks  WHERE blockchain = '".$blockchain."' AND height >= ".e($height));
    sql("DELETE FROM miners  WHERE blockchain = '".$blockchain."' AND height >= ".e($height));
    sql("DELETE FROM actions WHERE blockchain = '".$blockchain."' AND height >= ".e($height));

    update_power();
    update_actions($blockchain);
}


function revert_bytes($hex) {
    $hex = str_split($hex, 2);
    $hex = array_reverse($hex);
    return implode('', $hex);
}


function pool_decode($coinbase, $coinbase_hashpower=false) {
    global $__pools_json_cache;

    if (!$__pools_json_cache)
        $__pools_json_cache = json_decode(file_get_contents('lib/pools.json'), true);

    if (is_array($coinbase))
        foreach ($__pools_json_cache['payout_addresses'] AS $address => $pool)
            foreach ((array)$coinbase['vout'] AS $vout)
                if ($address === $vout['scriptPubKey']['addresses'][0])
                    return $pool;


    $coinbase_hex = (is_array($coinbase)?$coinbase['vin'][0]['coinbase']:$coinbase);
    foreach ($__pools_json_cache['coinbase_hex'] AS $hex => $pool)
        if (strpos($coinbase_hex, $hex) !== false)
            return $pool;


    $coinbase_text = hex2bin($coinbase_hex);
    foreach ($__pools_json_cache['coinbase_tags'] AS $tag => $pool)
        if (strpos($coinbase_text, $tag) !== false)
            return $pool;


    if (is_array($coinbase_hashpower) AND count((array)$coinbase_hashpower['miners'])>=30) // Hack
        return ['name' => 'P2Pool', 'pool_link' => 'https://github.com/jtoomim/p2pool'];

    return null;
}


function pool_identify() {
    foreach (sql("SELECT id, coinbase FROM blocks") AS $r)
        if ($pool = pool_decode($r['coinbase']))
            sql_update('blocks', ['pool' => $pool['name'], 'pool_link' => ($pool['link']?$pool['link']:null)], "id = '".$r['id']."'");
}


function address_normalice($address) {
    
    if (substr($address,0,12)=='bitcoincash:') {
        include_once('lib/cashaddress.php');
        $address = \CashAddress\CashAddress::new2old($address, false);
    }

    return trim($address);
}


function hashpower_humans($hps, $unit=false, $decimals=0) {

    if (!is_numeric($hps) OR $hps==0)
        return '';

    $units = [
        'E' => 1000000000000000000,
        'P' =>    1000000000000000,
        'T' =>       1000000000000,
        ];
    
    if ($units[$unit])
        return num($hps/$units[$unit], $decimals).'&nbsp;'.$unit.'H/s';

    foreach ($units AS $u => $x)
        if ($hps/$x>=100 OR $u=='T')
            return num($hps/$x, $decimals).'&nbsp;'.$u.'H/s';

}


function hashpower_humans_phs($hps, $decimals=0) {
    return hashpower_humans($hps, 'P', $decimals);
}


function hextobase58($hex) {
    include_once('lib/base58.php');
    $base58 = new Base58;
    return $base58->encode($hex);
}


function hex2bin_print($hex) {

    foreach (str_split($hex,2) AS $byte)
        $output .= (ctype_print(hex2bin($byte))?'<b>'.hex2bin($byte).'</b>':$byte);

    return str_replace('</b><b>', '', $output);
}
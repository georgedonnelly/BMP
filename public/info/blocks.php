<?php # BMP — Javier González González

$__template['title'] = 'Blocks';

echo html_h($__template['title'], 1);


if ($_GET['blockchain'])
    $sql_where[] = "blockchain = '".e($_GET['blockchain'])."'";

if ($_GET['pool'])
    $sql_where[] = "pool = '".e($_GET['pool'])."'";

if ($_GET['unknown'])
    $sql_where[] = "pool IS NULL";


$data = sql("SELECT blockchain, height, hash,
    (SELECT COUNT(*) FROM miners  WHERE blockchain = blocks.blockchain AND height = blocks.height) AS miners,
    (SELECT COUNT(*) FROM actions WHERE blockchain = blocks.blockchain AND  height = blocks.height) AS actions, 
    pool, tx_count, time, hashpower, power_by".($_GET['coinbase']?", coinbase":"")."
    FROM blocks ".($sql_where?"WHERE ".implode(" AND ", $sql_where):"")."
    ORDER BY time DESC, height DESC");


foreach ($data AS $key => $value) {
    
    if ($value['actions'])
        $data[$key]['actions']  = html_b($value['actions']);

    $data[$key]['tx_count']  = num($value['tx_count']);

    $data[$key]['hashpower'] = hashpower_humans($value['hashpower']);
    $data[$key]['hash']      = substr($value['hash'],0,26);

    if ($_GET['coinbase'])
        $data[$key]['coinbase']  = hex2bin($value['coinbase']);

}

foreach (BLOCKCHAINS AS $blockchain => $value)
    $blockchain_colors[$blockchain] = $value['background_color'];

echo html_table($data, [
    'blockchain'    => ['tr_background_color' => $blockchain_colors],
    'miners'        => ['align'     => 'right'],
    'actions'       => ['align'     => 'right'],
    'hash'          => ['monospace' => true],
    'tx_count'      => ['align'     => 'right', 'th' => 'TX'],
    'hashpower'     => ['align'     => 'right'],
    ]);

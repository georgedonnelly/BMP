<?php # BMP — Javier González González


function html_table($data, $config=false) {

    if (!is_array($data))
        return '';
    
    // Header
    $html .= '<tr style="'.($config['th_background-color']?'background-color:'.$config['th_background-color'].';':'').'">';
    foreach ((array)$data[0] AS $key => $value) {

        if (isset($config[$key]['th']))
            $key = $config[$key]['th'];

        $html .= '<th>'.ucfirst($key).'</th>';
    }
    $html .= '</tr>';
    
    
    // Content
    foreach ($data AS $row) {
        $html .= '<tr>';
        foreach ($row AS $key => $column) {
            $td_extra = '';
            
            if ($config[$key]['align'])
                $td_extra .= ' align="'.$config[$key]['align'].'"';

                $monospace = false;
                if ($config[$key]['monospace'])
                    $monospace = ' class="monospace"';

                if ($config[$key]['ucfirst'])
                    $column = ucfirst($column);

                if ($config[$key]['capital'])
                    $column = strtoupper($column);

            $html .= '<td'.$td_extra.$monospace.' nowrap>'.$column.'</td>';
        }
        $html .= '</tr>';
    }
    

    return '<table>'.$html.'</table>';
}


function html_a($url, $text, $blank=false) {
    return '<a href="'.$url.'"'.($blank?' target="_blank"':'').'>'.$text.'</a>';
}


function html_b($text) {
    return '<b>'.$text.'</b>';
}

function html_h($text, $num=1) {
    return '<h'.$num.'>'.$text.'</h'.$num.'>';
}


function html_button($url=false, $text='', $style='primary', $extra=false) {
    if ($url)
        return '<a href="'.$url.'" class="btn btn-'.$style.'">'.$text.'</a>';
    else
        return '<button type="button" class="btn btn-'.$style.'">'.$text.'</button>';
}
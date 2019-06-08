// BMP


last = '2009-01-03 18:15';
ajax_refresh = true;
refresh = '';
chat_delay = 2000;
chat_scroll = 0;



window.onload = function(){
	scroll_down();
	refresh = setTimeout(chat_query_ajax, chat_delay); 
	chat_query_ajax();
}


$('#chat_input_msg').keyup(function() {
	$('#op_return_preview').text(bin2hex($(this).val()));
});


$('#chat_form_msg').submit(function() {

	var timestamp = Math.round(new Date().getTime()/1000);

	var msg = $('#chat_input_msg').val();
	$('#chat_input_msg').val('');
	$('#op_return_preview').text('');
    
    
	var action  = '02';
    var channel = '0000';

	var op_return = bmp_protocol_prefix + action + bin2hex(timestamp) + channel + bin2hex(msg);


	result = blockchain_send_tx(op_return);
	
	return false;
});



function chat_query_ajax() {

	if (ajax_refresh) {
		ajax_refresh = false;
        
        clearTimeout(refresh);
        var start = new Date().getTime();
		$('#vpc_actividad').attr('src', '/public/chat/img/point_blue.png');

		$.post('/chat/ajax/refresh?last=' + last, function(data) {

            if (data) {
                $('#vpc_actividad').attr('src', '/public/chat/img/point_green.png');
                print_msg(data);
                scroll_down();
            }

            var elapsed = new Date().getTime() - start;
            $('#vpc_actividad').attr('title', elapsed + ' ms latency');
            $('#vpc_actividad').attr('src', '/public/chat/img/point_grey.png');

            refresh = setTimeout(chat_query_ajax, chat_delay);

            ajax_refresh = true;
        });
		
	}
}



function print_msg(data) {
	var html = '';

    if (!data['msg'])
        return false;

	data['msg'].forEach(function(value, key, array) {

        if (value['height'] == null)
            value['height'] = '';
        
        var date = new Date(Date.parse(value['time']));
        
        html += '<tr>';

        if (value['action']=='chat') {

            if (value['nick'] !== null)
                var nick = value['nick'].substr(0, 20);
            else
                var nick = value['address'].substr(-10, 10);

            html += '<td title="' + date.format('Y-m-d H:i:s') + ' UTC">' + date.format('H:i') + '</td>';
            html += '<td align=right title="Miner: ' + value['address'] + '" class="monospace"><a href="/info/miner/' + value['address'] + '" target="_blank">' + nick + '</a></td>';
            html += '<td width="100%">' + value['p3'] + '</td>';

        }


        if (value['action']=='miner_parameter') {

            html += '<td title="' + date.format('Y-m-d H:i:s') + ' UTC">' + date.format('H:i') + '</td>';
    
            html += '<td colspan=2 style="color:#00469A;">';
            html += '<b>[' + value['action'].toUpperCase() + ']</b>&nbsp; ';
            if (value['p1']=='nick') {
                html += '<a href="/info/miner/' + value['address'] + '" target="_blank">' + value['address'].substr(-10, 10) + '</a> set nick: <i>' + value['p2'] + '</i>';    
            }
            html += '</td>';

        }

        html += '<td>' + value['height'] + '</td>';
        html += '<td align=right><a href="/info/action/' + value['txid'] + '" class="bmp_power">' + value['power'] + '%</a></td>';
        html += '</tr>';
        

		last = value['time'];
	});
	
	$('#chat_msg').append(html);
}


function scroll_down() {
	if (chat_scroll <= document.getElementById('vpc').scrollTop) {
		document.getElementById('vpc').scrollTop = 100000000;
		chat_scroll = document.getElementById('vpc').scrollTop;
	}
}

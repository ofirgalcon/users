var formatUsersTime = function(col, row){
    
    var cell = $('td:eq('+col+')', row)
    var checkin = parseInt(cell.text());
    if (checkin > 0){
        var date = new Date(checkin * 1000);
        cell.html('<span title="'+date+'">'+moment(date).fromNow()+'</span>');
    } else {
        cell.text("")
    }
}

var formatUsersYesNo = function(col, row){
    var cell = $('td:eq('+col+')', row),
    value = cell.text()
    value = value == '1' ? '<span class="label label-success">'+i18n.t('yes')+'</span>' :
    (value === '0' ? '<span class="label label-danger">'+i18n.t('no')+'</span>' : '')
    cell.html(value)
}

var formatUsersYesNoSSH = function(col, row){
    var cell = $('td:eq('+col+')', row),
    value = cell.text()
    value = value == '1' ? '<span class="label label-danger">'+i18n.t('yes')+'</span>' :
    (value === '0' ? '<span class="label label-success">'+i18n.t('no')+'</span>' : '')
    cell.html(value)
}

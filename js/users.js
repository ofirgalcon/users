var formatUsersYesNoSSH = function(col, row){
    var cell = $('td:eq('+col+')', row),
        value = cell.text()
    value = value == '1' ? mr.label(i18n.t('yes'), 'danger') :
        (value === '0' ? mr.label(i18n.t('no'), 'success') : '')
    cell.html(value)
}

var formatUsersYesNoGood = function(col, row){
    var cell = $('td:eq('+col+')', row),
        value = cell.text()
    value = value == '1' ? mr.label(i18n.t('yes'), 'success') :
        (value === '0' ? mr.label(i18n.t('no'), 'danger') : '')
    cell.html(value)
}

var formatUsersCurrentUserBoolean = function(col, row){
    var cell = $('td:eq('+col+')', row),
        currentUser = cell.text().trim(),
        recordNameCol = $('.table th[data-colname="local_users.record_name"]').index(),
        recordName = recordNameCol > -1 ? $('td:eq('+recordNameCol+')', row).text().trim() : ''

    var isCurrent = (recordName && currentUser && recordName === currentUser) ? '1' : '0'
    cell.html(isCurrent === '1' ? mr.label(i18n.t('yes'), 'success') : mr.label(i18n.t('no'), 'danger'))
}

var formatUsersYesNoSSH = function(col, row){
    var cell = $('td:eq('+col+')', row),
        value = cell.text()
    value = value == '1' ? mr.label(i18n.t('yes'), 'danger') :
        (value === '0' ? mr.label(i18n.t('no'), 'success') : '')
    cell.html(value)
}

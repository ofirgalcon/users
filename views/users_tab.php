<div id="users-tab"></div>
<h2 data-i18n="users.users"></h2>

<div id="users-msg" data-i18n="listing.loading" class="col-lg-12 text-center"></div>

<script>
$(document).on('appReady', function(){
    $.getJSON(appUrl + '/module/users/get_tab_data/' + serialNumber, function(data){

        // Check if we have data
        if(!data[0]){
            $('#users-msg').text(i18n.t('no_data'));
            $('#users-header').removeClass('hide');

            // Update the tab users count
            $('#users-cnt').text("");

        } else {

            // Hide
            $('#users-msg').text('');
            $('#users-view').removeClass('hide');

            // Set count of user accounts
            $('#users-cnt').text(data.length);

            var local_admins = ''

            $.each(data, function(i,d){
                // Generate rows from data
                var rows = ''

                for (var prop in d){
                    // Blank empty rows
                    if((d[prop] == '' || d[prop] == null) && d[prop] !== 0) {
                        rows = rows
                    }

                    else if((prop == 'ssh_access' || prop == 'screenshare_access') && d[prop] == 1){
                        rows = rows + '<tr><th>'+i18n.t('users.'+prop)+'</th><td><span class="label label-danger">'+i18n.t('on')+'</span></td></tr>';
                    }
                    else if((prop == 'ssh_access' || prop == 'screenshare_access') && d[prop] == 0){
                        rows = rows + '<tr><th>'+i18n.t('users.'+prop)+'</th><td><span class="label label-success">'+i18n.t('off')+'</span></td></tr>';
                    }
                    
                    else if(prop == 'administrator' && d[prop] == 1){
                        rows = rows + '<tr><th>'+i18n.t('users.'+prop)+'</th><td><span class="label label-danger">'+i18n.t('yes')+'</span></td></tr>';
                        local_admins = local_admins + d['record_name'] + " (" + d['unique_id'] + ") "
                    }
                    else if((prop == 'administrator' || prop == 'autologin_enabled') && d[prop] == 0){
                        rows = rows + '<tr><th>'+i18n.t('users.'+prop)+'</th><td><span class="label label-success">'+i18n.t('no')+'</span></td></tr>';
                    }
                    else if(prop == 'autologin_enabled' && d[prop] == 1){
                        rows = rows + '<tr><th>'+i18n.t('users.'+prop)+'</th><td><span class="label label-danger">'+i18n.t('yes')+'</span></td></tr>';
                    }

                    else if((prop == 'copy_timestamp' || prop == 'creation_time' || prop == 'smb_password_last_set' || prop == 'linked_timestamp' || prop == 'failed_login_timestamp' || prop == 'password_last_set_time' || prop == 'last_login_timestamp') && parseInt(d[prop]) > 0){
                        var date = new Date(d[prop] * 1000);
                        rows = rows + '<tr><th>'+i18n.t('users.'+prop)+'</th><td><span title="'+moment(date).fromNow()+'">'+moment(date).format('llll')+'</span></td></tr>';
                    }
                    else {
                        rows = rows + '<tr><th>'+i18n.t('users.'+prop)+'</th><td>'+d[prop]+'</td></tr>';
                    }
                }

                $('#users-tab')
                .append($('<h4>')
                    .append($('<i>')
                        .addClass('fa fa-user'))
                    .append(' '+d.record_name))
                .append($('<div style="max-width:750px;">')
                    .append($('<table>')
                        .addClass('table table-striped table-condensed')
                        .append($('<tbody>')
                            .append(rows))))
            })

            // Appent local admins to client detail table
            $('#mr-users-table').append('<tr><th>'+i18n.t('users.local_administrators')+'</th><td>'+local_admins+'</td></tr>')
        }
    });
});
</script>
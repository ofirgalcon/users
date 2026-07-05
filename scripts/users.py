#!/usr/local/munkireport/munkireport-python3
# Written for MunkiReport by tuxudo

import subprocess
import os
import plistlib
import re
from datetime import datetime
import time

import sys

# Import ctypes before other framework imports to avoid conflicts
try:
    from ctypes import (CDLL,
                        Structure,
                        POINTER,
                        c_int64,
                        c_int32,
                        c_int16,
                        c_char,
                        c_uint32)
    from ctypes.util import find_library
except (AttributeError, ImportError) as e:
    # Python 3.12 ctypes compatibility issue - ctypes module is broken
    # This indicates a corrupted or incomplete Python 3.12 installation
    # The ctypes module itself fails to load, not our code
    error_msg = str(e) if e else "Unknown error"
    print(f"Error importing ctypes: {error_msg}", file=sys.stderr)
    print("This indicates a broken Python 3.12 installation. Please reinstall Python 3.12.", file=sys.stderr)
    sys.exit(1)

from SystemConfiguration import SCDynamicStoreCopyConsoleUser

from Foundation import CFPreferencesCopyAppValue

# constants
c = CDLL(find_library("System"))

class timeval(Structure):
    _fields_ = [
                ("tv_sec",  c_int64),
                # ("tv_usec", c_int32),
               ]

class utmpx(Structure):
    _fields_ = [
                ("ut_user", c_char*256),
                ("ut_id",   c_char*4),
                ("ut_line", c_char*32),
                ("ut_pid",  c_int32),
                ("ut_type", c_int16),
                ("ut_tv",   timeval),
                ("ut_host", c_char*256),
                ("ut_pad",  c_uint32*16),
                ]

_volume_owner_lookup_cache = None

def readPlist(plist):
    if not isinstance(plist, bytes):
        plist = plist.encode()

    try:
        return plistlib.readPlistFromString(plist)
    except AttributeError:
        try:
            return plistlib.loads(plist)
        except Exception:
            return {}
    except Exception:
        return {}

def run_command(command):
    proc = subprocess.Popen(command, shell=False, bufsize=-1,
                            stdin=subprocess.PIPE,
                            stdout=subprocess.PIPE,
                            stderr=subprocess.PIPE)
    (stdout, stderr) = proc.communicate()
    return proc.returncode, stdout.decode('utf-8', errors='ignore'), stderr.decode('utf-8', errors='ignore')

def normalize_guid(guid):
    if not guid:
        return ''
    return guid.strip().lower().strip('{}')

def get_secure_token_status(record_name):
    if not record_name:
        return None

    cmd = ['/usr/sbin/sysadminctl', '-secureTokenStatus', record_name]
    (returncode, stdout, stderr) = run_command(cmd)
    if returncode != 0:
        return None

    output = ('%s\n%s' % (stdout, stderr)).lower()
    if 'secure token is enabled for user' in output:
        return 1
    if 'secure token is disabled for user' in output:
        return 0
    return None

def get_volume_owner_lookup():
    global _volume_owner_lookup_cache

    if _volume_owner_lookup_cache is not None:
        return _volume_owner_lookup_cache

    cmd = ['/usr/sbin/diskutil', 'apfs', 'listUsers', '/']
    (returncode, stdout, stderr) = run_command(cmd)

    if returncode != 0:
        _volume_owner_lookup_cache = None
        return _volume_owner_lookup_cache

    guid_regex = re.compile(r'[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}')
    volume_owners = {}
    current_guid = None

    for line in stdout.splitlines():
        line = line.strip()

        guid_match = guid_regex.search(line)
        if guid_match:
            current_guid = normalize_guid(guid_match.group(0))
            continue

        if current_guid and 'Volume Owner:' in line:
            if 'Yes' in line:
                volume_owners[current_guid] = 1
            elif 'No' in line:
                volume_owners[current_guid] = 0
            current_guid = None

    _volume_owner_lookup_cache = volume_owners
    return _volume_owner_lookup_cache

def get_volume_owner_status(generated_uuid):
    normalized_uuid = normalize_guid(generated_uuid)
    if not normalized_uuid:
        return None

    lookup = get_volume_owner_lookup()
    if lookup is None:
        return None

    return lookup.get(normalized_uuid, 0)

def get_users_info():

    # Get all users info as plist
    cmd = ['/usr/bin/dscl', '-plist', '.', '-readall', '/Users']
    proc = subprocess.Popen(cmd, shell=False, bufsize=-1,
                            stdin=subprocess.PIPE,
                            stdout=subprocess.PIPE, stderr=subprocess.PIPE)
    (output, unused_error) = proc.communicate()

    try:
        return readPlist(output)
    except Exception:
        return {}

def get_group_names():

    # Get all groups info as plist
    cmd = ['/usr/bin/dscl', '-plist', '.', '-readall', '/Groups']
    proc = subprocess.Popen(cmd, shell=False, bufsize=-1,
                            stdin=subprocess.PIPE,
                            stdout=subprocess.PIPE, stderr=subprocess.PIPE)
    (output, unused_error) = proc.communicate()

    try:
        groups_pl = readPlist(output)

        group_names = {}

        # Process all groups to make translate array
        for group in groups_pl:

            # Process each group record name, some of more than one record name
            if "dsAttrTypeStandard:RealName" in group:
                for record_name in group["dsAttrTypeStandard:RecordName"]:
                    if "Public Folder" not in group["dsAttrTypeStandard:RealName"][0].rstrip():
                        group_names.update({record_name: group["dsAttrTypeStandard:RealName"][0].rstrip()})

        return group_names

    except Exception:
        return {}

def process_user_info(all_users,group_names):
    out = []
    current_user = get_current_user()

    for user in all_users:

        # Skip service accounts
        if 'dsAttrTypeStandard:UserShell' not in list(user.keys()) or user['dsAttrTypeStandard:UserShell'][0] == "/usr/bin/false" or 'dsAttrTypeStandard:NFSHomeDirectory' not in list(user.keys()) or user['dsAttrTypeStandard:NFSHomeDirectory'][0] == "/var/setup" or user['dsAttrTypeStandard:NFSHomeDirectory'][0] == "/var/spool/uucp" or user['dsAttrTypeStandard:RecordName'][0] == "root":
            continue

        user_atts = {}
        user_atts['current_user'] = current_user
        user_atts['is_hidden'] = 0

        for user_att in user:

            if user_att == 'dsAttrTypeStandard:RealName':
                user_atts['real_name'] = user[user_att][0]
            elif user_att == 'dsAttrTypeNative:naprivs':
                user_atts['ard_priv'] = user[user_att][0]
            elif user_att == 'dsAttrTypeStandard:AppleMetaNodeLocation':
                user_atts['node_location'] = user[user_att][0]
            elif user_att == 'dsAttrTypeStandard:AuthenticationHint' and user_account_hints_enabled():
                user_atts['password_hint'] = user[user_att][0]
            elif user_att == 'dsAttrTypeStandard:GeneratedUID':
                user_atts['generated_uuid'] = user[user_att][0]
            elif user_att == 'dsAttrTypeStandard:NFSHomeDirectory':
                user_atts['home_directory'] = user[user_att][0]
            elif user_att == 'dsAttrTypeStandard:PrimaryGroupID':
                user_atts['primary_group_id'] = user[user_att][0]
            elif user_att == 'dsAttrTypeStandard:RecordName':
                user_atts['record_name'] = user[user_att][0]

                # Check for autologin
                if user_account_auto_login_enabled() == user[user_att][0]:
                    user_atts['autologin_enabled'] = 1
                else:
                    user_atts['autologin_enabled'] = 0

                # Process user's groups
                try:

                    # Get groups from id command
                    cmd = ['/usr/bin/id', '-Gn', user['dsAttrTypeStandard:RecordName'][0]]
                    proc = subprocess.Popen(cmd, shell=False, bufsize=-1,
                                            stdin=subprocess.PIPE,
                                            stdout=subprocess.PIPE, stderr=subprocess.PIPE)
                    (output, unused_error) = proc.communicate()

                    groups_list = []

                    # Translate each group to real name
                    for group in output.decode().split(' '):
                        try:
                            groups_list.append(group_names[group.rstrip()])
                        except KeyError:
                            continue

                    user_atts['group_memership'] = ", ".join(sorted(groups_list))

                    # Check for administrator
                    if "Administrators" in groups_list:
                        user_atts['administrator'] = 1
                    else:
                        user_atts['administrator'] = 0

                    # Check for SSH
                    if "SSH Service" in user_atts['group_memership']:
                        user_atts['ssh_access'] = 1
                    else:
                        user_atts['ssh_access'] = 0

                    # Check for Screensharing
                    if "Screensharing Service" in user_atts['group_memership']:
                        user_atts['screenshare_access'] = 1
                    else:
                        user_atts['screenshare_access'] = 0

                except:
                    user_atts['group_memership'] = ""

            elif user_att == 'dsAttrTypeStandard:UniqueID':
                user_atts['unique_id'] = user[user_att][0]
            elif user_att == 'dsAttrTypeStandard:UserShell':
                user_atts['user_shell'] = user[user_att][0]
            elif user_att == 'dsAttrTypeStandard:AppleMetaRecordName':
                user_atts['meta_record_name'] = user[user_att][0]
            elif user_att == 'dsAttrTypeStandard:EMailAddress':
                user_atts['email_address'] = user[user_att][0]
            elif user_att == 'dsAttrTypeStandard:SMBGroupRID':
                user_atts['smb_group_rid'] = user[user_att][0]
            elif user_att == 'dsAttrTypeStandard:SMBHome':
                user_atts['smb_home'] = user[user_att][0]
            elif user_att == 'dsAttrTypeStandard:SMBHomeDrive':
                user_atts['smb_home_drive'] = user[user_att][0]
            elif user_att == 'dsAttrTypeStandard:SMBPrimaryGroupSID':
                user_atts['smb_primary_group_sid'] = user[user_att][0]
            elif user_att == 'dsAttrTypeStandard:SMBSID':
                user_atts['smb_sid'] = user[user_att][0]
            elif user_att == 'dsAttrTypeStandard:SMBScriptPath':
                user_atts['smb_script_path'] = user[user_att][0]
            elif user_att == 'dsAttrTypeStandard:OriginalNodeName':
                user_atts['original_node_name'] = user[user_att][0]
            elif user_att == 'dsAttrTypeStandard:PrimaryNTDomain':
                user_atts['primary_nt_domain'] = user[user_att][0]
            elif user_att == 'dsAttrTypeStandard:CopyTimestamp':
                try:
                    user_atts['copy_timestamp'] = str(time.mktime(datetime.strptime(user[user_att][0].strip(), "%Y-%m-%dT%H:%M:%SZ").timetuple()))
                except:
                    pass
            elif user_att == 'dsAttrTypeStandard:SMBPasswordLastSet':
                user_atts['smb_password_last_set'] = str((int(user[user_att][0])/10000000)-11644473600)

            elif user_att == 'dsAttrTypeNative:accountPolicyData':
                try:
                    policy_data = readPlist(user[user_att][0])
                    for policy_item in policy_data:
                        if policy_item == "creationTime" and ":" in str(policy_data[policy_item]):
                            user_atts['creation_time'] = str(int(time.mktime(policy_data[policy_item].timetuple())))
                        elif policy_item == "creationTime":
                            user_atts['creation_time'] = str(int(policy_data[policy_item]))
                        elif policy_item == "failedLoginCount":
                            user_atts['failed_login_count'] = policy_data[policy_item]
                        elif policy_item == "failedLoginTimestamp" and ":" in str(policy_data[policy_item]):
                            user_atts['failed_login_timestamp'] = str(int(time.mktime(policy_data[policy_item].timetuple())))
                        elif policy_item == "failedLoginTimestamp":
                            user_atts['failed_login_timestamp'] = str(int(policy_data[policy_item]))
                        elif policy_item == "passwordLastSetTime" and ":" in str(policy_data[policy_item]):
                            user_atts['password_last_set_time'] = str(int(time.mktime(policy_data[policy_item].timetuple())))
                        elif policy_item == "passwordLastSetTime":
                            user_atts['password_last_set_time'] = str(int(policy_data[policy_item]))
                        ## This is commented out to force the script to always use the much more reliable method below
                        # elif policy_item == "lastLoginTimestamp" and ":" in str(policy_data[policy_item]):
                            # user_atts['last_login_timestamp'] = str(int(time.mktime(policy_data[policy_item].timetuple())))
                        # elif policy_item == "lastLoginTimestamp":
                            # user_atts['last_login_timestamp'] = str(int(policy_data[policy_item]))
                        elif policy_item == "passwordHistoryDepth":
                            user_atts['password_history_depth'] = policy_data[policy_item]
                except:
                    pass

            elif user_att == 'dsAttrTypeNative:LinkedIdentity':

                try:
                    linkid_data = (readPlist(user[user_att][0]))["appleid.apple.com"]['linked identities'][0]

                    for linkit_item in linkid_data:
                        if linkit_item == "full name":
                            user_atts['linked_full_name'] = linkid_data[linkit_item]
                        elif linkit_item == "timestamp":
                            user_atts['linked_timestamp'] = str(int(time.mktime(linkid_data[linkit_item].timetuple())))
                except:
                    user_atts['linked_full_name'] = ""

            elif user_att == 'dsAttrTypeNative:IsHidden':
                user_atts['is_hidden'] = to_bool(user[user_att][0])

        # Get the last login timestamp
        if 'last_login_timestamp' not in user_atts and 'record_name' in user_atts:
            try:
                last_login_timestamp = last_login_time(user_atts['record_name'])
                if last_login_timestamp != "":
                    user_atts['last_login_timestamp'] = last_login_timestamp
            except:
                pass

        if 'record_name' in user_atts:
            secure_token_status = get_secure_token_status(user_atts['record_name'])
            if secure_token_status is not None:
                user_atts['secure_token'] = secure_token_status

        if 'generated_uuid' in user_atts:
            volume_owner_status = get_volume_owner_status(user_atts['generated_uuid'])
            if volume_owner_status is not None:
                user_atts['volume_owner'] = volume_owner_status

        out.append(user_atts)
    return out

def get_current_user():
    # From https://macmule.com/2014/11/19/how-to-get-the-currently-logged-in-user-in-a-more-apple-approved-way/

    username = (SCDynamicStoreCopyConsoleUser(None, None, None) or [None])[0]
    username = [username,""][username in [u"loginwindow", None, u""]]

    if username == "_mbsetupuser":
        username = "Setup Assistant"
    elif username == "loginwindow":
        username = "Login Window"
    elif username == "root":
        username = "root"
    elif username == "":
        username = "None"

    return username

def last_login_time(user_name):
    """This method will replicate the functionallity of the /usr/bin/last
    command to output all logins, reboots, and shutdowns. We then calculate
    the logout.

    session takes on of the following strings:
        * gui
        * gui_ssh
        * all
    """
    # This is largely from the user_sessions script

    # local constants
    setutxent_wtmp = c.setutxent_wtmp
    setutxent_wtmp.restype = None
    getutxent_wtmp = c.getutxent_wtmp
    getutxent_wtmp.restype = POINTER(utmpx)
    endutxent_wtmp = c.setutxent_wtmp
    endutxent_wtmp.restype = None
    # data storage
    events = []
    # initialize
    setutxent_wtmp(0)
    entry = getutxent_wtmp()

    while entry:
        e = entry.contents
        entry = getutxent_wtmp()
        event = {}

        # Return the first console login for the specifed username
        if e.ut_type == 7 and e.ut_line.decode("utf-8", errors="ignore") == "console" and e.ut_user.decode("utf-8", errors="ignore") == user_name:
            return str(int(e.ut_tv.tv_sec))

    # finish
    endutxent_wtmp()
    return ""

def user_account_hints_enabled():
    return CFPreferencesCopyAppValue('user_account_hints_enabled', 'MunkiReport')

def user_account_auto_login_enabled():
    return CFPreferencesCopyAppValue('autoLoginUser', 'com.apple.loginwindow')

def to_bool(s):
    if s == True or s == "YES":
        return 1
    else:
        return 0

def main():
    """Main"""

    # Get results
    result = dict()
    result = process_user_info(get_users_info(),get_group_names())

    # Write users results to cache

    cachedir = '%s/cache' % os.path.dirname(os.path.realpath(__file__))
    output_plist = os.path.join(cachedir, 'users.plist')
    try:
        plistlib.writePlist(result, output_plist)
    except Exception:
        with open(output_plist, 'wb') as fp:
            plistlib.dump(result, fp, fmt=plistlib.FMT_XML)

if __name__ == "__main__":
    main()

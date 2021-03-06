<? session_start(); ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <link href="css/jplayer.morning.light.css" rel="stylesheet" type="text/css" />
        <!--<link href="http://jplayer.org/latest/skin/pink.flag/jplayer.pink.flag.css" rel="stylesheet" type="text/css" />--> 
        
        <script type="text/javascript" src="js/jquery-1.9.1.min.js"></script>
        <script type="text/javascript" src="js/jquery-migrate-1.1.1.min.js"></script>
        <script type="text/javascript" src="js/jquery.jplayer.min.js"></script>
        <script type="text/javascript" src="js/jquery.jplayer.inspector.js"></script>
        <script type="text/javascript" src="js/jplayer.playlist.min.js"></script>
        <script type="text/javascript" src="js/json2.js"></script>
        <!--<script type="text/javascript" src="js/jquery.mockjax.js"></script>-->
        <!--<script type="text/javascript" src="js/emethplayer_mockjax.js"></script>-->
        <script type="text/javascript" src="js/icanhaz.js"></script>
<script type="text/javascript" id="globals">
var get_current_info_timeout;
var curr_playing = {};
var update_current_lock = 0;
var last_update_time = -10;
var jplist;
var available_tracks = {};
</script>
        
<script type="text/javascript" id="ajax_calls">
function get_logged_in_user()
{
    jQuery.ajax({
        type: "POST",
        url: "ajax.php?act=get_logged_in_user",
        dataType:'json',
        data: { },
        success: function(data) {
            console.log(data);
            if (data.status == "success")
            {
                jQuery('.username_display').html(data.email);
                jQuery('.logged_in').css('display', 'block');
                jQuery('.logged_out').css('display', 'none');
                if (data.curr_playing != -1)
                    resume_track(data.curr_playing.audio_data, data.curr_playing.audio_time);
            }
            else
            {
                alert("Error: "+data.error);
            }
        },
        error: function (e) {
            console.log("error", e);
        }
    });
    load_playlist();
}

function load_playlist()
{
    jQuery.ajax({
        type: "POST",
        url: "ajax.php?act=get_playlist",
        dataType:'json',
        data: { },
        success: function(data) {
            console.log(data);
            if (data.status == "success")
            {
                jQuery.each(data.playlist, function(k,v){
                    add_to_playlist(data.playlist_data[v].title, data.playlist_data[v].file_loc, v, data.playlist_data[v].author_name, data.playlist_data[v].sermon_timestamp);
                });

            }
            else
            {
                alert("Error: "+data.error);
            }
        },
        error: function (e) {
            console.log("error", e);
        }
    });
}

function logout()
{
    jQuery.ajax({
        type: "POST",
        url: "ajax.php?act=logout",
        dataType:'json',
        data: {},
        success: function(data) {
            console.log(data);
            jQuery('.logged_in').css('display', 'none');
            jQuery('.logged_out').css('display', 'block');
        },
        error: function (e) {
            console.log("error", e);
        }
    });
}

function login()
{
    var dataz = {
        'login_email': jQuery('#login_email').val(), 
        'login_pass': jQuery('#login_pass').val(), 
    };
    jQuery.ajax({
        type: "POST",
        url: "ajax.php?act=login",
        dataType:'json',
        data: dataz,
        success: function(data) {
            console.log(data);
            if (data.status == "success")
            {
                jQuery('.username_display').html(data.email);
                jQuery('.logged_in').css('display', 'block');
                jQuery('.logged_out').css('display', 'none');
                jQuery('.username_display').html(data.email);
                if (data.curr_playing != -1)
                    resume_track(data.curr_playing.audio_data, data.curr_playing.audio_time);
            }
            else
            {
                alert("Error: "+data.error);
            }
        },
        error: function (e) {
            console.log("error", e);
        }
    });
}

function register()
{
    var dataz = {
        'register_email': jQuery('#register_email').val(), 
        'register_pass1': jQuery('#register_pass1').val(), 
        'register_pass2': jQuery('#register_pass2').val(), 
    };
    if (dataz['register_pass1'] != dataz['register_pass2'])
    {
        alert("Error: Your passwords did not match.");
    }
    else
    {
        jQuery.ajax({
            type: "POST",
            url: "ajax.php?act=register",
            dataType:'json',
            data: dataz,
            success: function(data) {
                console.log(data);
                if (data.status == "success")
                {
                    jQuery('.username_display').html(data.email);
                    jQuery('.logged_in').css('display', 'block');
                    jQuery('.logged_out').css('display', 'none');
                    alert("Thanks for registering, "+data.email+"!");
                    if (data.curr_playing != -1)
                        resume_track(data.curr_playing.audio_data, data.curr_playing.audio_time);
                }
                else
                {
                    alert("Error: "+data.error);
                }
            },
            error: function (e) {
                console.log("error", e);
            }
        });
    }
}

function get_available_tracks()
{
    jQuery.ajax({
        type: "POST",
        url: "ajax.php?act=get_available_tracks",
        dataType:'json',
        data: {
        },
        success: function(data) {
            jQuery('#available_tracks').html('');
            jQuery.each(data['tracks'], function (k, v) {
                available_tracks[v['id']] = v;
                jQuery('#available_tracks').append(ich.available_track_template(v));
            });
        },
        error: function (e) {
            console.log("error", e);
        }
    });
}
</script>

<script type="text/javascript">
    
    
$(document).ready(function() {
    get_logged_in_user();
    // http://www.jplayer.org/latest/developer-guide/
    // http://www.jplayer.org/latest/quick-start-guide/
    
    $("#jquery_jplayer_1").jPlayer({
        ready: function(event) {
            
        },
        playing: function(event) {
            //console.log(event.jPlayer.status)
        },
        timeupdate: function(event) {
            curr_playing['current_playing_time'] = parseInt(event.jPlayer.status.currentTime);
            curr_playing['total_playing_time'] = parseInt(event.jPlayer.status.duration); //curr_playing['total_playing_time'] = parseInt(jQuery(this).data().jPlayer.status.duration);
            update_current();
        },
        swfPath: "js",
        supplied: "mp3"
    });
    $("#jplayer_inspector").jPlayerInspector({jPlayer:$("#jquery_jplayer_1")});
    
    get_available_tracks();
});

function new_current()
{
    update_current_lock = 1;
    last_update_time = -10;
    jQuery.ajax({
        url: 'ajax.php?act=new_current',
        type:"POST",
        dataType:'json',
        data: {
            'audio_id': curr_playing['id']
        },
        error:function (e) {
            console.log('error', e);
            update_current_lock = 0;
        },
        success:function (data) {
            console.log("new_current_success", data);
            update_current_lock = 0;
        }
    });
}

function save_playlist()
{
    var playlist = [];
    jQuery('.playlist_td').each(function(){
        playlist.push(jQuery(this).attr('data-id'));
    });

    jQuery.ajax({
        url: 'ajax.php?act=save_playlist',
        type:"POST",
        dataType:'json',
        data: {
            'playlist': playlist.join('|')
        },
        error:function (e) {
            console.log('error', e);
        },
        success:function (data) {
            console.log("save_playlist", data);
        }
    });
}

function update_current()
{
    if (update_current_lock == 0 && Math.abs(last_update_time - curr_playing['current_playing_time']) >= 10)
    {
        update_current_lock = 1;
        last_update_time = curr_playing['current_playing_time'];
        jQuery.ajax({
            url: 'ajax.php?act=update_current',
            type:"POST",
            dataType:'json',
            data: {
                'audio_id': curr_playing['id'],
                'audio_time': curr_playing['current_playing_time']
            },
            error:function (e) {
                console.log('error', e);
                update_current_lock = 0;
            },
            success:function (data) {
                console.log("update_current_success", data);
                update_current_lock = 0;
            }
        });
    }
}

function add_to_playlist(title, mp3, id, author_name, sermon_timestamp)
{
    if (jQuery(".playlist_"+id).length <= 0)
    {
        var dataz = {
            'file_loc': mp3,
            'id': id,
            'author_name': author_name,
            'title': title,
            'sermon_timestamp': sermon_timestamp
        };
        jQuery('#playlist').append(ich.playlist_track(dataz));
        save_playlist();
    }
}

function remove_from_playlist(pid)
{
    jQuery(".playlist_"+pid).remove();
    save_playlist();
}


function play_track(info)
{
    update_current_lock = 1;
    set_current_track_local(info);
    new_current();
}
function resume_track(info, track_time)
{
    console.log("resuming track", info, track_time);
    set_current_track_local(info);
    if (track_time > 0)
        setTimeout("play_from_time("+track_time+")", 1000); //to bypass bug that occurs for html5 solution when calling play with time right after setmedia
}
function play_from_time(track_time)
{
    $("#jquery_jplayer_1").jPlayer("play", track_time);
}
function set_current_track_local(info)
{
    curr_playing = info;
    $("#jquery_jplayer_1").jPlayer("setMedia", {
        mp3: info.file_loc
    });
    jQuery('#curr_title').html(info.title);
    jQuery('#curr_speaker').html(info.author_name);
    jQuery('#curr_church').html(info.church);
    jQuery('#curr_church_website').attr('src', info.church_website);
    jQuery('#curr_description').html(info.description);
    jQuery('#curr_total_plays').html(info.plays);
    jQuery('#curr_date').html('...');
}

function next_track()
{
    console.log("Loading next track...");
    if (jQuery('#playlist').find('tr:first-child').length > 0)
    {
        var load_track = available_tracks[jQuery('#playlist').find('tr:first-child').find('td:first-child').attr('data-id')];
        play_track(load_track);
        jQuery('#playlist').find('tr:first-child').remove();
        $("#jquery_jplayer_1").jPlayer("play");
    }
}
</script>
<style>
#content {
    height: 500px;
    width: 1000px;
    overflow: scroll;
}
#bottom_bar {
    position: fixed;
    z-index: 100; 
    bottom: 0; 
    left: 0;
    width: 100%;
}
        </style>
    </head>
    <body>
        <button onclick="sb()">sb</button>
<? print_r($_SESSION); ?>

<span class='logged_in' style='display:none;'>You are logged in as <span class='username_display'></span>.</span>
<span class='logged_out' style='display:block;'>You are not logged in.</span>
<div id='content'>
    <div id='available_tracks'></div>
</div>

        <div id="jquery_jplayer_1" class="jp-jplayer"></div>

        <div id="jp_container_1" class="jp-audio">
            <div class="jp-type-playlist">
                <div class="jp-gui jp-interface">
                    <ul class="jp-controls">
                        <li><a href="javascript:;" class="jp-previous" tabindex="1">previous</a></li>
                        <li><a href="javascript:;" class="jp-play" tabindex="1">play</a></li>
                        <li><a href="javascript:;" class="jp-pause" tabindex="1">pause</a></li>
                        <li><a href="javascript:;" onclick="next_track();" class="jp-next" tabindex="1">next</a></li>
                        <li><a href="javascript:;" class="jp-stop" tabindex="1">stop</a></li>
                        <li><a href="javascript:;" class="jp-mute" tabindex="1" title="mute">mute</a></li>
                        <li><a href="javascript:;" class="jp-unmute" tabindex="1" title="unmute">unmute</a></li>
                        <li><a href="javascript:;" class="jp-volume-max" tabindex="1" title="max volume">max volume</a></li>
                    </ul>
                    <div class="jp-progress">
                        <div class="jp-seek-bar">
                            <div class="jp-play-bar"></div>

                        </div>
                    </div>
                    <div class="jp-volume-bar">
                        <div class="jp-volume-bar-value"></div>
                    </div>
                    <div class="jp-current-time"></div>
                    <div class="jp-duration"></div>
                </div>
                <div class="jp-no-solution">
                    <span>Update Required</span>
                    To play the media you will need to either update your browser to a recent version or update your <a href="http://get.adobe.com/flashplayer/" target="_blank">Flash plugin</a>.
                </div>
            </div>
        </div>
        <span id='curr_title' style="font-weight:bold;">None</span><br />
        <span id='curr_description'></span><br />
        by <span id='curr_speaker'></span> on <span id='curr_date'></span><br />
        <a id='curr_church_website' href=""><span id='curr_church'></span></a>
        <span id='curr_total_plays'></span> plays.<br />
                   
<!-- tracks have 3 flags - played, downloaded, hidden -->
<table id='playlist'>
    
</table>

    <div id="jplayer_inspector"></div>

<br /><br />

<span class='logged_out' style='display:block;'>
    <hr />
    <h2>Login</h2>
    Email: <input type='text' id='login_email' onkeypress="if(event.keyCode==13) {login();}"><br />
    Password: <input type='password' id='login_pass' onkeypress="if(event.keyCode==13) {login();}"><br />
    <button onclick="login()">Submit</button><br /><br />
    
    <hr />
    <h2>Register</h2>
    Email: <input type='text' id='register_email' onkeypress="if(event.keyCode==13) {register();}"><br />
    Password: <input type='password' id='register_pass1' onkeypress="if(event.keyCode==13) {register();}"><br />
    Password Again: <input type='password' id='register_pass2' onkeypress="if(event.keyCode==13) {register();}"><br />
    <button onclick="register()">Submit</button><br /><br />
</span>

<span class='logged_in' style='display:none;'>
    <hr />
    <button onclick="logout()">Logout</button><br /><br />
</span>

    
<script type="text/html" id="available_track_template">
<table data-url="{{ file_loc }}">
    <tr>
        <td><h4 onclick="add_to_playlist('{{ title }}', '{{ file_loc }}', '{{ id }}', '{{ author_name }}', '{{ sermon_timestamp }}')">[Add to Playlist] {{ author_name }}: {{ title }}</h4></td>
        <td>{{ scripture }}</td>
    </tr>
    <tr>
        <td colspan=2>{{ description }}<br />Date: {{ sermon_timestamp }}</td>
    </tr>
    <tr>
        <td><a target="_blank" href='{{ church_website }}'>{{ church }}</a></td><td>Plays: {{ plays }}</td><td><a href='{{ file_loc }}'>Download</a></td>
    </tr>
</table>
</script>
<script type="text/html" id="playlist_track">
    <tr class="playlist_{{ id }}">
        <td class="playlist_td" data-url="{{ file_loc }}" data-id="{{ id }}"><h4>[<a href="javascript:void" onclick="remove_from_playlist('{{ id }}')">X</a>] {{ author_name }}: {{ title }}</h4></td> <td colspan=2>Date: {{ sermon_timestamp }}</td><td></td>
    </tr>
</script>

</body>
</html>
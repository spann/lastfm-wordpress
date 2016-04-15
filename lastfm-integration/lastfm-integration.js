var $ = jQuery;

var lastfmParams = $("#span_lastfm_spann_params");
var lastfmUsername = lastfmParams.attr("lastfm_spann_username");
var lastfmTracks = lastfmParams.attr("lastfm_spann_max_number_of_tracks");
var lastfmRefreshInterval = lastfmParams.attr("lastfm_spann_refresh_interval");
if (lastfmRefreshInterval == undefined || isNaN(lastfmRefreshInterval) || lastfmRefreshInterval == "") {
    lastfmRefreshInterval = 60;
}
var lastfmPlaybackSwitch = lastfmParams.attr("lastfm_spann_play_videos_on_click").toLowerCase();

var apiKey = "734ded501071f914dd5347659619c1b9";
var lastfmApiUrl = "http://ws.audioscrobbler.com/2.0/?method=user.getrecenttracks&user=" + lastfmUsername + "&api_key=" + apiKey + "&format=json&random=" + Math.random();

var isMobile = window.matchMedia("only screen and (max-width: 760px)");

function setDefaultValues() {
    if (lastfmTracks == undefined || isNaN(lastfmTracks) || lastfmTracks == "") {
        lastfmTracks = 20;
    }

    if (lastfmPlaybackSwitch != "yes" && lastfmPlaybackSwitch != "no") {
        lastfmPlaybackSwitch = "yes";
    }
}

function dataIsValid() {
    setDefaultValues();
    return !(lastfmUsername == undefined || lastfmUsername == "");
}

function createDivs() {
    var mainDiv = $("#div_lastfm_spann");
    mainDiv.html("");
    mainDiv.html('<div id="lastfm_spann_top" style="overflow: hidden; width: 100%; font-size: 11px; height: 64px;"><div id="lastfm_spann_last_track" youtube_url="http://youtube.com" style="width: 64px; height: 64px; float: left; cursor: pointer;"  onclick="onTrackClick(\'lastfm_spann_track_0\')"><img id="lastfm_spann_last_track_img" src="" style="width: 64px; height: 64px;" /></div><div id="lastfm_spann_last_track_details" style="max-width: 150px; height: 64px; float: left; padding-top: 1px; padding-bottom: 1px;"><div id="lastfm_spann_track_name" style="line-height: 20px; text-overflow: ellipsis; padding-left: 10px; font-weight: bold"></div><div id="lastfm_spann_artist_name" style="line-height: 20px; overflow: hidden; padding-left: 10px; padding-right: 10px;"></div><div id="lastfm_spann_track_status" style="line-height: 20px; overflow: hidden; padding-left: 10px; padding-right: 10px;"></div></div></div><div id="lastfm_spann_bottom" style="overflow: hidden; margin-top: 9px; width: 100%; min-height: 35px;"><div style="clear:both"></div></div>');
}

function displayInitialTrack() {
    displayMainTrack('lastfm_spann_track_0');
}

function displayMainTrack(id) {
    var trackDiv = $("#" + id);
    var playedTime = trackDiv.attr('track_status');
    if (!isNaN(playedTime)) {
        var curTime = Date.now() / 1000;
        var timeDiff = (curTime - playedTime);
        var mins = timeDiff / 60;
        var hours = mins / 60;

        if (!isNaN(hours) && hours > 1) {
            playedTime = Math.floor(hours) + " hours ago"
        } else if (!isNaN(mins) && mins > 1) {
            playedTime = Math.floor(mins) + " minutes ago"
        }
    }

    var img = trackDiv.find('img:first');
    $("#lastfm_spann_track_name").text(trackDiv.attr('track_name'));
    $("#lastfm_spann_artist_name").text(trackDiv.attr('artist_name'));
    $("#lastfm_spann_track_status").text(playedTime);
    $("#lastfm_spann_last_track_img").attr('src', img.attr('src'));
}

function onTrackHover(id) {
    displayMainTrack(id);
}

function onTrackClick(id) {
    if (lastfmPlaybackSwitch == "yes") {
        var trackDiv = $("#" + id);
        var popupDiv = $("#div_lastfm_spann_popup");

        var trackName = trackDiv.attr('track_name');
        var artistName = trackDiv.attr('artist_name');
        var widthHeightHtml = " width='640px' height='360px' ";

        if (isMobile.matches) {
            widthHeightHtml = " width='300px' height='150px' ";
            popupDiv.css({width: '300px', height: '150px', position: 'fixed', top: 0, bottom: 0, left: 0, right: 0, margin: 'auto', display: 'none'});
        } else {
            popupDiv.css({width: '640px', height: '360px', position: 'fixed', top: 0, bottom: 0, left: 0, right: 0, margin: 'auto', display: 'none'});
        }

        var youtube_embed_url = "https://www.youtube.com/embed/?listType=search&list=" + trackName + "+%20+" + artistName + "&autoplay=1";
        var html = '<iframe ' + widthHeightHtml + ' src="' + youtube_embed_url + '" frameborder="0" allowfullscreen></iframe>';

        popupDiv.html(html);
        popupDiv.css({"z-index": 50002, "display": "block"});

        var popupBackground = $("#div_lastfm_spann_popup_background");
        popupBackground.css({"z-index": 50001, "display": "block", "animation-name": "popupBackground", "animation-duration": "4s", "animation-delay": "1s", "animation-fill-mode": "forwards"});
    }
}

function hideLastfmVideo() {
    var popupBackground = $("#div_lastfm_spann_popup_background");
    var popupDiv = $("#div_lastfm_spann_popup");
    popupDiv.html('');
    popupDiv.css({"z-index": -1002, "display": "none"});
//    popupBackground.css({"animation-name": "popupBackground", "animation-duration": "2s", "animation-fill-mode": "forwards", "animation-direction": "reverse"});
    setTimeout(function () {
        popupBackground.css({"z-index": -1003, "display": "none", "background": "transparent"})
    }, 10);
}

function displayTrackInfo(track, i) {
    var trackHistoryDiv = $("#lastfm_spann_bottom");
    var imageSource = "data:image/png;base64,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";
    var trackImageSource = track.image[1]['#text'];
    if (trackImageSource != undefined && trackImageSource != "") {
        imageSource = trackImageSource;
    }

    var trackStatus = "now playing...";
    if (track['date'] != undefined) {
        trackStatus = track['date']['uts'];
    }
    trackHistoryDiv.append('<div id="lastfm_spann_track_' + i + '" track_name="' + track.name + '" artist_name="' + track.artist['#text'] + '" track_status="' + trackStatus + '" style="width: 34px; height: 34px; float: left; cursor: pointer; margin: 1px;" onmouseover="onTrackHover(this.id)" onclick="onTrackClick(this.id)"><img src="' + imageSource + '" style="width: 34px; height: 34px;" /></div>');
}

function fillLastfmData() {
    $.getJSON(lastfmApiUrl, function (data) {
        createDivs();
        var tracks = data.recenttracks.track;
        for (var i = 0; i < Math.min(tracks.length, lastfmTracks); i++) {
            var track = tracks[i];
            displayTrackInfo(track, i);
        }
        displayInitialTrack();
    });
}

function lastfmSpannMain() {
    if (dataIsValid()) {
        fillLastfmData();
    }
}

setInterval(function () {
    lastfmSpannMain();
}, lastfmRefreshInterval * 1000);
lastfmSpannMain();

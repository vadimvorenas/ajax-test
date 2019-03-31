HOST_URL = 'http://vadimvorenas.ddns.net/';

function getCookie(name) {
    var dc = document.cookie;
    var prefix = name + "=";
    var begin = dc.indexOf("; " + prefix);
    if (begin == -1) {
        begin = dc.indexOf(prefix);
        if (begin != 0) return null;
    } else {
        begin += 2;
        var end = document.cookie.indexOf(";", begin);
        if (end == -1) {
            end = dc.length;
        }
    }
    return decodeURI(dc.substring(begin + prefix.length, end));
}

function setCookie(name, value, days=1) {
    var expires = "";
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toUTCString();
    }
    console.log(value);
    document.cookie = name + "=" + (value || "") + expires + "; path=/";
}

function logout() {
    setCookie('accessToken', '',-7);
    window.location.replace("./login.html");
}

function checkToken(token) {
    let request = {
        "action": "checkToken", "params": {
            "token": token
        }
    };
    $.ajax({
        type: "POST",
        url: HOST_URL + 'api.php',
        data: JSON.stringify(request),
        contentType: "application/json; charset=utf-8",
        success: function (data) {
            return true;
        },
        error: function (error) {
            logout();
        }
    });
}
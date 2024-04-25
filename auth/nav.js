var xhr = new XMLHttpRequest();
var links = document.querySelectorAll('.link');

links.forEach(function(link) {
    link.addEventListener('click', function(event) {
        event.preventDefault();
        var url = link.getAttribute('href');
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '/router/router.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);
                var targetElement = document.getElementById('earth');
                targetElement.innerHTML = response.html;
                executeScripts(response.scripts);
            }
        }
        xhr.send('url=' + url + '1');
        var href = link.getAttribute('href');
        history.pushState(null, null, href);
    });
});

window.addEventListener('popstate', function(event) {
    var url_popstate = window.location.pathname.replace(/\/$/, '');
    var xhr_popstate = new XMLHttpRequest();
    xhr_popstate.open('POST', '/router/router.php', true);
    xhr_popstate.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr_popstate.onreadystatechange = function() {
        if (xhr_popstate.readyState === 4 && xhr_popstate.status === 200) {
            var response = JSON.parse(xhr_popstate.responseText);
            var targetElement = document.getElementById('earth');
            targetElement.innerHTML = response.html;
            executeScripts(response.scripts);
        }
    }
    xhr_popstate.send('url=' + url_popstate + '1');
});

function executeScripts(scripts) {
    scripts.forEach(function(script) {
        var scriptElement = document.createElement('script');
        scriptElement.src = script;
        document.getElementById('earth').appendChild(scriptElement);
    });
}

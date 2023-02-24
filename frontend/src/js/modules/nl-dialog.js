import A11yDialog from 'a11y-dialog'

const container = document.querySelector('#nl-dialog')

if (container) {
    const dialog = new A11yDialog(container)

    let api_script = document.getElementById('mtcaptcha_api');
    let properApiScript = document.createElement("script");
    let mtCaptchaCreated = false;
    let captchaContainer = document.querySelector(".newsletter__dialog__captcha");

    dialog.on('show', function (element, event) {
        if (api_script && mtCaptchaCreated === false) {
            properApiScript.setAttribute('type', 'text/javascript');
            properApiScript.setAttribute('src', api_script.getAttribute('data-src'));

            api_script.parentNode.insertBefore(properApiScript, api_script);
            mtCaptchaCreated = true;
        }
    })
}

const episerver_newsletterform = document.getElementById('episerver_newsletterform');
if (episerver_newsletterform) {
    episerver_newsletterform.addEventListener('submit', (e) => {
        e.preventDefault();

        let mailfield = document.getElementById('newsletter__email');

        if (mailfield.value == '') {
            alert('Bitte eine E-Mail-Adresse eingeben.');
            return false;
        }

        let callbackName = 'jQuery_' + Math.round(100000 * Math.random());
        window[callbackName] = function(data) {
            delete window[callbackName];

            if (typeof(data) !== 'undefined' && data && typeof(data.result) !== 'undefined') {
                if (data.result === 'ok' || data.result === 'ok: updated') {
                    if (episerver_newsletterform.getAttribute('data-target')) {
                        location.href = episerver_newsletterform.getAttribute('data-target');
                    } else {
                        alert("Die Newsletter-Bestellung wurde ausgeführt!");
                    }
                    episerver_newsletterform.style.display = 'none';
                } else {
                    alert('Ein Fehler trat bei der Newsletter-Bestellung auf: ' + data.result);
                }
            } else {
                console.log(data);
                alert('Ein Fehler trat bei der Newsletter-Bestellung auf.');
            }
        };

        let ajaxurl = episerver_newsletterform.getAttribute('action') + '&callback=' + callbackName;
        const formdata = new FormData(episerver_newsletterform);
        const params = new URLSearchParams(formdata);

        const opts = {
            method: 'POST',
            body: params
        }

        fetch(ajaxurl, opts)
        .then(res => {
            if (!res.ok) {
                throw new Error('Fehlerhafte Antwort bei Newsletteranmeldung');
            }

            return res.text();
        }).then(data => {

            // Create script: This contains something like "jQuery_4711({"result":"ok"})"
            // This is then executed by calling the callback function.
            var script = document.createElement('script');
            script.type = 'text/javascript';
            try {
                script.appendChild(document.createTextNode(data));
            } catch (e) {
                script.text = data;
            }
            document.body.appendChild(script);
        }).catch(err => alert('Netzwerkfehler bei der Newsletteranmeldung.'));

        return false;
    });
}

'use strict';
Joomla.submitbutton = function (task) {
    let form = document.querySelector('#adminForm');
    let valid = document.formvalidator.isValid(form);
    if (task === 'task.cancel' || valid) {
        let fields = document.querySelectorAll("#adminForm input[type='text']");
        fields.forEach(function(elem) {
            elem.value = elem.value.trim();
            elem.value = elem.value.replace(/\s+/g, ' ');
        });
        Joomla.submitform(task, form);
    }
};

function switchType(val) {
    let theme = document.querySelector('#jform_theme');
    let place = document.querySelector('#jform_place');
    let contactID = document.querySelector('#jform_contactID');
    let state = true;
    switch (val) {
        case 'task': {
            state = true;
            contactID.setAttribute('disabled', true);
            break;
        }
        case 'meet': {
            state = false;
            contactID.removeAttribute('disabled');
            contactID.name = 'jform[contactID]';
            break;
        }
    }
    theme.readOnly = state;
    place.readOnly = state;
    contactID.readOnly = state;
    jQuery(contactID).trigger("liszt:updated");
}

function getActiveTaskCount() {
    let dat = document.querySelector("#jform_date_task").value.split('.');
    dat = dat[2] + '-' + dat[1] + '-' + dat[0];
    let url = `index.php?option=com_scheduler&task=tasks.execute&date=${dat}&format=json`;
    jQuery.getJSON(url, function (json) {
        document.querySelector("#active-cnt-date").innerText = document.querySelector("#jform_date_task").value;
        document.querySelector("#active-cnt-count").innerText = json.data.cnt;
        document.querySelector(".active-cnt").style.display = 'block';
    });
}

function fromTemplate(tip) {
    let field = document.getElementById("jform_template_"+tip);
    document.querySelector("#jform_"+tip).value = field.options[field.selectedIndex].getAttribute('data-text');
}

window.onload = function () {
    //Сохранение активной вкладки на странице
    jQuery('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        // save the latest tab; use cookies if you like 'em better:
        localStorage.setItem('lastTab', jQuery(this).attr('href'));
    });
    var lastTab = localStorage.getItem('lastTab');
    if (lastTab) {
        jQuery('[href="' + lastTab + '"]').tab('show');
    }
};
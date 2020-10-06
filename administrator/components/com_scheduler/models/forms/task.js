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


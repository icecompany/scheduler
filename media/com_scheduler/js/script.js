function updateTask(taskID) {
    let form = document.querySelector("#adminForm");
    form.addEventListener('submit', function (event) {
        event.preventDefault();
    })
    let result = document.querySelector(`#task-${taskID}`).value.trim();
    if (result === '') alert('Введите результат задачи');
    location.href = encodeURI(`index.php?option=com_scheduler&task=task.updateTask&id=${taskID}&result=${result}`);
}

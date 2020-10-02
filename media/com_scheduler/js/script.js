function updateTask(taskID) {
    let result = document.querySelector(`#task-${taskID}`).value.trim();
    if (result === '') alert('Введите результат задачи');
    let url = encodeURI(`index.php?option=com_scheduler&task=task.updateTask&id=${taskID}&result=${result}`);
    location.href = url;
}

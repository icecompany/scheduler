alter table `#__mkv_scheduler`
    add index `#__mkv_scheduler_date_task_date_close_index` (date_task, date_close);

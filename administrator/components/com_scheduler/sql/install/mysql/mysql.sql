create table `#__mkv_scheduler`
(
    id          int unsigned not null auto_increment primary key,
    date_create timestamp    not null,
    date_task   date         not null,
    date_close  timestamp    null     default null,
    contractID  int unsigned not null,
    managerID   int          not null,
    user_open   int          not null,
    user_close  int          null     default null,
    status      tinyint      not null default 0,
    task        text         not null,
    result      text         null     default null,
    constraint `#__mkv_scheduler_#__mkv_contracts_contractID_id_fk` foreign key (contractID)
        references `#__mkv_contracts` (id)
        on update cascade on delete cascade,
    constraint `#__mkv_scheduler_#__users_managerID_id_fk` foreign key (managerID)
        references `#__users` (id)
        on update cascade on delete restrict,
    constraint `#__mkv_scheduler_#__users_user_open_id_fk` foreign key (user_open)
        references `#__users` (id)
        on update cascade on delete restrict,
    constraint `#__mkv_scheduler_#__users_user_close_id_fk` foreign key (user_close)
        references `#__users` (id)
        on update cascade on delete restrict,
    index `#__mkv_scheduler_managerID_status_date_task_index` (managerID, status, date_task),
    index `#__mkv_scheduler_managerID_date_task_status_index` (managerID, date_task, status)
) character set utf8
  collate utf8_general_ci;

insert into `#__mkv_scheduler`
select id, dat_open, dat, dat_close, contractID, managerID, userOpen, userClose,
       if (result is null, if(dat < curdate(), -2, if(dat > curdate(),2, 1)), 3),
       task, result
from `#__prj_todos` where is_notify = 0;

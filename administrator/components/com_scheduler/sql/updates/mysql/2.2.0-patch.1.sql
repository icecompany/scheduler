create table `#__mkv_task_templates`
(
    id        smallint unsigned      not null primary key auto_increment,
    managerID int                    not null,
    `type`    set ('task', 'result') not null default 'task',
    title     text                   not null,
    `text`    text                   not null,
    constraint `#__mkv_task_templates_#__users_managerID_id_fk` foreign key (managerID) references `#__users` (id)
        on update cascade on delete cascade
) character set utf8
  collate utf8_general_ci;

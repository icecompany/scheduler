create table `#__mkv_managers_stat` (
                                           id int unsigned not null primary key auto_increment,
                                           dat date not null,
                                           managerID int not null,
                                           projectID smallint unsigned not null,
                                           `status_-1` smallint unsigned not null default 0,
                                           status_0 smallint unsigned not null default 0,
                                           status_1 smallint unsigned not null default 0,
                                           status_2 smallint unsigned not null default 0,
                                           status_3 smallint unsigned not null default 0,
                                           status_4 smallint unsigned not null default 0,
                                           status_5 smallint unsigned not null default 0,
                                           status_6 smallint unsigned not null default 0,
                                           status_9 smallint unsigned not null default 0,
                                           status_10 smallint unsigned not null default 0,
                                           companies_in_work smallint unsigned not null default 0,
                                           contracts_without_tasks smallint unsigned not null default 0,
                                           tasks_all smallint unsigned not null default 0,
                                           tasks_expires smallint unsigned not null default 0,
                                           tasks_two_weeks smallint unsigned not null default 0,
                                           tasks_after_two_weeks smallint unsigned not null default 0,
                                           constraint `#__mkv_managers_stat_#__users_managerID_id_fk` foreign key (managerID)
                                               references `#__users` (id) on update cascade on delete restrict,
                                           constraint `#__mkv_managers_stat_#__mkv_projects_projectID_id_fk` foreign key (projectID)
                                               references `#__mkv_projects` (id) on update cascade on delete restrict
);

insert into `#__mkv_managers_stat`
select null, dat, managerID, projectID,
       `status_-1`, status_0, `status_1`, `status_2`, `status_3`, `status_4`, 0, 0, `status_9`, `status_10`,
       exhibitors, 0, plan, todos_expires, todos_future, todos_after_next_week
from `#__prj_managers_stat`;

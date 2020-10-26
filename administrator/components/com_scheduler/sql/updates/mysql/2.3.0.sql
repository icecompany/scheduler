create table `#__mkv_meetings`
(
    id int unsigned not null auto_increment primary key,
    taskID int unsigned not null,
    contactID int unsigned not null,
    place text not null,
    theme text not null,
    constraint `#__mkv_meetings_#__mkv_scheduler_taskID_id_fk` foreign key (taskID)
        references `#__mkv_scheduler` (id)
        on update cascade on delete cascade,
    constraint `#__mkv_meetings_#__mkv_c_contacts_contactID_id_fk` foreign key (contactID)
        references `#__mkv_companies_contacts` (id)
        on update cascade on delete cascade
)
    character set utf8mb4 collate utf8mb4_general_ci;


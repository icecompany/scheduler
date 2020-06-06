-- auto-generated definition
create table `#__mkv_notifies`
(
    id          int unsigned auto_increment
        primary key,
    date_create timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    date_read   timestamp                           null,
    user_create int                                 null,
    managerID   int                                 not null,
    contractID  int unsigned                        not null,
    text        text                                not null,
    status      tinyint   default 0                 not null,
    constraint `#__mkv_notifies_#__mkv_contracts_contractID_id_fk`
        foreign key (contractID) references `#__mkv_contracts` (id)
            on update cascade on delete cascade,
    constraint `#__mkv_notifies_#__users_managerID_id_fk`
        foreign key (managerID) references `#__users` (id)
            on update cascade,
    constraint `#__mkv_notifies_#__users_user_create_id_fk`
        foreign key (user_create) references `#__users` (id)
            on update cascade
)
    charset = utf8;

create index `#__mkv_notifies_managerID_status_index`
    on `#__mkv_notifies` (managerID, status);

create index `#__mkv_notifies_status_index`
    on `#__mkv_notifies` (status);


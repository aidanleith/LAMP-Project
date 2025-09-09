-- Updated structure of db
CREATE TABLE users (
    `id` INT NOT NULL AUTO_INCREMENT ,
    `first_name` VARCHAR(50) NOT NULL DEFAULT '' ,
    `last_name` VARCHAR(50) NOT NULL DEFAULT '' ,
    `username` VARCHAR(50) NOT NULL UNIQUE DEFAULT '' ,
    `password` VARCHAR(50) NOT NULL DEFAULT '' ,
    PRIMARY KEY (`id`)
    ) ENGINE = InnoDB;

CREATE TABLE contacts (
    `id` INT NOT NULL AUTO_INCREMENT ,
    `user_id` INT NOT NULL DEFAULT '0' ,
    `first_name` VARCHAR(50) NOT NULL DEFAULT '' ,
    `last_name` VARCHAR(50) NOT NULL DEFAULT '' ,
    `phone_number` VARCHAR(50) NOT NULL DEFAULT '' ,
    `email` VARCHAR(50) NOT NULL DEFAULT '' ,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES users(`id`)
    ) ENGINE = InnoDB;
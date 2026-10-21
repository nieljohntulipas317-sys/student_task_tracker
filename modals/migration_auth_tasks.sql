USE student_task_tracker;

CREATE TABLE IF NOT EXISTS users (
    user_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(254) NOT NULL,
    username VARCHAR(50) NULL,
    gender VARCHAR(30) NULL,
    address VARCHAR(255) NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id),
    UNIQUE KEY uq_users_email (email),
    UNIQUE KEY uq_users_username (username)
) ENGINE=InnoDB
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

ALTER TABLE tasks
    ADD COLUMN user_id INT UNSIGNED NULL AFTER task_id,
    ADD KEY idx_tasks_user_id (user_id),
    ADD CONSTRAINT fk_tasks_user FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE CASCADE;

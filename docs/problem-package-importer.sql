-- Apply once to an existing installation before using the Images Library or importer.
-- Alternatively: php spark migrate (do not run both).
-- Replace table names if your installation uses a database prefix.
ALTER TABLE questions ENGINE=InnoDB;
ALTER TABLE questions_answers ENGINE=InnoDB;
ALTER TABLE topics_questions ENGINE=InnoDB;
ALTER TABLE questions ADD COLUMN tags VARCHAR(1000) NULL;

CREATE TABLE IF NOT EXISTS images_library (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    public_id CHAR(32) NOT NULL,
    storage_name VARCHAR(40) NOT NULL,
    original_name VARCHAR(180) NOT NULL,
    mime_type VARCHAR(32) NOT NULL,
    file_size INT UNSIGNED NOT NULL,
    width INT UNSIGNED NOT NULL,
    height INT UNSIGNED NOT NULL,
    owner_id BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NULL,
    PRIMARY KEY (id),
    UNIQUE KEY images_library_public_id (public_id),
    KEY images_library_owner_id_id (owner_id, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

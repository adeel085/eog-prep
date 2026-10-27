<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddImageLibraryAndQuestionTags extends Migration
{
    public function up()
    {
        // Existing question data must be transactional for an all-or-nothing import.
        if ($this->db->DBDriver === 'MySQLi') {
            foreach (['questions', 'questions_answers', 'topics_questions'] as $table) {
                $status = $this->db->query('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?', [$this->db->prefixTable($table)])->getRowArray();
                if ($status && strtoupper($status['ENGINE']) !== 'INNODB') {
                    $this->db->query('ALTER TABLE ' . $this->db->protectIdentifiers($this->db->prefixTable($table)) . ' ENGINE=InnoDB');
                }
            }
        }
        if (!$this->db->fieldExists('tags', 'questions')) {
            $this->forge->addColumn('questions', ['tags' => ['type' => 'VARCHAR', 'constraint' => 1000, 'null' => true]]);
        }
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'public_id' => ['type' => 'CHAR', 'constraint' => 32],
            'storage_name' => ['type' => 'VARCHAR', 'constraint' => 40],
            'original_name' => ['type' => 'VARCHAR', 'constraint' => 180],
            'mime_type' => ['type' => 'VARCHAR', 'constraint' => 32],
            'file_size' => ['type' => 'INT', 'unsigned' => true],
            'width' => ['type' => 'INT', 'unsigned' => true],
            'height' => ['type' => 'INT', 'unsigned' => true],
            'owner_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('public_id');
        $this->forge->addKey(['owner_id', 'id']);
        $this->forge->createTable('images_library', true, $this->db->DBDriver === 'MySQLi' ? ['ENGINE' => 'InnoDB'] : []);
    }

    public function down()
    {
        $this->forge->dropTable('images_library', true);
        $this->forge->dropColumn('questions', 'tags');
        // Keep InnoDB: reverting engines would remove transactional guarantees.
    }
}

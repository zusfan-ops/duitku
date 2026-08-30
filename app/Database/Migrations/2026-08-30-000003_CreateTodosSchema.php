<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTodosSchema extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('todos')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'user_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'title' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'description' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'category' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'default'    => 'Pribadi',
                ],
                'priority' => [
                    'type'       => 'ENUM',
                    'constraint' => ['high', 'medium', 'low'],
                    'default'    => 'medium',
                ],
                'due_date' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'due_time' => [
                    'type' => 'TIME',
                    'null' => true,
                ],
                'is_completed' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 0,
                ],
                'completed_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'subtasks' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);

            $this->forge->addKey('id', true);
            $this->forge->addKey(['user_id', 'is_completed']);
            $this->forge->addKey(['user_id', 'due_date']);
            $this->forge->createTable('todos', true);
        }
    }

    public function down()
    {
        $this->forge->dropTable('todos', true);
    }
}

<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRoomTypesTable extends Migration
{
    public function up(): void
    {
        // 1. Buat tabel room_types
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => false],
            'code'        => ['type' => 'VARCHAR', 'constraint' => 20,  'null' => true],
            'description' => ['type' => 'TEXT',    'null' => true],
            'is_active'   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
            'deleted_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('room_types');

        // 2. Tambah kolom ke locations jika belum ada
        $db     = \Config\Database::connect();
        $fields = $db->getFieldNames('locations');
        $add    = [];

        if (! in_array('room_type_id', $fields)) {
            $add['room_type_id'] = [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'building',
            ];
        }
        if (! in_array('capacity', $fields)) {
            $add['capacity'] = [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'comment'    => 'Kapasitas orang / item',
                'after'      => 'room_type_id',
            ];
        }
        if (! in_array('photo', $fields)) {
            $add['photo'] = [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'capacity',
            ];
        }
        if (! in_array('notes', $fields)) {
            $add['notes'] = [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'photo',
            ];
        }

        if (! empty($add)) {
            $this->forge->addColumn('locations', $add);
        }

        // 3. Tambah kolom ke users jika belum ada
        $userFields = $db->getFieldNames('users');
        $addUser    = [];

        // department_id is already added in AlterUsersAddPhone migration, so we skip it.
        /*
        if (! in_array('department_id', $userFields)) {
            $addUser['department_id'] = [
                'type' => 'INT', 'constraint' => 11, 'unsigned' => true,
                'null' => true, 'after' => 'phone',
            ];
        }
        */
        if (! in_array('avatar', $userFields)) {
            $addUser['avatar'] = [
                'type' => 'VARCHAR', 'constraint' => 255,
                'null' => true, 'after' => 'department_id',
            ];
        }
        if (! in_array('employee_id', $userFields)) {
            $addUser['employee_id'] = [
                'type' => 'VARCHAR', 'constraint' => 50,
                'null' => true, 'after' => 'avatar',
            ];
        }
        if (! in_array('position', $userFields)) {
            $addUser['position'] = [
                'type' => 'VARCHAR', 'constraint' => 100,
                'null' => true, 'after' => 'employee_id',
            ];
        }
        if (! in_array('notes', $userFields)) {
            $addUser['notes'] = [
                'type' => 'TEXT', 'null' => true, 'after' => 'position',
            ];
        }

        if (! empty($addUser)) {
            $this->forge->addColumn('users', $addUser);
        }
    }

    public function down(): void
    {
        $this->forge->dropTable('room_types', true);
        $this->forge->dropColumn('locations', ['room_type_id', 'capacity', 'photo', 'notes']);
        $this->forge->dropColumn('users', ['department_id', 'avatar', 'employee_id', 'position', 'notes']);
    }
}

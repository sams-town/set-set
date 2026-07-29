<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterBorrowsAddPreviousStatus extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('borrows', [
            'previous_asset_status' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'default'    => null,
                'after'      => 'status',
                'comment'    => 'Status aset sebelum dipinjam, untuk restore saat dikembalikan',
            ],
            'borrower_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'default'    => null,
                'after'      => 'purpose',
                'comment'    => 'Nama peminjam (bisa nama bebas, bukan hanya user terdaftar)',
            ],
            'borrower_dept' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'default'    => null,
                'after'      => 'borrower_name',
                'comment'    => 'Departemen peminjam',
            ],
            'borrower_phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'default'    => null,
                'after'      => 'borrower_dept',
                'comment'    => 'No HP peminjam',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('borrows', ['previous_asset_status', 'borrower_name', 'borrower_dept', 'borrower_phone']);
    }
}

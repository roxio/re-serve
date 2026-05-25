<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

class CreateSalonSchema extends Migration
{
    private array $tables = [
        'ad-settings',
        'agents',
        'analytics-settings',
        'blog',
        'blogstatus',
        'bookingtbl',
        'comments-settings',
        'contactdetails',
        'gallery',
        'gcategory',
        'general-settings',
        'logintbl',
        'meta-tags-settings',
        'orders',
        'pages',
        'recaptcha-settings',
        'servicetable',
        'smtp-settings',
        'social-keys-settings',
        'stripe-settings',
        'themesettings',
    ];

    public function up(): void
    {
        if ($this->db->tableExists('logintbl')) {
            return;
        }

        $sqlPath = APPPATH . 'Database/Sql/latest.sql';

        if (! is_file($sqlPath)) {
            throw new RuntimeException('Missing database schema file: ' . $sqlPath);
        }

        foreach ($this->splitSql((string) file_get_contents($sqlPath)) as $statement) {
            $this->db->query($statement);
        }
    }

    public function down(): void
    {
        foreach (array_reverse($this->tables) as $table) {
            $this->db->query('DROP TABLE IF EXISTS `' . str_replace('`', '``', $table) . '`');
        }
    }

    private function splitSql(string $sql): array
    {
        $statements = [];
        $buffer = '';
        $length = strlen($sql);
        $quote = null;
        $escaped = false;

        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];
            $next = $sql[$i + 1] ?? '';

            if ($quote === null && $char === '-' && $next === '-') {
                while ($i < $length && ! in_array($sql[$i], ["\n", "\r"], true)) {
                    $i++;
                }

                continue;
            }

            if ($quote === null && $char === '#') {
                while ($i < $length && ! in_array($sql[$i], ["\n", "\r"], true)) {
                    $i++;
                }

                continue;
            }

            if ($quote === null && $char === '/' && $next === '*') {
                $i += 2;

                while ($i < $length && ! ($sql[$i] === '*' && ($sql[$i + 1] ?? '') === '/')) {
                    $i++;
                }

                $i++;
                continue;
            }

            $buffer .= $char;

            if ($quote !== null) {
                if ($escaped) {
                    $escaped = false;
                    continue;
                }

                if ($char === '\\') {
                    $escaped = true;
                    continue;
                }

                if ($char === $quote) {
                    $quote = null;
                }

                continue;
            }

            if ($char === '\'' || $char === '"') {
                $quote = $char;
                continue;
            }

            if ($char === ';') {
                $statement = trim(substr($buffer, 0, -1));

                if ($statement !== '') {
                    $statements[] = $statement;
                }

                $buffer = '';
            }
        }

        $tail = trim($buffer);

        if ($tail !== '') {
            $statements[] = $tail;
        }

        return $statements;
    }
}
